<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqTopic;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_returns_active_consultation_topics_in_order_instead_of_all_questions(): void
    {
        $secondTopic = FaqTopic::factory()->create([
            'name_en' => 'Second topic',
            'sort_order' => 2,
        ]);
        $firstTopic = FaqTopic::factory()->create([
            'name_en' => 'First topic',
            'sort_order' => 1,
        ]);
        $hiddenTopic = FaqTopic::factory()->create([
            'name_en' => 'Hidden topic',
            'is_active' => false,
        ]);

        Faq::factory()->for($firstTopic, 'topic')->create(['question_en' => 'First topic question?']);
        Faq::factory()->for($secondTopic, 'topic')->create(['question_en' => 'Second topic question?']);
        Faq::factory()->for($hiddenTopic, 'topic')->create(['question_en' => 'Hidden topic question?']);

        $response = $this->getJson('/api/bot/bootstrap', [
            'Accept-Language' => 'en',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.view', 'topics')
            ->assertJsonPath('data.option_items.0.type', 'topic')
            ->assertJsonPath('data.option_items.0.id', $firstTopic->id);

        $this->assertSame([
            'First topic',
            'Second topic',
            'Book Consultation',
        ], $response->json('data.options'));
        $this->assertNotContains('First topic question?', $response->json('data.options'));
        $this->assertNotContains('Hidden topic', $response->json('data.options'));
    }

    public function test_selecting_a_topic_returns_only_its_questions_in_sequence(): void
    {
        $topic = FaqTopic::factory()->create(['name_en' => 'Acne']);
        $otherTopic = FaqTopic::factory()->create(['name_en' => 'Hair']);
        $second = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'Second acne question?',
            'sort_order' => 2,
        ]);
        $first = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'First acne question?',
            'sort_order' => 1,
        ]);
        Faq::factory()->for($otherTopic, 'topic')->create([
            'question_en' => 'Hair question?',
        ]);

        $response = $this->postJson('/api/bot/ask', [
            'query' => 'Acne',
            'lang' => 'en',
            'option_type' => 'topic',
            'option_id' => $topic->id,
        ])->assertOk()
            ->assertJsonPath('data.view', 'questions')
            ->assertJsonPath('data.topic_id', $topic->id);

        $this->assertSame([
            $first->question_en,
            $second->question_en,
            'Back to consultation topics',
            'Book Consultation',
        ], $response->json('data.options'));
        $this->assertNotContains('Hair question?', $response->json('data.options'));
    }

    public function test_answer_returns_remaining_questions_from_the_same_topic(): void
    {
        $topic = FaqTopic::factory()->create(['name_en' => 'Acne']);
        $otherTopic = FaqTopic::factory()->create(['name_en' => 'Hair']);
        $answered = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'Does this answer come from the database?',
            'answer_en' => 'Yes, this answer is stored in the database.',
            'sort_order' => 1,
        ]);
        $remaining = Faq::factory()->for($topic, 'topic')->create([
            'question_en' => 'What is the next acne question?',
            'sort_order' => 2,
        ]);
        Faq::factory()->for($otherTopic, 'topic')->create([
            'question_en' => 'Unrelated hair question?',
        ]);

        $response = $this->postJson('/api/bot/ask', [
            'query' => $answered->question_en,
            'lang' => 'en',
            'option_type' => 'faq',
            'option_id' => $answered->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.answer', $answered->answer_en)
            ->assertJsonPath('data.faq_id', $answered->id);

        $this->assertContains($remaining->question_en, $response->json('data.options'));
        $this->assertNotContains($answered->question_en, $response->json('data.options'));
        $this->assertNotContains('Unrelated hair question?', $response->json('data.options'));
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

    public function test_faq_seeder_imports_the_original_flutter_hierarchy_without_duplicates(): void
    {
        $this->seed(FaqSeeder::class);
        $this->seed(FaqSeeder::class);

        $this->assertSame(13, FaqTopic::count());
        $this->assertSame(52, Faq::count());
        $this->assertSame(0, Faq::whereNull('faq_topic_id')->count());
        $this->assertTrue(
            FaqTopic::withCount('faqs')->get()->every(fn (FaqTopic $topic) => $topic->faqs_count === 4),
            'Every original Flutter topic should contain its four ordered questions.'
        );
        $this->assertDatabaseHas('faq_topics', [
            'slug' => 'acne',
            'name_en' => 'Acne',
            'name_ar' => 'حب الشباب',
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('faqs', [
            'question_en' => 'Why does my acne keep coming back?',
            'question_ar' => 'لماذا تتكرر الحبوب عندي؟',
            'is_active' => true,
        ]);
    }
}
