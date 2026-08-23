<?php

namespace Database\Factories;

use App\Models\FaqTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FaqTopic>
 */
class FaqTopicFactory extends Factory
{
    protected $model = FaqTopic::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'name_ar' => 'موضوع '.fake()->unique()->word(),
            'name_en' => ucfirst($name),
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
