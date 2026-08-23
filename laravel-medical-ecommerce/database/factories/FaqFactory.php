<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_ar' => fake()->unique()->sentence().'؟',
            'question_en' => fake()->unique()->sentence().'?',
            'answer_ar' => fake()->paragraph(),
            'answer_en' => fake()->paragraph(),
            'keywords' => implode(', ', fake()->words(3)),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
