<?php

namespace Database\Seeders;

use App\Models\Concern;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductConcernSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Gentle Medical Cleanser' => ['cleansing', 'acne', 'pores'],
            'Medical Moisturizer' => ['hydration', 'acne'],
            'Sunscreen SPF 50' => ['sun-protection', 'pigmentation', 'body-pigmentation'],
            'Niacinamide & Arbutin Serum' => ['pigmentation', 'pores', 'acne'],
            'Azelaic Acid Cream' => ['acne', 'pigmentation'],
            'Caffeine Serum' => ['dark-circles'],
            'Pigmentation Whitening Cream' => ['pigmentation', 'body-pigmentation'],
            'Vitamin C Serum' => ['pigmentation', 'sun-protection'],
            'Glycolic Acid Peeling' => ['acne', 'pores', 'pigmentation', 'body-pigmentation'],
            'Doxycycline (Oral Anti-inflammatory)' => ['acne', 'hormonal-imbalance'],
            'Isotretinoin (Roaccutane)' => ['acne', 'hormonal-imbalance'],
            'Body Pigmentation Lotion' => ['body-pigmentation', 'hydration'],
            'Stretch Marks Repair Cream' => ['stretch-marks', 'hydration'],
            'Cellulite Firming Gel' => ['cellulite'],
            'Hair Strengthening Serum' => ['hair-problems', 'hormonal-imbalance'],
            'Scalp Support Spray' => ['hair-problems'],
        ];

        foreach ($map as $productName => $concernSlugs) {
            $product = Product::where('name_en', $productName)->first();

            if (! $product) {
                continue;
            }

            $concernIds = Concern::whereIn('slug', $concernSlugs)->pluck('id')->all();
            $product->concerns()->sync($concernIds);
        }
    }
}
