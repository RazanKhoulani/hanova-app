<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">Name (English)</label>
        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $concern?->name_en) }}" required placeholder="e.g. Hormonal Imbalance">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Name (Arabic)</label>
        <input type="text" name="name_ar" class="form-control" dir="rtl" value="{{ old('name_ar', $concern?->name_ar) }}" required placeholder="مثال: اضطراب الهرمونات">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label class="form-label fw-bold">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $concern?->slug) }}" placeholder="auto-generated from English name">
        <div class="form-text">Used by the mobile app filter. Keep it stable after products are linked.</div>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $concern?->is_active ?? true))>
            <span class="form-check-label">Visible in app</span>
        </label>
    </div>
</div>
