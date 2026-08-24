@extends('admin.layout.app')

@section('title', 'Offers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Offers</h2>
    <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Add Offer
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">Title</th>
                        <th class="border-0 px-4 py-3 text-secondary">Discount</th>
                        <th class="border-0 px-4 py-3 text-secondary">Target</th>
                        <th class="border-0 px-4 py-3 text-secondary">Dates</th>
                        <th class="border-0 px-4 py-3 text-secondary">Status</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">Actions</th>
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
                                {{ $offer->discount_type === 'percentage' ? $offer->discount_value . '%' : number_format($offer->discount_value, 2) . ' ل.س' }}
                            </td>
                            <td class="align-middle px-4"><code>{{ $offer->target_segment }}</code></td>
                            <td class="align-middle px-4 small">
                                <div>From: {{ $offer->starts_at?->format('Y-m-d H:i') ?? 'Now' }}</div>
                                <div>To: {{ $offer->ends_at?->format('Y-m-d H:i') ?? 'No end' }}</div>
                            </td>
                            <td class="align-middle px-4">
                                <span class="badge bg-{{ $offer->is_active ? 'success' : 'secondary' }}">{{ $offer->is_active ? 'Active' : 'Paused' }}</span>
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
                            <td colspan="6" class="text-center py-5 text-muted">No offers found.</td>
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
