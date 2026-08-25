<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Patient;
use App\Models\PatientMedicalFact;
use App\Models\PatientDocument;
use App\Models\PatientProgressPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('query', ''));
        $patients = Patient::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('record_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.patients.index', compact('patients', 'search'));
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Record code', 'Name', 'Phone', 'Age', 'Address', 'Registered at']);

            Patient::query()
                ->orderBy('id')
                ->cursor()
                ->each(function (Patient $patient) use ($handle) {
                    fputcsv($handle, [
                        $patient->record_code,
                        $patient->name,
                        $patient->phone,
                        $patient->age,
                        $patient->address,
                        optional($patient->created_at)->format('Y-m-d H:i'),
                    ]);
                });

            fclose($handle);
        }, 'hanova-patients-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show($id)
    {
        $patient = Patient::with([
            'user',
            'progressPhotos' => fn ($query) => $query->with('coupon')->latest(),
            'medicalFacts' => fn ($query) => $query->with('sourceMessage')->latest(),
            'documents' => fn ($query) => $query->with('consultation')->latest(),
            'appointments' => fn ($query) => $query->with('consultation')->latest('date'),
        ])->findOrFail($id);
        return view('admin.patients.show', compact('patient'));
    }

    public function approveProgressPhoto(PatientProgressPhoto $photo)
    {
        if ($photo->status !== 'pending') {
            return back()->with('success', 'Progress photos were already reviewed');
        }

        $coupon = null;

        if ($photo->consent_for_discount && $photo->user_id) {
            $coupon = Coupon::create([
                'code' => $this->makePhotoCouponCode($photo),
                'user_id' => $photo->user_id,
                'discount_type' => 'percentage',
                'discount_value' => $photo->discount_percent,
                'status' => 'active',
                'source' => 'before_after_photos',
                'expires_at' => now()->addMonths(3),
            ]);
        }

        $photo->update([
            'status' => 'approved',
            'coupon_id' => $coupon?->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $photo->patient->update([
            'image_before' => $photo->before_image,
            'image_after' => $photo->after_image,
        ]);

        return back()->with('success', 'Progress photos approved successfully');
    }

    public function rejectProgressPhoto(Request $request, PatientProgressPhoto $photo)
    {
        if ($photo->status !== 'pending') {
            return back()->with('success', 'Progress photos were already reviewed');
        }

        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $photo->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return back()->with('success', 'Progress photos rejected');
    }

    public function updateMedicalFactStatus(Request $request, PatientMedicalFact $fact)
    {
        $data = $request->validate([
            'status' => 'required|in:suggested,confirmed,ignored',
        ]);

        $fact->update($data);

        return back()->with('success', 'Medical fact status updated');
    }

    public function storeDocument(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $data['file'];
        $path = $file->store('patient-documents/'.$patient->id, 'public');

        PatientDocument::create([
            'patient_id' => $patient->id,
            'user_id' => $patient->user_id,
            'document_type' => str_starts_with((string) $file->getMimeType(), 'image/') ? 'clinical_photo' : 'medical_file',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'تم رفع الملف إلى ملف المريضة');
    }

    private function makePhotoCouponCode(PatientProgressPhoto $photo): string
    {
        do {
            $code = 'PHOTO-' . $photo->user_id . '-' . Str::upper(Str::random(6));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
