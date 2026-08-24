@extends('admin.layout.app')

@section('title', 'Order Details #' . $order->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Order Details: #{{ $order->id }}</h2>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Items -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Order Items</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3 text-center">Price</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($item->product->image)
                                            <img src="{{ asset('storage/'.$item->product->image) }}" width="40" class="rounded me-3">
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $item->product->name_en }}</div>
                                            <small class="text-muted">{{ $item->product->name_ar }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">${{ number_format($item->price, 2) }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-end fw-bold">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @php($itemsSubtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity))
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end px-4 py-2">Items Subtotal:</td>
                                <td class="text-end px-4 py-2">${{ number_format($itemsSubtotal, 2) }}</td>
                            </tr>
                            @if(($order->discount_amount ?? 0) > 0)
                                <tr>
                                    <td colspan="3" class="text-end px-4 py-2 text-success">Discount:</td>
                                    <td class="text-end px-4 py-2 text-success">-${{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            @if(($order->delivery_fee ?? 0) > 0)
                                <tr>
                                    <td colspan="3" class="text-end px-4 py-2">Delivery Fee:</td>
                                    <td class="text-end px-4 py-2">${{ number_format($order->delivery_fee, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end fw-bold px-4 py-3">Total Amount:</td>
                                <td class="text-end fw-bold text-primary fs-5 px-4 py-3">${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Order Status & Customer -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Order Status</div>
            <div class="card-body">
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <label class="form-label text-muted small text-uppercase fw-bold">Current Status</label>
                    <div class="mb-3">
                        @if($order->is_confirmed)
                            <span class="badge bg-success mb-2"><i class="fas fa-check-circle me-1"></i> Confirmed by Phone</span>
                        @else
                            <span class="badge bg-warning text-dark mb-2"><i class="fas fa-clock me-1"></i> Waiting Confirmation</span>
                        @endif
                    </div>
                    @if(auth()->user()->hasRole('delivery'))
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn btn-success w-100" @disabled($order->status === 'delivered')>Mark Delivered</button>
                    @else
                        <select name="status" class="form-select mb-3">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    @endif
                </form>
                
                <hr>
                
                @unless(auth()->user()->hasRole('delivery'))
                    <form action="{{ route('admin.orders.uploadReceipt', $order->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label text-muted small text-uppercase fw-bold">Shipping Receipt (Image)</label>
                        <input type="file" name="shipping_receipt" class="form-control mb-2" accept="image/*" required>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">Upload Receipt</button>
                    </form>
                @endunless
            </div>
        </div>

        @if($order->shipping_receipt)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Shipping Receipt</div>
            <div class="card-body text-center">
                <a href="{{ asset('storage/' . $order->shipping_receipt) }}" target="_blank">
                    <img src="{{ asset('storage/' . $order->shipping_receipt) }}" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                </a>
            </div>
        </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Customer Information</div>
            <div class="card-body">
                <p class="mb-1 fw-bold">{{ $order->user->name ?? 'Unknown Customer' }}</p>
                <p class="mb-3 text-muted">{{ $order->user->phone ?? 'N/A' }}</p>
                
                <label class="form-label text-muted small text-uppercase fw-bold">Shipping Address</label>
                <p>{{ $order->shipping_address ?? 'No address provided' }}</p>

                <label class="form-label text-muted small text-uppercase fw-bold">Delivery</label>
                <p class="mb-1">{{ ucfirst(str_replace('_', ' ', $order->delivery_method ?? 'home_delivery')) }}</p>
                @if($order->delivery_method === 'qadmous')
                    <div class="alert alert-light border">
                        <strong>Qadmous:</strong> {{ $order->qadmous_governorate }} / {{ $order->qadmous_branch }}<br>
                        <strong>Recipient:</strong> {{ $order->recipient_name }} — {{ $order->recipient_phone }}
                    </div>
                    <form method="POST" action="{{ route('admin.orders.updateTracking', $order->id) }}" class="input-group mb-3">@csrf @method('PUT')
                        <input name="tracking_number" value="{{ $order->tracking_number }}" class="form-control" placeholder="Qadmous tracking number" required>
                        <button class="btn btn-outline-primary">Save tracking</button>
                    </form>
                @endif
                @if($order->deliveryArea)
                    <p class="mb-1 text-muted">{{ $order->deliveryArea->name_en }} - ${{ number_format($order->delivery_fee ?? 0, 2) }}</p>
                @elseif($order->pickup_location)
                    <p class="mb-1 text-muted">{{ ucfirst($order->pickup_location) }}</p>
                @endif
                @if($order->deliveryUser)
                    <p class="mb-3 text-muted">Assigned delivery: {{ $order->deliveryUser->name }} {{ $order->deliveryUser->phone ? '(' . $order->deliveryUser->phone . ')' : '' }}</p>
                @endif
                
                <hr>
                <p class="mb-0"><strong>Payment:</strong> {{ ucfirst($order->payment_method) }}</p>
                @if($order->payment_status)
                    <p class="mb-0"><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
                @endif
                @if($order->coupon)
                    <p class="mb-0"><strong>Coupon:</strong> {{ $order->coupon->code }} (-{{ $order->coupon->discount_type === 'percentage' ? $order->coupon->discount_value . '%' : '$' . number_format($order->coupon->discount_value, 2) }})</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
