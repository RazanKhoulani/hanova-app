@extends('admin.layout.app')

@section('title', __('admin.add_product'))

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-link text-decoration-none text-secondary p-0 me-3">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} fa-lg"></i>
    </a>
    <h2 class="mb-0">{{ __('admin.add_product') }}</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.name_english') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" required placeholder="{{ __('admin.name_en_placeholder') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.name_arabic') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar') }}" required placeholder="{{ __('admin.name_ar_placeholder') }}">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.product_brand') }}</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="{{ __('admin.brand_placeholder') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.catalog_type') }}</label>
                    <select name="catalog_type" class="form-select">
                        @foreach(['product' => 'catalog_product', 'bundle' => 'catalog_bundle', 'session' => 'catalog_session', 'nutrition' => 'catalog_nutrition'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('catalog_type', 'product') === $value)>{{ __('admin.' . $label) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('admin.bundle_components') }}</label>
                <select name="bundle_product_ids[]" class="form-select" multiple size="5">
                    @foreach($bundleProducts as $bundleProduct)
                        <option value="{{ $bundleProduct->id }}" @selected(in_array($bundleProduct->id, old('bundle_product_ids', [])))>{{ $bundleProduct->name_ar }} / {{ $bundleProduct->name_en }}</option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('admin.bundle_components_hint') }}</div>
            </div>

            <div class="row mb-4 g-3">
                @foreach(['price_syp' => ['selling_price_syp', 'ل.س', true], 'price_usd' => ['selling_price_usd', 'USD', true], 'cost_syp' => ['cost_syp', 'ل.س', true], 'cost_usd' => ['cost_usd', 'USD', false]] as $field => [$label, $symbol, $required])
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('admin.'.$label) }} @if($required)<span class="text-danger">*</span>@endif</label>
                        <div class="input-group"><span class="input-group-text bg-light">{{ $symbol }}</span><input type="number" min="0" step="0.01" name="{{ $field }}" class="form-control" value="{{ old($field) }}" @required($required) placeholder="0.00"></div>
                    </div>
                @endforeach
                <div class="col-12"><div class="form-text">{{ __('admin.independent_prices_hint') }}</div></div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold d-block">{{ __('admin.inventory_tracking') }}</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="track_inventory" name="track_inventory" value="1" @checked(old('track_inventory', true))>
                        <label class="form-check-label" for="track_inventory">{{ __('admin.track_units') }}</label>
                    </div>
                    <div class="form-text">{{ __('admin.services_no_stock') }}</div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.current_stock') }}</label>
                    <input type="number" min="0" step="1" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" required>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.low_stock_alert') }}</label>
                    <input type="number" min="0" step="1" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', 5) }}" required>
                    <div class="form-text">{{ __('admin.low_stock_hint') }}</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.commercial_category') }}</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="{{ __('admin.category_placeholder') }}">
                    <div class="form-text">{{ __('admin.commercial_category_hint') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.treatment_concerns') }}</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($concerns as $concern)
                            <label class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="concern_ids[]" value="{{ $concern->id }}" @checked(in_array($concern->id, old('concern_ids', [])))>
                                <span class="form-check-label">{{ $concern->name_ar }} / {{ $concern->name_en }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-text">{{ __('admin.concerns_hint') }}</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">{{ __('admin.description_english') }}</label>
                    <textarea name="description_en" class="form-control" rows="4" placeholder="{{ __('admin.description_placeholder') }}">{{ old('description_en') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('admin.description_arabic') }}</label>
                    <textarea name="description_ar" class="form-control" dir="rtl" rows="4" placeholder="{{ __('admin.description_ar_placeholder') }}">{{ old('description_ar') }}</textarea>
                </div>
            </div>

            <h5 class="mb-3">{{ __('admin.bot_details') }}</h5>
            @foreach(['usage', 'suitable_for', 'active_ingredients', 'warnings'] as $field)
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">{{ __('admin.' . $field) }} ({{ __('admin.english') }})</label><textarea name="{{ $field }}_en" class="form-control" rows="2">{{ old($field.'_en') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">{{ __('admin.' . $field) }} ({{ __('admin.arabic') }})</label><textarea name="{{ $field }}_ar" class="form-control" dir="rtl" rows="2">{{ old($field.'_ar') }}</textarea></div>
            </div>
            @endforeach

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('admin.product_image') }}</label>
                <input type="file" name="image" class="form-control" accept="image/*" capture="environment">
                <div class="form-text text-muted">{{ __('admin.product_image_hint') }}</div>
            </div>

            <hr class="my-4 text-muted">

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary me-2 px-4">{{ __('admin.cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i>{{ __('admin.save_product') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
