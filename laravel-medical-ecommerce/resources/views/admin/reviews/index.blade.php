@extends('admin.layout.app')

@section('title', __('admin.reviews'))

@section('content')
@php
    $money = static fn ($amount): string => number_format((float) $amount, 2) . ' ل.س';
@endphp
<div class="page-header mb-4">
    <div>
        <p class="eyebrow">{{ __('admin.store') }}</p>
        <h1>{{ __('admin.reviews') }}</h1>
        <p>{{ __('admin.reviews_hint') }}</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="px-4 py-3">{{ __('admin.product') }}</th>
                    <th class="px-4 py-3">{{ __('admin.customer') }}</th>
                    <th class="px-4 py-3">{{ __('admin.rating') }}</th>
                    <th class="px-4 py-3">{{ __('admin.comment') }}</th>
                    <th class="px-4 py-3">{{ __('admin.reward_coupon') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('admin.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td class="px-4">
                            <strong>{{ $review->product?->name_en ?? '-' }}</strong>
                            <div class="small text-muted" dir="rtl">{{ $review->product?->name_ar }}</div>
                        </td>
                        <td class="px-4">
                            <strong>{{ $review->user?->name ?? '-' }}</strong>
                            <div class="small text-muted" dir="ltr">{{ $review->user?->phone }}</div>
                        </td>
                        <td class="px-4 text-warning" style="white-space: nowrap;">
                            @for($star = 1; $star <= 5; $star++)
                                <i class="fa{{ $star <= $review->rating ? 's' : 'r' }} fa-star"></i>
                            @endfor
                        </td>
                        <td class="px-4" style="min-width: 220px;">{{ $review->comment ?: __('admin.no_comment') }}</td>
                        <td class="px-4">
                            @if($review->coupon)
                                <code>{{ $review->coupon->code }}</code>
                                <div class="small text-muted">{{ $review->coupon->discount_type === 'percentage' ? $review->coupon->discount_value . '%' : $money($review->coupon->discount_value) }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="px-4 text-end">
                            <form action="{{ route('admin.reviews.visibility', $review) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm {{ $review->is_visible ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                    <i class="fa-solid {{ $review->is_visible ? 'fa-eye-slash' : 'fa-eye' }} me-1"></i>
                                    {{ $review->is_visible ? __('admin.hide') : __('admin.show') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">{{ __('admin.no_reviews') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
