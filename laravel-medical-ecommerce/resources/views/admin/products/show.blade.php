@extends('admin.layout.app')

@section('title', 'Product Details: ' . $product->name_en)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Product Details: {{ $product->name_en }}</h2>
    <div class="btn-group">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

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
                        <small class="d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Price</small>
                        <span class="fw-bold fs-5">{{ number_format($product->price, 2) }} ل.س</span>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-3 py-2 rounded">
                        <small class="d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Cost</small>
                        <span class="fw-bold fs-5">{{ number_format($product->cost, 2) }} ل.س</span>
                    </div>
                </div>

                <div class="badge bg-{{ ($product->price - $product->cost) > 0 ? 'success' : 'danger' }} p-2">
                    Profit: {{ number_format($product->price - $product->cost, 2) }} ل.س
                </div>

                <div class="mt-3">
                    @if(!$product->track_inventory)
                        <span class="badge bg-secondary px-3 py-2">Inventory not tracked</span>
                    @elseif($product->stock_quantity <= 0)
                        <span class="badge bg-danger px-3 py-2">Out of stock</span>
                    @elseif($product->stock_quantity <= $product->low_stock_threshold)
                        <span class="badge bg-warning text-dark px-3 py-2">Low stock: {{ $product->stock_quantity }} units</span>
                    @else
                        <span class="badge bg-success px-3 py-2">Available: {{ $product->stock_quantity }} units</span>
                    @endif
                </div>

                <div class="mt-4 text-start">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">Commercial Category</label>
                    <div>{{ $product->category ?? 'No commercial category' }}</div>

                    <label class="text-muted small text-uppercase fw-bold d-block mt-3 mb-2">Treatment Concerns</label>
                    @forelse($product->concerns as $concern)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-1">{{ $concern->name_ar }} / {{ $concern->name_en }}</span>
                    @empty
                        <span class="text-muted small">No concerns assigned.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-header bg-white fw-bold py-3">Description</div>
            <div class="card-body py-4">
                <div class="mb-5">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">English Description</label>
                    <p class="fs-5">{{ $product->description_en ?? 'No description provided in English.' }}</p>
                </div>

                <div>
                    <label class="text-muted small text-uppercase fw-bold d-block mb-2">Arabic Description</label>
                    <p class="fs-5" dir="rtl">{{ $product->description_ar ?? 'لا يوجد وصف باللغة العربية.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
