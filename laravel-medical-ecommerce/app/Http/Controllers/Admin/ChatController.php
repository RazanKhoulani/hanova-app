<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
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

        return back()->with('success', 'Message sent successfully');
    }
}
