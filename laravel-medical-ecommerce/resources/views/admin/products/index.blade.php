@extends('admin.layout.app')

@section('title', __('admin.products_management'))

@section('content')
@php
    $pricing = \App\Models\AppSetting::pricingValues();
    $displayCurrency = $pricing['display_currency'] === 'usd' ? 'usd' : 'syp_new';
    $rate = (float) $pricing[$displayCurrency === 'usd' ? 'syp_old_per_usd' : 'syp_old_per_new'];
    $currencyLabel = $displayCurrency === 'usd' ? 'USD' : __('admin.currency_syp_new');
    $money = static fn ($amount): string => $rate > 0
        ? number_format(((float) $amount) / $rate, 2) . ' ' . $currencyLabel
        : '—';
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.products_management') }}</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>{{ __('admin.add_product') }}</a>
</div>

@if($rate <= 0)<div class="alert alert-warning border-0 shadow-sm mb-4"><i class="fas fa-triangle-exclamation me-2"></i>{{ __('admin.currency_rate_missing') }}</div>@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">{{ __('admin.tracked_products') }}</small><div class="fs-3 fw-bold">{{ $inventorySummary['tracked'] }}</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">{{ __('admin.available') }}</small><div class="fs-3 fw-bold text-success">{{ $inventorySummary['available'] }}</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">{{ __('admin.low_stock') }}</small><div class="fs-3 fw-bold text-warning">{{ $inventorySummary['low'] }}</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">{{ __('admin.out_of_stock') }}</small><div class="fs-3 fw-bold text-danger">{{ $inventorySummary['out'] }}</div></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.id') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.image') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.name_english') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.name_arabic') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.concern') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.price') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.cost') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.stock') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="clickable-row" data-href="{{ route('admin.products.show', $product->id) }}">
                        <td class="align-middle px-4">{{ $product->id }}</td>
                        <td class="align-middle px-4">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name_en }}" width="40" height="40" class="rounded object-fit-cover shadow-sm">
                            @else
                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td class="align-middle px-4 fw-medium text-dark">{{ $product->name_en }}</td>
                        <td class="align-middle px-4 text-muted">{{ $product->name_ar }}</td>
                        <td class="align-middle px-4">
                            @forelse($product->concerns as $concern)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-1">{{ $concern->name_ar }}</span>
                            @empty
                                <span class="text-muted small">{{ __('admin.no_concerns') }}</span>
                            @endforelse
                        </td>
                        <td class="align-middle px-4"><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">{{ $money($product->price) }}</span></td>
                        <td class="align-middle px-4"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1">{{ $money($product->cost) }}</span></td>
                        <td class="align-middle px-4" style="min-width: 190px;">
                            @if(!$product->track_inventory)
                                <span class="badge bg-secondary">{{ __('admin.not_tracked') }}</span>
                            @else
                                @php($stockClass = $product->stock_quantity <= 0 ? 'danger' : ($product->stock_quantity <= $product->low_stock_threshold ? 'warning text-dark' : 'success'))
                                <span class="badge bg-{{ $stockClass }} mb-2">
                                    {{ $product->stock_quantity <= 0 ? __('admin.out_of_stock') : $product->stock_quantity . ' ' . __('admin.units') }}
                                </span>
                                <form action="{{ route('admin.products.stock.update', $product->id) }}" method="POST" class="d-flex gap-1" onclick="event.stopPropagation()">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" min="0" step="1" name="stock_quantity" class="form-control form-control-sm" value="{{ $product->stock_quantity }}" aria-label="{{ __('admin.stock_quantity_for', ['name' => $product->name_en]) }}">
                                    <button class="btn btn-sm btn-outline-primary" type="submit" title="{{ __('admin.save_stock') }}"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                        </td>
                        <td class="align-middle px-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="{{ __('admin.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">{{ __('admin.no_products_found') }}</p>
                            <small>{{ __('admin.add_first_product') }}</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
