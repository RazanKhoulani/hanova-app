@extends('admin.layout.app')

@section('title', __('admin.offers_management'))

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
    <h2>{{ __('admin.offers_management') }}</h2>
    <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('admin.add_offer') }}
    </a>
</div>

@if($rate <= 0)<div class="alert alert-warning border-0 shadow-sm mb-4"><i class="fas fa-triangle-exclamation me-2"></i>{{ __('admin.currency_rate_missing') }}</div>@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.title') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.discount') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.target') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.dates') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.status') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td class="align-middle px-4">
                                <div class="fw-semibold">{{ $offer->title_en }}</div>
                                <div class="text-muted small" dir="rtl">{{ $offer->title_ar }}</div>
                            </td>
                            <td class="align-middle px-4">
                                {{ $offer->discount_type === 'percentage' ? $offer->discount_value . '%' : $money($offer->discount_value) }}
                            </td>
                            <td class="align-middle px-4"><span class="status-pill info">{{ __('admin.target_' . $offer->target_segment) }}</span></td>
                            <td class="align-middle px-4 small">
                                <div>{{ __('admin.from') }}: {{ $offer->starts_at?->format('Y-m-d H:i') ?? __('admin.now') }}</div>
                                <div>{{ __('admin.to') }}: {{ $offer->ends_at?->format('Y-m-d H:i') ?? __('admin.no_end') }}</div>
                            </td>
                            <td class="align-middle px-4">
                                <span class="badge bg-{{ $offer->is_active ? 'success' : 'secondary' }}">{{ $offer->is_active ? __('admin.active') : __('admin.paused') }}</span>
                            </td>
                            <td class="align-middle px-4 text-end">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="d-inline delete-confirm">
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
                            <td colspan="6" class="text-center py-5 text-muted">{{ __('admin.no_offers_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($offers->hasPages())
        <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
            {{ $offers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
