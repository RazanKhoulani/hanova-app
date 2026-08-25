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
                    <label class="form-label fw-bold">البراند</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="مثال: Hanova Care">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">نوع الكتالوج</label>
                    <select name="catalog_type" class="form-select">
                        @foreach(['product' => 'منتج عناية', 'bundle' => 'بكج كامل', 'session' => 'جلسة', 'nutrition' => 'تغذية'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('catalog_type', 'product') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">مكونات البكج</label>
                <select name="bundle_product_ids[]" class="form-select" multiple size="5">
                    @foreach($bundleProducts as $bundleProduct)
                        <option value="{{ $bundleProduct->id }}" @selected(in_array($bundleProduct->id, old('bundle_product_ids', [])))>{{ $bundleProduct->name_ar }} / {{ $bundleProduct->name_en }}</option>
                    @endforeach
                </select>
                <div class="form-text">اختاري مكونات البكج عند اختيار نوع «بكج كامل».</div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">سعر البيع (ل.س) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">ل.س</span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">سعر التكلفة (ل.س) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">ل.س</span>
                        <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost') }}" required placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold d-block">Inventory tracking</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="track_inventory" name="track_inventory" value="1" @checked(old('track_inventory', true))>
                        <label class="form-check-label" for="track_inventory">Track available units for this item</label>
                    </div>
                    <div class="form-text">Turn this off for services that do not have physical stock.</div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Current stock</label>
                    <input type="number" min="0" step="1" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" required>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Low-stock alert at</label>
                    <input type="number" min="0" step="1" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', 5) }}" required>
                    <div class="form-text">The dashboard marks the product as low at this quantity or below.</div>
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

            <h5 class="mb-3">Bot Product Details / تفاصيل المنتج للبوت</h5>
            @foreach(['usage' => 'طريقة الاستخدام', 'suitable_for' => 'لمن يناسب', 'active_ingredients' => 'المكونات الفعالة', 'warnings' => 'التحذيرات'] as $field => $label)
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }} (English)</label><textarea name="{{ $field }}_en" class="form-control" rows="2">{{ old($field.'_en') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">{{ $label }} (العربية)</label><textarea name="{{ $field }}_ar" class="form-control" dir="rtl" rows="2">{{ old($field.'_ar') }}</textarea></div>
            </div>
            @endforeach

            <div class="mb-4">
                <label class="form-label fw-bold">Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*" capture="environment">
                <div class="form-text text-muted">يمكن التقاط الصورة بالكاميرا مباشرة. يوصى بصورة مربعة PNG/JPG حتى 10MB، وتُحفظ بالدقة الأصلية.</div>
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
