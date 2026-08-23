<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Consultation topic</label>
        <select class="form-select" name="faq_topic_id" required>
            <option value="">Select topic</option>
            @foreach($topics as $topicOption)
                <option value="{{ $topicOption->id }}" @selected((string) old('faq_topic_id', $faq?->faq_topic_id) === (string) $topicOption->id)>
                    {{ $topicOption->sort_order }} — {{ $topicOption->name_en }} / {{ $topicOption->name_ar }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Question order in topic</label>
        <input type="number" class="form-control" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $faq?->sort_order ?? 0) }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Question (English)</label>
        <input type="text" class="form-control" name="question_en" value="{{ old('question_en', $faq?->question_en) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">السؤال (العربية)</label>
        <input type="text" class="form-control" name="question_ar" value="{{ old('question_ar', $faq?->question_ar) }}" required dir="rtl">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Answer (English)</label>
        <textarea class="form-control" name="answer_en" rows="5" required>{{ old('answer_en', $faq?->answer_en) }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">الجواب (العربية)</label>
        <textarea class="form-control" name="answer_ar" rows="5" required dir="rtl">{{ old('answer_ar', $faq?->answer_ar) }}</textarea>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Keywords (Arabic and English, comma separated)</label>
    <input type="text" class="form-control" name="keywords" value="{{ old('keywords', $faq?->keywords) }}" placeholder="e.g. acne, hormones, حبوب, هرمونات">
    <div class="form-text">Keywords are used only when the user types a free-form question.</div>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="faqActive{{ $faq?->id ?? 'New' }}" @checked(old('is_active', $faq?->is_active ?? true))>
    <label class="form-check-label" for="faqActive{{ $faq?->id ?? 'New' }}">Show this question in its topic and allow the bot to answer it</label>
</div>
