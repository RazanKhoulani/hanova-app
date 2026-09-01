@extends('admin.layout.app')

@section('title', __('admin.offers_management'))

@section('content')
@php
    $money = static fn ($amount): string => number_format((float) $amount, 0) . ' ل.س';
@endphp
<div class="page-header">
    <div><p class="eyebrow">{{ __('admin.store') }}</p><h1>{{ __('admin.offers_management') }}</h1><p>{{ __('admin.offers_management') }}</p></div>
    <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('admin.add_offer') }}
    </a>
</div>

<section class="panel-card data-panel">
    <div class="panel-heading"><div><h3>{{ __('admin.offers_management') }}</h3><p>{{ __('admin.dates') }}</p></div><span class="soft-count">{{ $offers->total() }}</span></div>
    <div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 admin-data-table">
                <thead>
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
                                <span class="status-pill {{ $offer->is_active ? 'success' : '' }}">{{ $offer->is_active ? __('admin.active') : __('admin.paused') }}</span>
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
        <div class="pt-4 px-4">
            {{ $offers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</section>
@endsection
