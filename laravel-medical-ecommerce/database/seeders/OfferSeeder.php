<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Offer::updateOrCreate(
            ['title_en' => 'Welcome Skincare Offer'],
            [
                'title_ar' => 'عرض العناية الأول',
                'description_ar' => 'خصم ترحيبي على طلبك القادم عند التسجيل.',
                'description_en' => 'A welcome discount for your next skincare order.',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'target_segment' => 'new_user',
                'priority' => 10,
                'is_active' => true,
            ]
        );
    }
}
