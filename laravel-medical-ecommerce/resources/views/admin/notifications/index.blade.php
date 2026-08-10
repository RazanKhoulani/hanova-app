@extends('admin.layout.app')

@section('title', 'System Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>System Notifications</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
        <i class="fas fa-paper-plane me-2"></i> Send Notification
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 border-0">Recipient</th>
                        <th class="px-4 py-3 border-0">Type</th>
                        <th class="px-4 py-3 border-0">Title</th>
                        <th class="px-4 py-3 border-0">Body</th>
                        <th class="px-4 py-3 border-0">Sent At</th>
                        <th class="px-4 py-3 border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td class="px-4 py-3 align-middle">
                            @if($notification->user_id)
                                <span class="badge bg-info text-dark">{{ $notification->user->name }}</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fas fa-broadcast-tower me-1"></i> Broadcast (All)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="badge bg-light text-dark">{{ $notification->type ?? 'general' }}</span>
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
                            <p class="mb-0 fs-5">No notifications sent yet</p>
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
                    <h5 class="modal-title fw-bold">Send New Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Recipient</label>
                        <select name="user_id" class="form-select bg-light border-0">
                            <option value="">All Users (Broadcast)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Type</label>
                        <select name="type" class="form-select bg-light border-0">
                            <option value="general">General</option>
                            <option value="offer">Offer</option>
                            <option value="clinic">Clinic Update</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Title</label>
                        <input type="text" name="title" class="form-control bg-light border-0" placeholder="Notification Title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Message Body</label>
                        <textarea name="body" class="form-control bg-light border-0" rows="4" placeholder="Enter notification message..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Send Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
