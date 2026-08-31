@extends('admin.layout.app')

@section('title', __('admin.user_profile') . ' - ' . $user->name)

@section('content')
@php
    $roleName = $user->roles->first()?->name ?? 'user';
    $money = static fn ($amount): string => number_format((float) $amount, 2) . ' ل.س';
@endphp
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.users') }}</p><h1>{{ __('admin.user_profile') }}</h1><p>{{ $user->name }}</p></div>
    <div class="action-toolbar">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}</a>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i>{{ __('admin.edit') }}</a>
        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-confirm d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i>{{ __('admin.delete') }}</button></form>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <section class="panel-card detail-profile-card h-100">
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
            <h2>{{ $user->name }}</h2>
            <p class="muted-number" dir="ltr">{{ $user->phone }}</p>
            @if($user->email)<p class="text-muted mb-3" dir="ltr">{{ $user->email }}</p>@endif
            <span class="status-pill info">{{ __('admin.role_' . $roleName) }}</span>
            <div class="profile-meta mt-4"><div><span>{{ __('admin.registered_at') }}</span><strong>{{ $user->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</strong></div><div><span>{{ __('admin.order_count') }}</span><strong>{{ $user->orders->count() }}</strong></div></div>
        </section>
    </div>
    <div class="col-xl-8">
        <section class="panel-card mb-4">
            <div class="panel-heading"><div><h3>{{ __('admin.manage_role') }}</h3><p>{{ __('admin.manage_role_hint') }}</p></div></div>
            <form action="{{ route('admin.users.assignRole', $user->id) }}" method="POST" class="row g-3 align-items-end">
                @csrf @method('PUT')
                <div class="col-md-8"><label for="role" class="form-label">{{ __('admin.assign_role') }}</label><select id="role" name="role" class="form-select">@foreach(['user', 'doctor', 'admin', 'delivery'] as $role)<option value="{{ $role }}" @selected($roleName === $role)>{{ __('admin.role_' . $role) }}</option>@endforeach</select></div>
                <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">{{ __('admin.update_role') }}</button></div>
            </form>
        </section>
        <section class="panel-card data-panel">
            <div class="panel-heading"><div><h3>{{ __('admin.recent_orders_for_user') }}</h3><p>{{ __('admin.recent_orders_hint') }}</p></div><a href="{{ route('admin.orders.index') }}" class="panel-link">{{ __('admin.view_all') }}</a></div>
            <div class="table-responsive"><table class="table align-middle mb-0 admin-data-table"><thead><tr><th>{{ __('admin.order_id') }}</th><th>{{ __('admin.date') }}</th><th>{{ __('admin.status') }}</th><th class="text-end">{{ __('admin.total') }}</th></tr></thead><tbody>
                @forelse($user->orders->sortByDesc('created_at')->take(5) as $order)
                    @php($statusKey = trans()->has('admin.status_' . $order->status) ? 'admin.status_' . $order->status : null)
                    <tr class="clickable-row" data-href="{{ route('admin.orders.show', $order->id) }}"><td class="fw-bold">#{{ $order->id }}</td><td class="text-muted">{{ $order->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td><td><span class="status-pill {{ in_array($order->status, ['cancelled', 'canceled']) ? 'danger' : ($order->status === 'completed' ? 'success' : 'warning') }}">{{ $statusKey ? __($statusKey) : ucfirst($order->status) }}</span></td><td class="text-end fw-bold">{{ $money($order->total_amount) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty-table"><i class="fas fa-bag-shopping"></i><span>{{ __('admin.no_orders_for_user') }}</span></td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>
</div>
@endsection
