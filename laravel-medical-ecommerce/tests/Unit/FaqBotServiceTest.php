<?php

namespace Tests\Unit;

use App\Models\Faq;
use App\Models\FaqTopic;
use App\Services\FaqBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqBotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_options_are_topics_not_flat_questions(): void
    {
        $topic = FaqTopic::factory()->create([
            'name_ar' => 'حب الشباب',
            'name_en' => 'Acne',
        ]);
        $faq = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'How do I treat acne?',
        ]);

        $result = app(FaqBotService::class)->bootstrap('en');

        $this->assertContains($topic->name_en, $result['options']);
        $this->assertNotContains($faq->question_en, $result['options']);
        $this->assertSame('topic', $result['option_items'][0]['type']);
        $this->assertContains('Book Consultation', $result['options']);
    }

    public function test_topic_selection_returns_only_ordered_active_questions(): void
    {
        $topic = FaqTopic::factory()->create(['name_en' => 'Acne']);
        $otherTopic = FaqTopic::factory()->create(['name_en' => 'Hair']);
        $second = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'Second question?',
            'sort_order' => 2,
        ]);
        $first = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'First question?',
            'sort_order' => 1,
        ]);
        Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'Inactive question?',
            'is_active' => false,
        ]);
        Faq::factory()->for($otherTopic, 'topic')->create([
            'question_en' => 'Other topic question?',
        ]);

        $result = app(FaqBotService::class)->findAnswer('Acne', 'en', [], 'topic', $topic->id);

        $this->assertSame($first->question_en, $result['options'][0]);
        $this->assertSame($second->question_en, $result['options'][1]);
        $this->assertNotContains('Inactive question?', $result['options']);
        $this->assertNotContains('Other topic question?', $result['options']);
    }

    public function test_exact_database_question_returns_answer_and_remaining_topic_questions(): void
    {
        $topic = FaqTopic::factory()->create();
        $faq = Faq::factory()->for($topic, 'topic')->create([
            'question_ar' => 'كيف أهدئ احمرار البشرة؟',
            'question_en' => 'How can I calm skin redness?',
            'answer_ar' => 'استخدمي غسولًا لطيفًا ومرطبًا مناسبًا.',
            'answer_en' => 'Use a gentle cleanser and a suitable moisturizer.',
            'keywords' => 'redness, irritation, احمرار',
            'sort_order' => 1,
        ]);
        $remaining = Faq::factory()->for($topic, 'topic')->create([
            'question_ar' => 'ما السؤال التالي؟',
            'sort_order' => 2,
        ]);

        $result = app(FaqBotService::class)->findAnswer($faq->question_ar, 'ar');

        $this->assertSame($faq->answer_ar, $result['answer']);
        $this->assertSame($faq->id, $result['faq_id']);
        $this->assertNotContains($faq->question_ar, $result['options']);
        $this->assertContains($remaining->question_ar, $result['options']);
        $this->assertContains('العودة لمواضيع الاستشارة', $result['options']);
    }

    public function test_keywords_match_database_answer_even_with_product_context(): void
    {
        $topic = FaqTopic::factory()->create();
        $faq = Faq::factory()->for($topic, 'topic')->create([
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

    public function test_inactive_topic_hides_its_questions_and_answers(): void
    {
        $topic = FaqTopic::factory()->create(['is_active' => false]);
        $faq = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'Should this answer be hidden?',
            'answer_en' => 'This answer must never be returned.',
            'keywords' => 'hidden-answer-keyword',
            'is_active' => true,
        ]);

        $result = app(FaqBotService::class)->findAnswer('hidden-answer-keyword', 'en');

        $this->assertNotSame($faq->answer_en, $result['answer']);
        $this->assertNotContains($topic->name_en, $result['options']);
    }
}
