@extends('admin.layout.app')

@section('title', 'Chat with ' . $conversation->user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.chats.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="mb-0">Chat: {{ $conversation->user->name }}</h2>
    </div>
</div>

<div class="card shadow-sm border-0 d-flex flex-column" style="height: 70vh;">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 10px; height: 10px;"></div>
            <span class="text-muted small">Online Console</span>
        </div>
    </div>
    
    <div class="card-body overflow-auto p-4 bg-light bg-opacity-50" id="chat-box">
        @foreach($conversation->messages as $message)
            <div class="d-flex mb-4 {{ $message->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="max-w-75">
                    @if($message->sender_id != auth()->id())
                        <small class="text-muted d-block mb-1">{{ $message->sender->name }}</small>
                    @endif
                    <div class="p-3 rounded-4 shadow-sm {{ $message->sender_id == auth()->id() ? 'bg-primary text-white br-tr-0' : 'bg-white text-dark br-tl-0' }}" 
                         style="max-width: 450px; border-radius: 1rem;">
                        {{ $message->body }}
                    </div>
                    <small class="text-muted mt-1 d-block {{ $message->sender_id == auth()->id() ? 'text-end' : '' }}" style="font-size: 0.7rem;">
                        {{ $message->created_at->format('H:i') }}
                        @if($message->sender_id == auth()->id())
                            <i class="fas fa-check-double ms-1 {{ $message->is_read ? 'text-info' : '' }}"></i>
                        @endif
                    </small>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card-footer bg-white border-top-0 py-3">
        <form action="{{ route('admin.chats.messages.store', $conversation->id) }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="body" class="form-control border-0 bg-light py-2" placeholder="Type your message here..." required autofocus>
                <button class="btn btn-primary px-4" type="submit">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>
@endpush
@endsection
