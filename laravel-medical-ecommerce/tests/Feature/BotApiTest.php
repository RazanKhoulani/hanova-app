<?php

namespace Tests\Feature;

use App\Models\Faq;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_returns_all_active_questions_in_the_requested_language(): void
    {
        $activeFaq = Faq::factory()->create([
            'question_ar' => 'سؤال عربي ديناميكي؟',
            'question_en' => 'A dynamic English question?',
            'is_active' => true,
        ]);
        $inactiveFaq = Faq::factory()->create([
            'question_ar' => 'سؤال عربي مخفي؟',
            'question_en' => 'A hidden English question?',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/bot/bootstrap', [
            'Accept-Language' => 'en',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertContains($activeFaq->question_en, $response->json('data.options'));
        $this->assertNotContains($inactiveFaq->question_en, $response->json('data.options'));
    }

    public function test_ask_returns_the_answer_added_to_the_database(): void
    {
        $faq = Faq::factory()->create([
            'question_ar' => 'هل هذا الجواب من قاعدة البيانات؟',
            'question_en' => 'Does this answer come from the database?',
            'answer_ar' => 'نعم، هذا الجواب مخزن في قاعدة البيانات.',
            'answer_en' => 'Yes, this answer is stored in the database.',
            'keywords' => 'database answer, جواب قاعدة البيانات',
        ]);

        $this->postJson('/api/bot/ask', [
            'query' => $faq->question_en,
            'lang' => 'en',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.answer', $faq->answer_en)
            ->assertJsonPath('data.faq_id', $faq->id);
    }

    public function test_public_faq_list_excludes_inactive_records_without_pagination(): void
    {
        Faq::factory()->count(17)->create(['is_active' => true]);
        Faq::factory()->create(['is_active' => false]);

        $this->getJson('/api/faqs?lang=en')
            ->assertOk()
            ->assertJsonCount(17, 'data')
            ->assertJsonMissing(['is_active' => false]);
    }

    public function test_faq_seeder_imports_all_legacy_bot_content_without_duplicates(): void
    {
        $this->seed(FaqSeeder::class);
        $this->seed(FaqSeeder::class);

        $this->assertSame(52, Faq::count());
        $this->assertDatabaseHas('faqs', [
            'question_en' => 'Why does my acne keep coming back?',
            'question_ar' => 'لماذا تتكرر الحبوب عندي؟',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('faqs', [
            'question_en' => 'When should I book a consultation?',
            'question_ar' => 'متى أحجز استشارة؟',
            'is_active' => true,
        ]);
    }
}
