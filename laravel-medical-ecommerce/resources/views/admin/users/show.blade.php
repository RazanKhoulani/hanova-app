@extends('admin.layout.app')

@section('title', 'User Profile: ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Profile: {{ $user->name }}</h2>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">User info</div>
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-user fa-2x text-primary"></i>
                </div>
                <h5 class="fw-bold">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->phone }}</p>
                
                <div class="pt-2">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary px-3 py-2">{{ ucfirst($role->name) }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Manage Role</div>
            <div class="card-body">
                <form action="{{ route('admin.users.assignRole', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Assign Role</label>
                        <select name="role" class="form-select">
                            <option value="user" {{ $user->hasRole('user') ? 'selected' : '' }}>User</option>
                            <option value="doctor" {{ $user->hasRole('doctor') ? 'selected' : '' }}>Doctor</option>
                            <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                            <option value="delivery" {{ $user->hasRole('delivery') ? 'selected' : '' }}>Delivery</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Role</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span>Recent Orders</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="px-4 py-2 border-0">Order ID</th>
                                <th class="px-4 py-2 border-0">Date</th>
                                <th class="px-4 py-2 border-0">Status</th>
                                <th class="px-4 py-2 border-0 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders()->latest()->take(5)->get() as $order)
                            <tr class="clickable-row" data-href="{{ route('admin.orders.show', $order->id) }}">
                                <td class="px-4 py-3 fw-bold">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-muted">{{ $order->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-info px-2 py-1">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-end fw-bold">{{ number_format($order->total_amount, 2) }} ل.س</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small italic">No orders found for this user</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
