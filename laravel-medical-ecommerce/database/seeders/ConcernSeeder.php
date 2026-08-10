<?php

namespace Database\Seeders;

use App\Models\Concern;
use Illuminate\Database\Seeder;

class ConcernSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['slug' => 'acne', 'name_ar' => 'حب الشباب', 'name_en' => 'Acne'],
            ['slug' => 'hormonal-imbalance', 'name_ar' => 'اضطراب الهرمونات', 'name_en' => 'Hormonal Imbalance'],
            ['slug' => 'pigmentation', 'name_ar' => 'التصبغات', 'name_en' => 'Pigmentation'],
            ['slug' => 'dark-circles', 'name_ar' => 'الهالات السوداء', 'name_en' => 'Dark Circles'],
            ['slug' => 'pores', 'name_ar' => 'توسع المسامات', 'name_en' => 'Enlarged Pores'],
            ['slug' => 'hair-problems', 'name_ar' => 'مشاكل الشعر', 'name_en' => 'Hair Problems'],
            ['slug' => 'cellulite', 'name_ar' => 'السيلوليت', 'name_en' => 'Cellulite'],
            ['slug' => 'stretch-marks', 'name_ar' => 'علامات التمدد', 'name_en' => 'Stretch Marks'],
            ['slug' => 'body-pigmentation', 'name_ar' => 'تصبغات الجسم', 'name_en' => 'Body Pigmentation'],
            ['slug' => 'sun-protection', 'name_ar' => 'واقي شمسي', 'name_en' => 'Sun Protection'],
            ['slug' => 'hydration', 'name_ar' => 'ترطيب', 'name_en' => 'Hydration'],
            ['slug' => 'cleansing', 'name_ar' => 'تنظيف البشرة', 'name_en' => 'Cleansing'],
        ] as $concern) {
            Concern::updateOrCreate(
                ['slug' => $concern['slug']],
                $concern + ['is_active' => true]
            );
        }
    }
}
