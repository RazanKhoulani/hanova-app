@extends('admin.layout.app')

@section('title', __('admin.edit') . ': ' . $product->name_en)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ __('admin.edit') }}: {{ $product->name_en }}</h2>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('admin.back_to_list') }}
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.name_english') }}</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $product->name_en) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.name_arabic') }}</label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar', $product->name_ar) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.base_price') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ __('admin.base_value') }}</span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.base_cost') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ __('admin.base_value') }}</span>
                        <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost', $product->cost) }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold d-block">{{ __('admin.inventory_tracking') }}</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="track_inventory" name="track_inventory" value="1" @checked(old('track_inventory', $product->track_inventory))>
                        <label class="form-check-label" for="track_inventory">{{ __('admin.track_units') }}</label>
                    </div>
                    <div class="form-text">{{ __('admin.services_no_stock') }}</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">{{ __('admin.current_stock') }}</label>
                    <input type="number" min="0" step="1" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">{{ __('admin.low_stock_alert') }}</label>
                    <input type="number" min="0" step="1" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.commercial_category') }}</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" placeholder="{{ __('admin.category_placeholder') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.product_brand') }}</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}" placeholder="{{ __('admin.brand_placeholder') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.catalog_type') }}</label>
                    <select name="catalog_type" class="form-select">
                        @foreach(['product' => 'catalog_product', 'bundle' => 'catalog_bundle', 'session' => 'catalog_session', 'nutrition' => 'catalog_nutrition'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('catalog_type', $product->catalog_type ?? 'product') === $value)>{{ __('admin.' . $label) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.treatment_concerns') }}</label>
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

                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('admin.bundle_components') }}</label>
                    @php($selectedBundleProducts = old('bundle_product_ids', $product->bundle_product_ids ?? []))
                    <select name="bundle_product_ids[]" class="form-select" multiple size="5">
                        @foreach($bundleProducts as $bundleProduct)
                            <option value="{{ $bundleProduct->id }}" @selected(in_array($bundleProduct->id, $selectedBundleProducts))>{{ $bundleProduct->name_ar }} / {{ $bundleProduct->name_en }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('admin.bundle_components_hint') }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.description_english') }}</label>
                    <textarea name="description_en" class="form-control" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.description_arabic') }}</label>
                    <textarea name="description_ar" class="form-control" dir="rtl" rows="4">{{ old('description_ar', $product->description_ar) }}</textarea>
                </div>

                <div class="col-12"><h5 class="mt-2">{{ __('admin.bot_details') }}</h5></div>
                    @foreach(['usage', 'suitable_for', 'active_ingredients', 'warnings'] as $field)
                    <div class="col-md-6"><label class="form-label">{{ __('admin.' . $field) }} ({{ __('admin.english') }})</label><textarea name="{{ $field }}_en" class="form-control" rows="2">{{ old($field.'_en', $product->{$field.'_en'}) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label">{{ __('admin.' . $field) }} ({{ __('admin.arabic') }})</label><textarea name="{{ $field }}_ar" class="form-control" dir="rtl" rows="2">{{ old($field.'_ar', $product->{$field.'_ar'}) }}</textarea></div>
                @endforeach

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.product_image') }}</label>
                    <input type="file" name="image" class="form-control mb-2" accept="image/*" capture="environment">
                    @if($product->image)
                        <div class="mt-2 text-muted small">{{ __('admin.current_image') }}:</div>
                        <img src="{{ asset('storage/'.$product->image) }}" class="rounded shadow-sm mt-1" width="100">
                    @endif
                    <div class="form-text">{{ __('admin.replace_image_hint') }} {{ __('admin.product_image_hint') }}</div>
                </div>

                <div class="col-12 mt-5">
                    <hr>
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <i class="fas fa-save me-2"></i>{{ __('admin.update_product') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
