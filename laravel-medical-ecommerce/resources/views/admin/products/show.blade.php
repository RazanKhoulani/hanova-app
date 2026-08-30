@extends('admin.layout.app')

@section('title', __('admin.product_details') . ': ' . $product->name_en)

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
    <h2>{{ __('admin.product_details') }}: {{ $product->name_en }}</h2>
    <div class="btn-group">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
        </a>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>{{ __('admin.edit') }}
        </a>
    </div>
</div>

@if($rate <= 0)<div class="alert alert-warning border-0 shadow-sm mb-4"><i class="fas fa-triangle-exclamation me-2"></i>{{ __('admin.currency_rate_missing') }}</div>@endif

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-body text-center p-4">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" class="img-fluid rounded shadow-sm mb-4" style="max-height: 250px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-4 mx-auto" style="width: 200px; height: 200px;">
                        <i class="fas fa-image fa-3x text-secondary"></i>
                    </div>
                @endif

                <h4 class="fw-bold mb-1">{{ $product->name_en }}</h4>
                <div class="text-muted mb-3">{{ $product->name_ar }}</div>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <div class="bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded">
                        <small class="d-block text-uppercase fw-bold" style="font-size: 0.65rem;">{{ __('admin.price') }}</small>
                        <span class="fw-bold fs-5">{{ $money($product->price) }}</span>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-3 py-2 rounded">
                        <small class="d-block text-uppercase fw-bold" style="font-size: 0.65rem;">{{ __('admin.cost') }}</small>
                        <span class="fw-bold fs-5">{{ $money($product->cost) }}</span>
                    </div>
                </div>

                <div class="badge bg-{{ ($product->price - $product->cost) > 0 ? 'success' : 'danger' }} p-2">
                    {{ __('admin.profit') }}: {{ $money($product->price - $product->cost) }}
                </div>

                <div class="mt-3">
                    @if(!$product->track_inventory)
                        <span class="badge bg-secondary px-3 py-2">{{ __('admin.inventory_not_tracked') }}</span>
                    @elseif($product->stock_quantity <= 0)
                        <span class="badge bg-danger px-3 py-2">{{ __('admin.out_of_stock') }}</span>
                    @elseif($product->stock_quantity <= $product->low_stock_threshold)
                        <span class="badge bg-warning text-dark px-3 py-2">{{ __('admin.low_stock_units', ['count' => $product->stock_quantity]) }}</span>
                    @else
                        <span class="badge bg-success px-3 py-2">{{ __('admin.available_units', ['count' => $product->stock_quantity]) }}</span>
                    @endif
                </div>

                <div class="mt-4 text-start">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">{{ __('admin.commercial_category') }}</label>
                    <div>{{ $product->category ?? __('admin.no_commercial_category') }}</div>

                    <label class="text-muted small text-uppercase fw-bold d-block mt-3 mb-2">{{ __('admin.treatment_concerns') }}</label>
                    @forelse($product->concerns as $concern)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-1">{{ $concern->name_ar }} / {{ $concern->name_en }}</span>
                    @empty
                        <span class="text-muted small">{{ __('admin.no_concerns') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-header bg-white fw-bold py-3">{{ __('admin.description') }}</div>
            <div class="card-body py-4">
                <div class="mb-5">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">{{ __('admin.description_english') }}</label>
                    <p class="fs-5">{{ $product->description_en ?? __('admin.no_description_en') }}</p>
                </div>

                <div>
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">{{ __('admin.description_arabic') }}</label>
                    <p class="fs-5" dir="rtl">{{ $product->description_ar ?? __('admin.no_description_ar') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
