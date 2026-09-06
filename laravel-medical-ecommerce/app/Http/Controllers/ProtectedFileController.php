<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\PatientProgressPhoto;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProtectedFileController extends Controller
{
    public function __invoke(Request $request, string $kind, int $id, string $field, AuditService $auditService)
    {
        $viewer = $request->integer('viewer') ? User::find($request->integer('viewer')) : null;
        [$model, $path, $disk, $name] = $this->resolveFile($kind, $id, $field);

        if ($viewer && ! $this->canAccess($viewer, $kind, $model)) {
            abort(403);
        }

        $disk = $disk ?: 'public';
        if (! Storage::disk($disk)->exists($path) && $disk !== 'public' && Storage::disk('public')->exists($path)) {
            $disk = 'public';
        }

        abort_unless(Storage::disk($disk)->exists($path), 404);

        if ($viewer) {
            $auditService->record('protected_file.viewed', $model, [
                'kind' => $kind,
                'field' => $field,
            ], $viewer->id);
        }

        return Storage::disk($disk)->response($path, $name, [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolveFile(string $kind, int $id, string $field): array
    {
        return match ($kind) {
            'message' => $this->messageFile($id, $field),
            'patient-document' => $this->patientDocumentFile($id, $field),
            'progress-photo' => $this->progressPhotoFile($id, $field),
            'patient' => $this->patientFile($id, $field),
            'order-receipt' => $this->orderReceiptFile($id, $field),
            default => abort(404),
        };
    }

    private function messageFile(int $id, string $field): array
    {
        abort_unless($field === 'attachment', 404);
        $message = Message::with('conversation')->findOrFail($id);
        abort_unless($message->attachment, 404);

        return [$message, $message->attachment, $message->attachment_disk, basename($message->attachment)];
    }

    private function patientDocumentFile(int $id, string $field): array
    {
        abort_unless($field === 'file', 404);
        $document = PatientDocument::with('patient')->findOrFail($id);

        return [$document, $document->file_path, $document->storage_disk, $document->original_name ?: basename($document->file_path)];
    }

    private function progressPhotoFile(int $id, string $field): array
    {
        abort_unless(in_array($field, ['before', 'after'], true), 404);
        $photo = PatientProgressPhoto::with('patient')->findOrFail($id);
        $path = $field === 'before' ? $photo->before_image : $photo->after_image;

        return [$photo, $path, $photo->storage_disk, basename($path)];
    }

    private function patientFile(int $id, string $field): array
    {
        abort_unless(in_array($field, ['medical', 'before', 'after'], true), 404);
        $patient = Patient::findOrFail($id);
        $path = match ($field) {
            'medical' => $patient->medical_file,
            'before' => $patient->image_before,
            'after' => $patient->image_after,
        };
        $disk = $field === 'medical' ? $patient->medical_file_disk : $patient->progress_images_disk;
        abort_unless($path, 404);

        return [$patient, $path, $disk, basename($path)];
    }

    private function orderReceiptFile(int $id, string $field): array
    {
        abort_unless($field === 'receipt', 404);
        $order = Order::findOrFail($id);
        abort_unless($order->shipping_receipt, 404);

        return [$order, $order->shipping_receipt, $order->receipt_disk, basename($order->shipping_receipt)];
    }

    private function canAccess(User $viewer, string $kind, Model $model): bool
    {
        if ($kind === 'message') {
            return $model->conversation->canBeAccessedBy($viewer);
        }

        if ($kind === 'order-receipt') {
            return (int) $model->user_id === (int) $viewer->id || $viewer->hasAnyRole(['admin', 'doctor']);
        }

        $patient = $model instanceof Patient ? $model : $model->patient;
        if ((int) $patient->user_id === (int) $viewer->id) {
            return true;
        }

        if ($viewer->hasRole('admin')) {
            return true;
        }

        return $viewer->hasRole('doctor') && (
            $patient->appointments()->where('doctor_id', $viewer->id)->exists()
            || $patient->consultations()->where('doctor_id', $viewer->id)->exists()
        );
    }
}
