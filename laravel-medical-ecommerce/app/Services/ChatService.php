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
        $isAllowed = (int) $conversation->user_id === (int) $userId || (int) $conversation->doctor_id === (int) $userId;

        if (! $isAllowed) {
            throw new AuthorizationException('You are not allowed to access this conversation.');
        }

        return $conversation;
    }

    public function startConversation($userId, $doctorId, $consultationId = null)
    {
        $consultation = null;
        if ($consultationId) {
            $consultation = Consultation::query()
                ->where('user_id', $userId)
                ->findOrFail($consultationId);
            $doctorId = $consultation->doctor_id ?: $doctorId;
        }

        $resolvedDoctorId = $this->resolveDoctorId($doctorId);
        if ($consultation && ! $consultation->doctor_id) {
            $consultation->update(['doctor_id' => $resolvedDoctorId]);
        }

        return $this->chatRepository->findOrCreateConversation(
            $userId,
            $resolvedDoctorId,
            $consultation?->id,
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
            $path = $uploadedFile->store('chat_files', 'public');
            $messageData['attachment'] = $path;
            $messageData['body'] = $data['message'] ?? null;
        } else {
            $messageData['body'] = $data['message'] ?? '';
        }

        $message = $this->chatRepository->createMessage($messageData);
        if ($uploadedFile instanceof UploadedFile) {
            $this->storePatientDocument($conversation, $message, $uploadedFile, $path);
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
        $recipientId = (int) $message->sender_id === (int) $conversation->user_id
            ? $conversation->doctor_id
            : $conversation->user_id;

        if (! $recipientId) {
            return;
        }

        $message->loadMissing('sender');
        $senderName = $message->sender?->name ?? 'Hanova';
        $preview = $message->type === 'text'
            ? Str::limit((string) $message->body, 100)
            : 'مرفق طبي جديد';

        Notification::create([
            'user_id' => $recipientId,
            'title' => "رسالة جديدة من {$senderName}",
            'body' => $preview,
            'type' => 'chat_message',
            'data' => [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'title_en' => "New message from {$senderName}",
                'body_en' => $message->type === 'text' ? Str::limit((string) $message->body, 100) : 'New medical attachment',
            ],
        ]);
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

    private function storePatientDocument($conversation, $message, UploadedFile $file, string $path): void
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
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
            ]
        );
    }
}
