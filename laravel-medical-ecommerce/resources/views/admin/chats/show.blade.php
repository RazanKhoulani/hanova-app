@extends('admin.layout.app')

@section('title', __('admin.chat_with', ['name' => $conversation->user->name]))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.chats.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
        </a>
        <h2 class="mb-0">{{ __('admin.chat_title', ['name' => $conversation->user->name]) }}</h2>
    </div>
</div>

<div class="card shadow-sm border-0 d-flex flex-column" style="height: 70vh;">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center">
            <span id="chat-connection-dot" class="rounded-circle bg-warning me-2" style="width: 10px; height: 10px;"></span>
            <span id="chat-connection-status" class="text-muted small">{{ __('admin.realtime_connecting') }}</span>
        </div>
    </div>
    
    <div class="card-body overflow-auto p-4 bg-light bg-opacity-50" id="chat-box">
        @foreach($conversation->messages as $message)
            <div class="d-flex mb-4 chat-message {{ $message->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
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
        <div id="chat-send-error" class="alert alert-danger py-2 px-3 mb-2 d-none" role="alert"></div>
        <form id="chat-message-form" action="{{ route('admin.chats.messages.store', $conversation->id) }}" method="POST">
            @csrf
            <div class="input-group">
                <input id="chat-message-input" type="text" name="body" class="form-control border-0 bg-light py-2" placeholder="{{ __('admin.write_message') }}" required autofocus autocomplete="off">
                <button id="chat-send-button" class="btn btn-primary px-4" type="submit">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chat-box');
        const statusText = document.getElementById('chat-connection-status');
        const statusDot = document.getElementById('chat-connection-dot');
        const messageForm = document.getElementById('chat-message-form');
        const messageInput = document.getElementById('chat-message-input');
        const sendButton = document.getElementById('chat-send-button');
        const sendError = document.getElementById('chat-send-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const conversationId = {{ (int) $conversation->id }};
        const currentUserId = {{ (int) auth()->id() }};
        const fallbackSenderName = @json($conversation->user->name);
        const locale = @json(app()->getLocale());
        const authEndpoint = @json(route('admin.broadcasting.auth'));
        const markReadUrl = @json(route('admin.chats.read', $conversation->id));
        const pusherKey = @json(config('broadcasting.connections.pusher.key'));
        const pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
        const statusLabels = {
            connecting: @json(__('admin.realtime_connecting')),
            connected: @json(__('admin.realtime_connected')),
            reconnecting: @json(__('admin.realtime_reconnecting')),
            unavailable: @json(__('admin.realtime_unavailable')),
        };
        let pusher = null;

        const scrollToLatest = () => {
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        const setConnectionStatus = (state) => {
            const connected = state === 'connected';
            const reconnecting = state === 'connecting' || state === 'reconnecting';
            statusText.textContent = connected
                ? statusLabels.connected
                : reconnecting
                    ? statusLabels.reconnecting
                    : statusLabels.unavailable;
            statusDot.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            statusDot.classList.add(connected ? 'bg-success' : reconnecting ? 'bg-warning' : 'bg-danger');
        };

        const formatTime = (value) => {
            const date = value ? new Date(value) : new Date();
            if (Number.isNaN(date.getTime())) return '';

            return new Intl.DateTimeFormat(locale, {
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        };

        const appendMessage = (message) => {
            if (!message || Number(message.conversation_id) !== conversationId) return;
            if (document.querySelector(`[data-message-id="${Number(message.id)}"]`)) return;

            const isMine = Number(message.sender_id) === currentUserId;
            const row = document.createElement('div');
            row.className = `d-flex mb-4 chat-message ${isMine ? 'justify-content-end' : 'justify-content-start'}`;
            row.dataset.messageId = String(message.id);

            const content = document.createElement('div');
            content.className = 'max-w-75';

            if (!isMine) {
                const sender = document.createElement('small');
                sender.className = 'text-muted d-block mb-1';
                sender.textContent = message.sender_name || fallbackSenderName;
                content.appendChild(sender);
            }

            const bubble = document.createElement('div');
            bubble.className = `p-3 rounded-4 shadow-sm ${isMine ? 'bg-primary text-white br-tr-0' : 'bg-white text-dark br-tl-0'}`;
            bubble.style.maxWidth = '450px';
            bubble.style.borderRadius = '1rem';
            bubble.textContent = message.message ?? message.text ?? '';
            content.appendChild(bubble);

            const meta = document.createElement('small');
            meta.className = `text-muted mt-1 d-block ${isMine ? 'text-end' : ''}`;
            meta.style.fontSize = '0.7rem';
            meta.textContent = formatTime(message.created_at);

            if (isMine) {
                const receipt = document.createElement('i');
                receipt.className = `fas fa-check-double ms-1 ${message.is_read ? 'text-info' : ''}`;
                meta.appendChild(receipt);
            }

            content.appendChild(meta);
            row.appendChild(content);
            chatBox.appendChild(row);
            scrollToLatest();
        };

        const markIncomingMessagesRead = () => {
            fetch(markReadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            }).catch(() => {});
        };

        scrollToLatest();

        if (!window.Pusher || !pusherKey) {
            setConnectionStatus('unavailable');
        } else {
            pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                forceTLS: true,
                channelAuthorization: {
                    endpoint: authEndpoint,
                    transport: 'ajax',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            });

            pusher.connection.bind('state_change', ({ current }) => {
                setConnectionStatus(current);
            });
            pusher.connection.bind('error', () => setConnectionStatus('unavailable'));

            const channel = pusher.subscribe(`private-conversation.${conversationId}`);
            channel.bind('pusher:subscription_succeeded', () => setConnectionStatus('connected'));
            channel.bind('pusher:subscription_error', () => setConnectionStatus('unavailable'));
            channel.bind('message.sent', (payload) => {
                const message = payload?.message;
                appendMessage(message);
                if (message && Number(message.sender_id) !== currentUserId) {
                    markIncomingMessagesRead();
                }
            });
        }

        messageForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = messageInput.value.trim();
            if (!body || sendButton.disabled) return;

            sendButton.disabled = true;
            sendError.classList.add('d-none');

            try {
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                };
                const socketId = pusher?.connection?.socket_id;
                if (socketId) headers['X-Socket-ID'] = socketId;

                const response = await fetch(messageForm.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers,
                    body: JSON.stringify({ body }),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const validationMessage = Object.values(payload.errors ?? {})
                        .flat()
                        .find(Boolean);
                    throw new Error(validationMessage || payload.message || @json(__('admin.unexpected_error')));
                }

                appendMessage(payload.data);
                messageInput.value = '';
                messageInput.focus();
            } catch (error) {
                sendError.textContent = error?.message || @json(__('admin.unexpected_error'));
                sendError.classList.remove('d-none');
            } finally {
                sendButton.disabled = false;
            }
        });

        window.addEventListener('beforeunload', () => {
            pusher?.unsubscribe(`private-conversation.${conversationId}`);
            pusher?.disconnect();
        });
    });
</script>
@endpush
@endsection
