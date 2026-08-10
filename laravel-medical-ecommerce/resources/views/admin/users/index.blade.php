@extends('admin.layout.app')

@section('title', 'Manage Users & Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Users Management</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 px-4 py-3">Name</th>
                        <th class="border-0 px-4 py-3">Phone</th>
                        <th class="border-0 px-4 py-3">Registered At</th>
                        <th class="border-0 px-4 py-3">Current Role</th>
                        <th class="border-0 px-4 py-3 text-end">Action/Assign Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="clickable-row" data-href="{{ route('admin.users.show', $user->id) }}">
                        <td class="align-middle px-4">{{ $user->id }}</td>
                        <td class="align-middle px-4 fw-medium">{{ $user->name }}</td>
                        <td class="align-middle px-4">{{ $user->phone }}</td>
                        <td class="align-middle px-4 text-muted">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="align-middle px-4">
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary me-1">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </td>
                        <td class="align-middle px-4 text-end">
                            <form action="{{ route('admin.users.assignRole', $user->id) }}" method="POST" class="d-flex justify-content-end align-items-center">
                                @csrf
                                @method('PUT')
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto me-2" onchange="this.form.submit()">
                                    <option value="" disabled selected>Change Role...</option>
                                    <option value="user">User</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="admin">Admin</option>
                                    <option value="delivery">Delivery</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No users found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
