@extends('admin.layout.app')

@section('title', 'Add Product')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-link text-decoration-none text-secondary p-0 me-3">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>
    <h2 class="mb-0">Add New Product</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Name (English) <span class="text-danger">*</span></label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" required placeholder="e.g. Gentle Cleanser">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Name (Arabic) <span class="text-danger">*</span></label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar') }}" required placeholder="مثال: غسول لطيف">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Selling Price ($) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-dollar-sign text-secondary"></i></span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cost ($) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-tag text-secondary"></i></span>
                        <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost') }}" required placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Commercial Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="e.g. Cleansers, Serums, Sun Protection">
                    <div class="form-text">Used for shelf/category grouping.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Treatment Concerns</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($concerns as $concern)
                            <label class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="concern_ids[]" value="{{ $concern->id }}" @checked(in_array($concern->id, old('concern_ids', [])))>
                                <span class="form-check-label">{{ $concern->name_ar }} / {{ $concern->name_en }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-text">Used by the app filters: acne, pigmentation, hormonal imbalance, etc.</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Description (English)</label>
                    <textarea name="description_en" class="form-control" rows="4" placeholder="Product details and usage instructions...">{{ old('description_en') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-control" dir="rtl" rows="4" placeholder="تفاصيل المنتج وطريقة الاستخدام...">{{ old('description_ar') }}</textarea>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <div class="form-text text-muted">Recommended: square PNG/JPG, max 2MB.</div>
            </div>

            <hr class="my-4 text-muted">

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary me-2 px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
