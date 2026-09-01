@extends('admin.layout.app')

@section('title', __('admin.medical_chats'))

@section('content')
<div class="page-header">
    <div><span class="page-eyebrow">HANOVA CARE</span><h2>{{ __('admin.medical_chats') }}</h2><p>{{ __('admin.manage_chats') }}</p></div>
    <span class="soft-count">{{ $conversations->total() }}</span>
</div>

<section class="panel-card data-panel">
    <div class="panel-heading"><div><h3>{{ __('admin.medical_chats') }}</h3><p>{{ __('admin.last_message') }}</p></div><i class="fas fa-comments text-primary"></i></div>
    <div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 admin-data-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 border-0">{{ __('admin.patient') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.last_message') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3 border-0 text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                    <tr class="clickable-row" data-href="{{ route('admin.chats.show', $conversation->id) }}">
                        <td class="px-4 py-3 align-middle">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $conversation->user->name }}</div>
                                    <small class="text-muted">{{ $conversation->user->phone }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($conversation->lastMessage)
                                <div class="text-truncate" style="max-width: 300px;">
                                    <span class="{{ $conversation->lastMessage->sender_id != auth()->id() && !$conversation->lastMessage->is_read ? 'fw-bold text-dark' : 'text-muted' }}">
                                        {{ $conversation->lastMessage->body }}
                                    </span>
                                </div>
                                <small class="text-muted d-block">{{ $conversation->lastMessage->created_at->locale(app()->getLocale())->diffForHumans() }}</small>
                            @else
                                <span class="text-muted italic">{{ __('admin.no_messages_yet') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @php
                                $unreadCount = $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="status-pill danger">{{ __('admin.new_count', ['count' => $unreadCount]) }}</span>
                            @else
                                <span class="status-pill success">{{ __('admin.read') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle text-end">
                            <a href="{{ route('admin.chats.show', $conversation->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-reply me-1"></i>{{ __('admin.reply') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">{{ __('admin.no_active_conversations') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
<div class="mt-4">{{ $conversations->links() }}</div>
@endsection
