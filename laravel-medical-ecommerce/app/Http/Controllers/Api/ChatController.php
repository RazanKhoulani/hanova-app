<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * List user conversations.
     */
    public function index()
    {
        $conversations = $this->chatService->getUserConversations(auth()->id());

        if (auth()->user()->hasAnyRole(['doctor', 'admin'])) {
            $conversations->getCollection()->transform(function ($conversation) {
                $conversation->setAttribute(
                    'contact_phone',
                    $conversation->user?->phone,
                );

                return $conversation;
            });
        }

        return response()->json(['data' => $conversations]);
    }

    /**
     * Create or fetch a user conversation.
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:users,id',
            'consultation_id' => 'nullable|exists:consultations,id',
        ]);

        $conversation = $this->chatService->startConversation(
            auth()->id(),
            $request->input('doctor_id'),
            $request->input('consultation_id'),
        );
        return response()->json(['data' => $conversation], 201);
    }

    /**
     * List messages for a conversation.
     */
    public function messages($conversationId)
    {
        $this->chatService->getConversationForUser(auth()->id(), $conversationId);
        $messages = $this->chatService->getConversationMessages($conversationId);
        $messages->setCollection($messages->getCollection()->reverse()->values());

        return MessageResource::collection($messages)->additional([
            'message' => 'Conversation messages retrieved successfully',
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(SendMessageRequest $request, $conversationId)
    {
        $message = $this->chatService->sendMessage(auth()->id(), $conversationId, $request->validated());
        return new MessageResource($message);
    }
}
