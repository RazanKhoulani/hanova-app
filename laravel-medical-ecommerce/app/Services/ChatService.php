<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Consultation;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\User;
use App\Repositories\ChatRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChatService
{
    protected ChatRepository $chatRepository;

    protected PatientMedicalFactExtractor $factExtractor;

    public function __construct(ChatRepository $chatRepository, PatientMedicalFactExtractor $factExtractor)
    {
        $this->chatRepository = $chatRepository;
        $this->factExtractor = $factExtractor;
    }

    public function getUserConversations($userId)
    {
        return $this->chatRepository->getUserConversations($userId);
    }

    public function getConversationMessages($conversationId)
    {
        return $this->chatRepository->getConversationMessages($conversationId);
    }

    public function getConversationForUser($userId, $conversationId)
    {
        $conversation = $this->chatRepository->findConversationById($conversationId);
        $user = User::findOrFail($userId);
        $isAllowed = $conversation->canBeAccessedBy($user);

        if (! $isAllowed) {
            throw new AuthorizationException('You are not allowed to access this conversation.');
        }

        return $conversation;
    }

    public function startConversation($userId, $doctorId, $consultationId = null, string $careScope = 'doctor')
    {
        $consultation = null;
        if ($consultationId) {
            $consultation = Consultation::query()
                ->where('user_id', $userId)
                ->findOrFail($consultationId);
            $doctorId = $consultation->doctor_id ?: $doctorId;
            $careScope = $consultation->appointment?->provider_type === 'team' ? 'team' : 'doctor';
        }

        $resolvedDoctorId = $this->resolveDoctorId($doctorId);
        if ($consultation && ! $consultation->doctor_id) {
            $consultation->update(['doctor_id' => $resolvedDoctorId]);
        }

        return $this->chatRepository->findOrCreateConversation(
            $userId,
            $resolvedDoctorId,
            $consultation?->id,
            in_array($careScope, ['doctor', 'team'], true) ? $careScope : 'doctor',
        );
    }

    public function sendMessage($senderId, $conversationId, array $data)
    {
        $conversation = $this->getConversationForUser($senderId, $conversationId);

        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'type' => $data['type'] ?? 'text',
        ];

        $uploadedFile = $data['file'] ?? null;
        if ($uploadedFile instanceof UploadedFile) {
            $disk = config('filesystems.medical_disk', 'local');
            $path = $uploadedFile->store('chat-files/'.$conversation->id, $disk);
            $messageData['attachment'] = $path;
            $messageData['attachment_disk'] = $disk;
            $messageData['body'] = $data['message'] ?? null;
        } else {
            $messageData['body'] = $data['message'] ?? '';
        }

        $message = $this->chatRepository->createMessage($messageData);
        if ($uploadedFile instanceof UploadedFile) {
            $this->storePatientDocument($conversation, $message, $uploadedFile, $path, $disk);
        }
        $this->factExtractor->extractFromMessage($message);
        $this->broadcastMessage($message);
        $this->notifyRecipient($conversation, $message);

        return $message;
    }

    private function broadcastMessage($message): void
    {
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (Throwable $exception) {
            Log::warning('Chat message saved but realtime broadcast failed.', [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyRecipient($conversation, $message): void
    {
        $isPatientMessage = (int) $message->sender_id === (int) $conversation->user_id;
        $recipientIds = $isPatientMessage
            ? ($conversation->care_scope === 'team'
                ? User::role(['admin', 'doctor', 'staff'])->pluck('id')
                : collect([$conversation->doctor_id]))
            : collect([$conversation->user_id]);
        $recipientIds = $recipientIds->filter()->unique();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $message->loadMissing('sender');
        $senderName = $message->sender?->name ?? 'Hanova';
        $previewAr = match ($message->type) {
            'text' => Str::limit((string) $message->body, 100),
            'audio' => 'رسالة صوتية جديدة',
            'image' => 'صورة جديدة',
            default => 'مرفق طبي جديد',
        };
        $previewEn = match ($message->type) {
            'text' => Str::limit((string) $message->body, 100),
            'audio' => 'New voice message',
            'image' => 'New image',
            default => 'New medical attachment',
        };

        foreach ($recipientIds as $recipientId) {
            Notification::create([
                'user_id' => $recipientId,
                'title' => "رسالة جديدة من {$senderName}",
                'body' => $previewAr,
                'type' => 'chat_message',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'consultation_id' => $conversation->consultation_id,
                    'translations' => [
                        'ar' => [
                            'title' => "رسالة جديدة من {$senderName}",
                            'body' => $previewAr,
                        ],
                        'en' => [
                            'title' => "New message from {$senderName}",
                            'body' => $previewEn,
                        ],
                    ],
                ],
            ]);
        }
    }

    private function resolveDoctorId(?int $doctorId): int
    {
        $doctor = $doctorId
            ? User::find($doctorId)
            : User::role('doctor')->oldest()->first()
                ?? User::role('admin')->oldest()->first();

        if (! $doctor || (! $doctor->hasRole('doctor') && ! $doctor->hasRole('admin'))) {
            throw ValidationException::withMessages([
                'doctor_id' => 'No doctor is available for chat right now.',
            ]);
        }

        return $doctor->id;
    }

    private function storePatientDocument($conversation, $message, UploadedFile $file, string $path, string $disk): void
    {
        $conversation->loadMissing('user');
        $patient = Patient::firstOrCreate(
            ['user_id' => $conversation->user_id],
            [
                'name' => $conversation->user?->name ?? 'Patient',
                'phone' => $conversation->user?->phone ?? '',
            ]
        );

        $mimeType = $file->getClientMimeType();
        PatientDocument::firstOrCreate(
            ['message_id' => $message->id],
            [
                'patient_id' => $patient->id,
                'user_id' => $conversation->user_id,
                'consultation_id' => $conversation->consultation_id,
                'conversation_id' => $conversation->id,
                'document_type' => str_starts_with((string) $mimeType, 'image/') ? 'image' : 'analysis',
                'file_path' => $path,
                'storage_disk' => $disk,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
            ]
        );
    }
}
