@extends('admin.layout.app')

@section('title', __('admin.system_notifications'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.system_notifications') }}</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
        <i class="fas fa-paper-plane me-2"></i>{{ __('admin.send_notification') }}
    </button>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-bold py-3">{{ __('admin.incoming_notifications') }}</div>
    <div class="list-group list-group-flush">
        @forelse($inboxNotifications as $alert)
            @php
                $target = match($alert->type) {
                    'chat_message' => isset($alert->data['conversation_id']) ? route('admin.chats.show', $alert->data['conversation_id']) : route('admin.chats.index'),
                    'new_order' => isset($alert->data['order_id']) ? route('admin.orders.show', $alert->data['order_id']) : route('admin.orders.index'),
                    'new_appointment' => isset($alert->data['appointment_id']) ? route('admin.appointments.show', $alert->data['appointment_id']) : route('admin.appointments.index'),
                    default => '#',
                };
            @endphp
            <a href="{{ $target }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-3">
                <div><strong>{{ $alert->title }}</strong><div class="text-muted small mt-1">{{ $alert->body }}</div></div>
                <small class="text-muted text-nowrap">{{ $alert->created_at->diffForHumans() }}</small>
            </a>
        @empty
            <div class="p-4 text-center text-muted">{{ __('admin.no_incoming_notifications') }}</div>
        @endforelse
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 border-0">{{ __('admin.recipient') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.notification_type') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.title') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.message_body') }}</th>
                        <th class="px-4 py-3 border-0">{{ __('admin.sent_at') }}</th>
                        <th class="px-4 py-3 border-0 text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td class="px-4 py-3 align-middle">
                            @if($notification->user_id)
                                <span class="badge bg-info text-dark">{{ $notification->user->name }}</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fas fa-broadcast-tower me-1"></i>{{ __('admin.broadcast_all') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="badge bg-light text-dark">{{ $notification->type === 'offer' ? __('admin.offer') : ($notification->type === 'clinic' ? __('admin.clinic_update') : __('admin.general')) }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle fw-bold">{{ $notification->title }}</td>
                        <td class="px-4 py-3 align-middle text-muted text-truncate" style="max-width: 250px;">{{ $notification->body }}</td>
                        <td class="px-4 py-3 align-middle text-muted">{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 align-middle text-end">
                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-confirm">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-bell-slash fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">{{ __('admin.no_notifications_sent') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">{{ __('admin.send_new_notification') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('admin.recipient') }}</label>
                        <select name="user_id" class="form-select bg-light border-0">
                            <option value="">{{ __('admin.broadcast_all') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('admin.notification_type') }}</label>
                        <select name="type" class="form-select bg-light border-0">
                            <option value="general">{{ __('admin.general') }}</option>
                            <option value="offer">{{ __('admin.offer') }}</option>
                            <option value="clinic">{{ __('admin.clinic_update') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('admin.title') }}</label>
                        <input type="text" name="title" class="form-control bg-light border-0" placeholder="{{ __('admin.notification_title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('admin.message_body') }}</label>
                        <textarea name="body" class="form-control bg-light border-0" rows="4" placeholder="{{ __('admin.enter_notification_message') }}" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-4">{{ __('admin.send_now') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
