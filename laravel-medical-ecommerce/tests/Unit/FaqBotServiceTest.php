<?php

namespace Tests\Unit;

use App\Services\FaqBotService;
use PHPUnit\Framework\TestCase;

class FaqBotServiceTest extends TestCase
{
    private FaqBotService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FaqBotService;
    }

    public function test_product_context_does_not_block_topic_questions(): void
    {
        $result = $this->service->findAnswer('Acne', 'en', [
            'product_name' => 'SPF 50 Sunscreen',
            'product_description' => 'Broad spectrum sunscreen.',
        ]);

        $this->assertContains('Why does my acne keep coming back?', $result['options']);
        $this->assertNotContains('Pigmentation', $result['options']);
    }

    public function test_product_context_does_not_block_topic_follow_ups(): void
    {
        $result = $this->service->findAnswer(
            'Why does my acne keep coming back?',
            'en',
            [
                'product_name' => 'SPF 50 Sunscreen',
                'asked_questions' => ['Acne'],
            ]
        );

        $this->assertStringContainsString('Recurring acne', $result['answer']);
        $this->assertNotContains('Why does my acne keep coming back?', $result['options']);
        $this->assertContains('What is the basic acne routine?', $result['options']);
    }

    public function test_general_product_question_keeps_product_context(): void
    {
        $result = $this->service->findAnswer('How do I use it?', 'en', [
            'product_name' => 'SPF 50 Sunscreen',
            'product_description' => 'Broad spectrum sunscreen.',
        ]);

        $this->assertStringContainsString('SPF 50 Sunscreen', $result['answer']);
        $this->assertContains('Acne', $result['options']);
    }
}
