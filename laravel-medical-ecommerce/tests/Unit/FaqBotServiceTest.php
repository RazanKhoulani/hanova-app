<?php

namespace Tests\Unit;

use App\Models\Faq;
use App\Services\FaqBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqBotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_options_come_from_active_database_faqs(): void
    {
        $activeFaq = Faq::factory()->create([
            'question_ar' => 'كيف أهدئ احمرار البشرة؟',
            'question_en' => 'How can I calm skin redness?',
            'is_active' => true,
        ]);
        $inactiveFaq = Faq::factory()->create([
            'question_ar' => 'سؤال مخفي',
            'question_en' => 'Hidden question',
            'is_active' => false,
        ]);

        $result = app(FaqBotService::class)->bootstrap('en');

        $this->assertContains($activeFaq->question_en, $result['options']);
        $this->assertNotContains($inactiveFaq->question_en, $result['options']);
        $this->assertContains('Book Consultation', $result['options']);
    }

    public function test_exact_database_question_returns_its_localized_answer(): void
    {
        $faq = Faq::factory()->create([
            'question_ar' => 'كيف أهدئ احمرار البشرة؟',
            'question_en' => 'How can I calm skin redness?',
            'answer_ar' => 'استخدمي غسولًا لطيفًا ومرطبًا مناسبًا.',
            'answer_en' => 'Use a gentle cleanser and a suitable moisturizer.',
            'keywords' => 'redness, irritation, احمرار',
        ]);

        $result = app(FaqBotService::class)->findAnswer($faq->question_ar, 'ar');

        $this->assertSame($faq->answer_ar, $result['answer']);
        $this->assertSame($faq->id, $result['faq_id']);
        $this->assertNotContains($faq->question_ar, $result['options']);
    }

    public function test_keywords_match_database_answer_even_with_product_context(): void
    {
        $faq = Faq::factory()->create([
            'question_en' => 'How can I calm skin redness?',
            'answer_en' => 'Use a gentle cleanser and a suitable moisturizer.',
            'keywords' => "redness, irritation\nreactive skin",
        ]);

        $result = app(FaqBotService::class)->findAnswer('My skin has irritation', 'en', [
            'product_name' => 'Calming Cream',
            'product_description' => 'A lightweight facial cream.',
        ]);

        $this->assertSame($faq->answer_en, $result['answer']);
        $this->assertSame($faq->id, $result['faq_id']);
    }

    public function test_inactive_faq_is_neither_suggested_nor_used_as_an_answer(): void
    {
        $faq = Faq::factory()->create([
            'question_en' => 'Should this answer be hidden?',
            'answer_en' => 'This answer must never be returned.',
            'keywords' => 'hidden-answer-keyword',
            'is_active' => false,
        ]);

        $result = app(FaqBotService::class)->findAnswer('hidden-answer-keyword', 'en');

        $this->assertNotSame($faq->answer_en, $result['answer']);
        $this->assertNotContains($faq->question_en, $result['options']);
    }
}
