@extends('admin.layout.app')

@section('title', __('admin.orders_management'))

@section('content')
@php
    $pricing = \App\Models\AppSetting::pricingValues();
    $displayCurrency = $pricing['display_currency'] === 'usd' ? 'usd' : 'syp_new';
    $rate = (float) $pricing[$displayCurrency === 'usd' ? 'syp_old_per_usd' : 'syp_old_per_new'];
    $currencyLabel = $displayCurrency === 'usd' ? 'USD' : __('admin.currency_syp_new');
    $money = static fn ($amount) => $rate > 0 ? number_format(((float) $amount) / $rate, 2) . ' ' . $currencyLabel : number_format((float) $amount, 2) . ' ' . __('admin.stored_value');
@endphp
<div class="page-header"><div><p class="eyebrow">{{ __('admin.store') }}</p><h1>{{ __('admin.orders_management') }}</h1><p>{{ __('admin.orders_management_hint') }}</p></div></div>
@if($rate <= 0)<div class="alert alert-warning border-0 shadow-sm mb-4"><i class="fas fa-triangle-exclamation me-2"></i>{{ __('admin.currency_rate_missing') }}</div>@endif
<section class="panel-card data-panel"><div class="panel-heading"><div><h3>{{ __('admin.orders') }}</h3><p>{{ __('admin.orders_table_hint') }}</p></div><span class="soft-count">{{ $orders->total() }}</span></div><div class="table-responsive"><table class="table align-middle mb-0 admin-data-table"><thead><tr><th>{{ __('admin.order_id') }}</th><th>{{ __('admin.date') }}</th><th>{{ __('admin.customer') }}</th><th>{{ __('admin.total') }}</th><th>{{ __('admin.payment') }}</th><th>{{ __('admin.delivery') }}</th><th>{{ __('admin.status') }}</th><th class="text-end">{{ __('admin.action') }}</th></tr></thead><tbody>
    @forelse($orders as $order)
        @php
            $statusKey = 'admin.status_' . $order->status;
            $paymentKey = match ($order->payment_method) { 'cash_on_delivery' => 'admin.cash_on_delivery', 'credit_card' => 'admin.credit_card', 'online', 'apple_pay' => 'admin.online_payment', default => 'admin.cash' };
            $deliveryKey = 'admin.' . ($order->delivery_method ?: 'home_delivery');
        @endphp
        <tr class="clickable-row" data-href="{{ route('admin.orders.show', $order->id) }}"><td class="fw-bold">#{{ $order->id }}</td><td class="text-muted">{{ $order->created_at->locale(app()->getLocale())->translatedFormat('d M Y، H:i') }}</td><td><strong>{{ $order->user?->name ?? __('admin.unknown_customer') }}</strong><small class="d-block text-muted" dir="ltr">{{ $order->user?->phone }}</small></td><td class="fw-bold">{{ $money($order->total_amount) }}@if(($order->discount_amount ?? 0) > 0)<small class="d-block text-success">-{{ $money($order->discount_amount) }} {{ __('admin.discount') }}</small>@endif</td><td><span class="status-pill info">{{ __($paymentKey) }}</span><small class="d-block text-muted">{{ $order->payment_status === 'paid' ? __('admin.paid') : __('admin.unpaid') }}</small></td><td><span>{{ trans()->has($deliveryKey) ? __($deliveryKey) : ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</span>@if($order->deliveryArea)<small class="d-block text-muted">{{ app()->getLocale() === 'ar' ? $order->deliveryArea->name_ar : $order->deliveryArea->name_en }}</small>@elseif($order->pickup_location)<small class="d-block text-muted">{{ __('admin.' . $order->pickup_location) }}</small>@else<small class="d-block text-muted">{{ __('admin.no_area') }}</small>@endif</td><td><span class="status-pill {{ in_array($order->status, ['cancelled', 'canceled']) ? 'danger' : (in_array($order->status, ['delivered', 'paid']) ? 'success' : 'warning') }}">{{ trans()->has($statusKey) ? __($statusKey) : ucfirst($order->status) }}</span></td><td><div class="action-toolbar justify-content-end">@if(auth()->user()->hasRole('delivery')) @if($order->status !== 'delivered')<form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">@csrf @method('PUT')<input type="hidden" name="status" value="delivered"><button type="submit" class="btn btn-sm btn-success">{{ __('admin.mark_delivered') }}</button></form>@endif @else<form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">@csrf @method('PUT')<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">@foreach(['pending', 'accepted', 'ready', 'paid', 'shipped', 'delivered', 'cancelled'] as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ __('admin.status_' . $status) }}</option>@endforeach</select></form>@endif<a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light" title="{{ __('admin.view_details') }}"><i class="fas fa-eye"></i></a></div></td></tr>
    @empty
        <tr><td colspan="8" class="empty-table"><i class="fas fa-shopping-cart"></i><span>{{ __('admin.no_orders_found') }}</span></td></tr>
    @endforelse
    </tbody></table></div>@if($orders->hasPages())<div class="pt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>@endif</section>
@endsection
