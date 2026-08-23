<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">{{ __('admin.consultation_topic') }}</label>
        <select class="form-select" name="faq_topic_id" required>
            <option value="">{{ __('admin.select_topic') }}</option>
            @foreach($topics as $topicOption)
                <option value="{{ $topicOption->id }}" @selected((string) old('faq_topic_id', $faq?->faq_topic_id) === (string) $topicOption->id)>
                    {{ $topicOption->sort_order }} — {{ $topicOption->name_en }} / {{ $topicOption->name_ar }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('admin.question_order_in_topic') }}</label>
        <input type="number" class="form-control" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $faq?->sort_order ?? 0) }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('admin.question_en') }}</label>
        <input type="text" class="form-control" name="question_en" value="{{ old('question_en', $faq?->question_en) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('admin.question_ar') }}</label>
        <input type="text" class="form-control" name="question_ar" value="{{ old('question_ar', $faq?->question_ar) }}" required dir="rtl">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('admin.answer_en') }}</label>
        <textarea class="form-control" name="answer_en" rows="5" required>{{ old('answer_en', $faq?->answer_en) }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('admin.answer_ar') }}</label>
        <textarea class="form-control" name="answer_ar" rows="5" required dir="rtl">{{ old('answer_ar', $faq?->answer_ar) }}</textarea>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('admin.keywords_bilingual') }}</label>
    <input type="text" class="form-control" name="keywords" value="{{ old('keywords', $faq?->keywords) }}" placeholder="{{ __('admin.keywords_placeholder') }}">
    <div class="form-text">{{ __('admin.keywords_hint') }}</div>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="faqActive{{ $faq?->id ?? 'New' }}" @checked(old('is_active', $faq?->is_active ?? true))>
    <label class="form-check-label" for="faqActive{{ $faq?->id ?? 'New' }}">{{ __('admin.show_question_in_bot') }}</label>
</div>
