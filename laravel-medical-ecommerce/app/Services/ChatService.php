<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Notification;
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

    public function startConversation($userId, $doctorId)
    {
        return $this->chatRepository->findOrCreateConversation(
            $userId,
            $this->resolveDoctorId($doctorId),
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

        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $path = $data['file']->store('chat_files', 'public');
            $messageData['attachment'] = $path;
            $messageData['body'] = $data['message'] ?? null;
        } else {
            $messageData['body'] = $data['message'] ?? '';
        }

        $message = $this->chatRepository->createMessage($messageData);
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
            : 'New attachment';

        Notification::create([
            'user_id' => $recipientId,
            'title' => "New message from {$senderName}",
            'body' => $preview,
            'type' => 'chat_message',
            'data' => [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
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
}
