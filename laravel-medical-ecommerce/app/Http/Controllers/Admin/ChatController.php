<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Services\PatientMedicalFactExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['user', 'lastMessage'])
            ->latest()
            ->paginate(15);
        return view('admin.chats.index', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = Conversation::with(['user', 'messages.sender'])->findOrFail($id);
        
        // Mark messages as read
        $conversation->messages()->where('sender_id', '!=', auth()->id())->update(['is_read' => true]);
        
        return view('admin.chats.show', compact('conversation'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $id,
            'sender_id' => auth()->id(),
            'body' => $request->body,
            'type' => 'text',
        ]);

        app(PatientMedicalFactExtractor::class)->extractFromMessage($message);
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (Throwable $exception) {
            Log::warning('Admin chat message saved but realtime broadcast failed.', [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'error' => $exception->getMessage(),
            ]);
        }

        $conversation = Conversation::findOrFail($id);
        Notification::create([
            'user_id' => $conversation->user_id,
            'title' => 'رسالة جديدة من العيادة',
            'body' => \Illuminate\Support\Str::limit($request->body, 100),
            'type' => 'chat_message',
            'data' => [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'title_en' => 'New message from the clinic',
                'body_en' => \Illuminate\Support\Str::limit($request->body, 100),
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => new MessageResource($message->fresh('sender')),
            ], 201);
        }

        return back()->with('success', __('admin.message_sent_successfully'));
    }

    public function markRead($id)
    {
        $conversation = Conversation::findOrFail($id);
        $updated = $conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['updated' => $updated]);
    }
}
