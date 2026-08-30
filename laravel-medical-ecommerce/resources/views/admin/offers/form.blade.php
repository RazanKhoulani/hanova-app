<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.title') }} ({{ __('admin.english') }})</label>
        <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $offer?->title_en) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ __('admin.title') }} ({{ __('admin.arabic') }})</label>
        <input type="text" name="title_ar" class="form-control" dir="rtl" value="{{ old('title_ar', $offer?->title_ar) }}" required>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.description') }} ({{ __('admin.english') }})</label>
        <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', $offer?->description_en) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ __('admin.description') }} ({{ __('admin.arabic') }})</label>
        <textarea name="description_ar" class="form-control" dir="rtl" rows="3">{{ old('description_ar', $offer?->description_ar) }}</textarea>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.discount_type') }}</label>
        <select name="discount_type" class="form-select" required>
            <option value="percentage" @selected(old('discount_type', $offer?->discount_type ?? 'percentage') === 'percentage')>{{ __('admin.percentage') }}</option>
            <option value="fixed" @selected(old('discount_type', $offer?->discount_type) === 'fixed')>{{ __('admin.fixed_amount') }}</option>
        </select>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.discount_value') }}</label>
        <input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="{{ old('discount_value', $offer?->discount_value ?? 0) }}" required>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.target_segment') }}</label>
        <select name="target_segment" class="form-select" required>
            @foreach(['all', 'new_user', 'loyal_patient', 'before_after_uploaded', 'has_completed_appointment'] as $value)
                <option value="{{ $value }}" @selected(old('target_segment', $offer?->target_segment ?? 'all') === $value)>{{ __('admin.target_' . $value) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">{{ __('admin.priority') }}</label>
        <input type="number" min="0" name="priority" class="form-control" value="{{ old('priority', $offer?->priority ?? 0) }}">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.starts_at') }}</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $offer?->starts_at?->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.ends_at') }}</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $offer?->ends_at?->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <label class="form-label fw-bold">{{ __('admin.offer_image') }}</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($offer?->image)
            <div class="form-text">{{ __('admin.replace_image_hint') }}</div>
        @endif
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <label class="form-check mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer?->is_active ?? true))>
            <span class="form-check-label">{{ __('admin.active') }}</span>
        </label>
    </div>
</div>
