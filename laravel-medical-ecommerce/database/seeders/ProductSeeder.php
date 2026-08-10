<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name_ar' => 'غسول طبي لطيف',
                'name_en' => 'Gentle Medical Cleanser',
                'price' => 15,
                'cost' => 10,
                'category' => 'Cleansers',
                'image' => 'products/cleanser.png',
                'description_ar' => 'غسول يومي لطيف يساعد على تنظيف البشرة بدون تجفيف، مناسب للبشرة الحساسة والمعرضة للحبوب.',
                'description_en' => 'Gentle daily cleanser that removes buildup without drying the skin, suitable for sensitive and acne-prone skin.',
            ],
            [
                'name_ar' => 'مرطب طبي',
                'name_en' => 'Medical Moisturizer',
                'price' => 20,
                'cost' => 12,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'مرطب داعم لحاجز البشرة يخفف الجفاف والتهيج ويحافظ على ترطيب متوازن.',
                'description_en' => 'Barrier-supporting moisturizer that reduces dryness and irritation while maintaining balanced hydration.',
            ],
            [
                'name_ar' => 'واقي شمس SPF 50',
                'name_en' => 'Sunscreen SPF 50',
                'price' => 25,
                'cost' => 18,
                'category' => 'Sun Protection',
                'image' => 'products/sunscreen.png',
                'description_ar' => 'واقي شمس واسع الطيف أساسي لعلاج التصبغات وحماية نتائج العلاج.',
                'description_en' => 'Broad-spectrum sunscreen essential for pigmentation care and preserving treatment results.',
            ],
            [
                'name_ar' => 'سيروم نياسيناميد وأربوتين',
                'name_en' => 'Niacinamide & Arbutin Serum',
                'price' => 30,
                'cost' => 20,
                'category' => 'Serums',
                'image' => 'products/serum.png',
                'description_ar' => 'سيروم يساعد على تهدئة الاحمرار وتحسين آثار الحبوب والتصبغات الخفيفة.',
                'description_en' => 'Serum that helps calm redness and improve acne marks and mild pigmentation.',
            ],
            [
                'name_ar' => 'كريم أزيليك أسيد',
                'name_en' => 'Azelaic Acid Cream',
                'price' => 22,
                'cost' => 15,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'كريم مناسب للحبوب والاحمرار وبعض آثار التصبغ، يستخدم ضمن خطة تدريجية.',
                'description_en' => 'Cream suitable for acne, redness, and some pigmentation marks as part of a gradual plan.',
            ],
            [
                'name_ar' => 'سيروم كافيين للهالات',
                'name_en' => 'Caffeine Serum',
                'price' => 18,
                'cost' => 10,
                'category' => 'Serums',
                'image' => 'products/serum.png',
                'description_ar' => 'سيروم خفيف لمنطقة حول العين يساعد على مظهر الانتفاخ والهالات المرتبطة بالإرهاق.',
                'description_en' => 'Light eye serum that helps the appearance of puffiness and fatigue-related dark circles.',
            ],
            [
                'name_ar' => 'كريم تفتيح التصبغات',
                'name_en' => 'Pigmentation Whitening Cream',
                'price' => 35,
                'cost' => 25,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'كريم موجه للتصبغات والبقع، يستخدم مع واقي الشمس وتحت متابعة عند الكلف.',
                'description_en' => 'Targeted cream for pigmentation and spots, used with sunscreen and clinical follow-up for melasma.',
            ],
            [
                'name_ar' => 'سيروم فيتامين C',
                'name_en' => 'Vitamin C Serum',
                'price' => 40,
                'cost' => 28,
                'category' => 'Serums',
                'image' => 'products/serum.png',
                'description_ar' => 'سيروم مضاد أكسدة يدعم إشراقة البشرة ويساعد في خطط التصبغات.',
                'description_en' => 'Antioxidant serum that supports brightness and pigmentation routines.',
            ],
            [
                'name_ar' => 'مقشر غليكوليك أسيد',
                'name_en' => 'Glycolic Acid Peeling',
                'price' => 28,
                'cost' => 20,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'مقشر كيميائي خفيف يساعد على ملمس البشرة وآثار الحبوب والتصبغات السطحية.',
                'description_en' => 'Mild chemical exfoliant for texture, acne marks, and superficial pigmentation.',
            ],
            [
                'name_ar' => 'دوكسيسيكلين مضاد التهاب فموي',
                'name_en' => 'Doxycycline (Oral Anti-inflammatory)',
                'price' => 10,
                'cost' => 5,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'دواء فموي للحبوب الالتهابية يستخدم فقط بوصفة ومتابعة طبية.',
                'description_en' => 'Oral anti-inflammatory acne medication used only with prescription and medical follow-up.',
            ],
            [
                'name_ar' => 'إيزوتريتينوين روكتان',
                'name_en' => 'Isotretinoin (Roaccutane)',
                'price' => 50,
                'cost' => 40,
                'category' => 'Skin Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'علاج للحبوب الشديدة والمتكررة، يحتاج تقييم وتحاليل ومتابعة طبية صارمة.',
                'description_en' => 'Treatment for severe recurring acne that requires assessment, lab checks, and strict medical follow-up.',
            ],
            [
                'name_ar' => 'لوشن تصبغات الجسم',
                'name_en' => 'Body Pigmentation Lotion',
                'price' => 32,
                'cost' => 21,
                'category' => 'Body Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'لوشن للجسم يساعد على تحسين التصبغات الناتجة عن الاحتكاك والجفاف مع الترطيب المنتظم.',
                'description_en' => 'Body lotion that helps pigmentation caused by friction and dryness with consistent moisturization.',
            ],
            [
                'name_ar' => 'كريم علامات التمدد',
                'name_en' => 'Stretch Marks Repair Cream',
                'price' => 34,
                'cost' => 22,
                'category' => 'Body Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'كريم داعم لمرونة الجلد ومظهر علامات التمدد الحديثة، ونتائجه تحتاج التزام ووقت.',
                'description_en' => 'Cream that supports skin elasticity and the appearance of newer stretch marks; results need consistency.',
            ],
            [
                'name_ar' => 'جل السيلوليت وشد الجسم',
                'name_en' => 'Cellulite Firming Gel',
                'price' => 38,
                'cost' => 26,
                'category' => 'Body Care',
                'image' => 'products/moisturizer.png',
                'description_ar' => 'جل مساعد لمظهر السيلوليت وشد الجلد، يستخدم مع المساج والحركة وخطة غذائية مناسبة.',
                'description_en' => 'Supportive gel for cellulite appearance and firmness, used with massage, movement, and nutrition planning.',
            ],
            [
                'name_ar' => 'سيروم تقوية الشعر',
                'name_en' => 'Hair Strengthening Serum',
                'price' => 29,
                'cost' => 18,
                'category' => 'Hair Care',
                'image' => 'products/serum.png',
                'description_ar' => 'سيروم داعم لفروة الرأس والشعر الضعيف، ويجب تقييم التساقط المتكرر لمعرفة السبب الداخلي.',
                'description_en' => 'Scalp-supporting serum for weak hair; recurring shedding should be assessed for internal causes.',
            ],
            [
                'name_ar' => 'بخاخ فروة الرأس',
                'name_en' => 'Scalp Support Spray',
                'price' => 24,
                'cost' => 16,
                'category' => 'Hair Care',
                'image' => 'products/serum.png',
                'description_ar' => 'بخاخ داعم لفروة الرأس في حالات التساقط الخفيف، ولا يغني عن تقييم الحديد والهرمونات عند اللزوم.',
                'description_en' => 'Scalp support spray for mild shedding; it does not replace ferritin and hormone assessment when needed.',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name_en' => $product['name_en']],
                $product
            );
        }
    }
}
