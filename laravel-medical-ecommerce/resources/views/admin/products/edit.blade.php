@extends('admin.layout.app')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Product: {{ $product->name_en }}</h2>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Name (English)</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $product->name_en) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar', $product->name_ar) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Selling Price ($)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Cost Price ($)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost', $product->cost) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Commercial Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" placeholder="e.g. Cleansers, Serums, Sun Protection">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Treatment Concerns</label>
                    @php($selectedConcerns = old('concern_ids', $product->concerns->pluck('id')->all()))
                    <div class="border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($concerns as $concern)
                            <label class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="concern_ids[]" value="{{ $concern->id }}" @checked(in_array($concern->id, $selectedConcerns))>
                                <span class="form-check-label">{{ $concern->name_ar }} / {{ $concern->name_en }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Description (English)</label>
                    <textarea name="description_en" class="form-control" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-control" dir="rtl" rows="4">{{ old('description_ar', $product->description_ar) }}</textarea>
                </div>

                <div class="col-12"><h5 class="mt-2">Bot Product Details / تفاصيل المنتج للبوت</h5></div>
                @foreach(['usage' => 'طريقة الاستخدام', 'suitable_for' => 'لمن يناسب', 'active_ingredients' => 'المكونات الفعالة', 'warnings' => 'التحذيرات'] as $field => $label)
                    <div class="col-md-6"><label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }} (English)</label><textarea name="{{ $field }}_en" class="form-control" rows="2">{{ old($field.'_en', $product->{$field.'_en'}) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label">{{ $label }} (العربية)</label><textarea name="{{ $field }}_ar" class="form-control" dir="rtl" rows="2">{{ old($field.'_ar', $product->{$field.'_ar'}) }}</textarea></div>
                @endforeach

                <div class="col-md-6">
                    <label class="form-label fw-bold">Product Image</label>
                    <input type="file" name="image" class="form-control mb-2">
                    @if($product->image)
                        <div class="mt-2 text-muted small">Current Image:</div>
                        <img src="{{ asset('storage/'.$product->image) }}" class="rounded shadow-sm mt-1" width="100">
                    @endif
                    <div class="form-text">Leave blank to keep current image.</div>
                </div>

                <div class="col-12 mt-5">
                    <hr>
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <i class="fas fa-save me-2"></i> Update Product
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
