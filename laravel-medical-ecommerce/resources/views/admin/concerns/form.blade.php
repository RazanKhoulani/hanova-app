<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.name_english') }}</label>
        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $concern?->name_en) }}" required placeholder="{{ __('admin.concern_en_placeholder') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ __('admin.name_arabic') }}</label>
        <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar', $concern?->name_ar) }}" required placeholder="{{ __('admin.concern_ar_placeholder') }}">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.slug') }}</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $concern?->slug) }}" placeholder="{{ __('admin.slug_auto_placeholder') }}">
        <div class="form-text">{{ __('admin.concern_slug_hint') }}</div>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $concern?->is_active ?? true))>
            <span class="form-check-label">{{ __('admin.visible_in_app') }}</span>
        </label>
    </div>
</div>
