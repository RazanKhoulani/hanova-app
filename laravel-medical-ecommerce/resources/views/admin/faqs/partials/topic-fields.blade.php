<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Topic name (English)</label>
        <input type="text" class="form-control" name="name_en" value="{{ old('name_en', $topic?->name_en) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">اسم الموضوع (العربية)</label>
        <input type="text" class="form-control" name="name_ar" value="{{ old('name_ar', $topic?->name_ar) }}" required dir="rtl">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Description (English)</label>
        <textarea class="form-control" name="description_en" rows="3">{{ old('description_en', $topic?->description_en) }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">الوصف (العربية)</label>
        <textarea class="form-control" name="description_ar" rows="3" dir="rtl">{{ old('description_ar', $topic?->description_ar) }}</textarea>
    </div>
</div>
<div class="row align-items-center">
    <div class="col-md-4 mb-3">
        <label class="form-label">Display order</label>
        <input type="number" class="form-control" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $topic?->sort_order ?? 0) }}" required>
    </div>
    <div class="col-md-8 mb-3 pt-md-4">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="topicActive{{ $topic?->id ?? 'New' }}" @checked(old('is_active', $topic?->is_active ?? true))>
            <label class="form-check-label" for="topicActive{{ $topic?->id ?? 'New' }}">Show this topic in the app</label>
        </div>
    </div>
</div>
