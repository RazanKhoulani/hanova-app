@extends('admin.layout.app')

@section('title', 'Manage Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Orders Management</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">Order ID</th>
                        <th class="border-0 px-4 py-3">Date</th>
                        <th class="border-0 px-4 py-3">Customer</th>
                        <th class="border-0 px-4 py-3">Total</th>
                        <th class="border-0 px-4 py-3">Payment</th>
                        <th class="border-0 px-4 py-3">Delivery</th>
                        <th class="border-0 px-4 py-3">Status</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="clickable-row" data-href="{{ route('admin.orders.show', $order->id) }}">
                        <td class="align-middle px-4 fw-bold">#{{ $order->id }}</td>
                        <td class="align-middle px-4 text-muted">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="align-middle px-4">
                            {{ $order->user->name ?? 'Unknown' }}<br>
                            <small class="text-muted">{{ $order->user->phone ?? '' }}</small>
                        </td>
                        <td class="align-middle px-4 fw-medium">
                            ${{ number_format($order->total_amount, 2) }}
                            @if(($order->discount_amount ?? 0) > 0)
                                <br><small class="text-success">Discount: -${{ number_format($order->discount_amount, 2) }}</small>
                            @endif
                            @if(($order->delivery_fee ?? 0) > 0)
                                <br><small class="text-muted">Delivery: ${{ number_format($order->delivery_fee, 2) }}</small>
                            @endif
                        </td>
                        <td class="align-middle px-4">
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                            @if($order->payment_status)
                                <br><small class="text-muted">{{ ucfirst($order->payment_status) }}</small>
                            @endif
                            @if($order->coupon)
                                <br><small class="text-success">{{ $order->coupon->code }}</small>
                            @endif
                        </td>
                        <td class="align-middle px-4">
                            {{ ucfirst(str_replace('_', ' ', $order->delivery_method ?? 'home_delivery')) }}<br>
                            @if($order->deliveryArea)
                                <small class="text-muted">{{ $order->deliveryArea->name_en }} - ${{ number_format($order->delivery_fee ?? 0, 2) }}</small>
                            @elseif($order->pickup_location)
                                <small class="text-muted">{{ ucfirst($order->pickup_location) }}</small>
                            @else
                                <small class="text-muted">No area</small>
                            @endif
                            @if($order->deliveryUser)
                                <br><small class="text-muted">Assigned: {{ $order->deliveryUser->name }}</small>
                            @endif
                        </td>
                        <td class="align-middle px-4">
                            <span class="badge bg-light text-dark">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="align-middle px-4 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                @if(auth()->user()->hasRole('delivery'))
                                    @if($order->status !== 'delivered')
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-sm btn-success">Delivered</button>
                                        </form>
                                    @endif
                                @else
                                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                            <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Ready</option>
                                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                @endif
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-cart fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No orders found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
