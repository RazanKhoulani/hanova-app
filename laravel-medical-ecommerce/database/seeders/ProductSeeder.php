<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\BundledProductImagePublisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        app(BundledProductImagePublisher::class)->publishMissing();

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
            $image = $product['image'] ?? null;
            unset($product['image']);

            $storedProduct = Product::firstOrNew(['name_en' => $product['name_en']]);
            $storedProduct->fill($product);

            if ($image && Storage::disk('public')->exists($image)) {
                if (! $storedProduct->exists || ! $storedProduct->image) {
                    $storedProduct->image = $image;
                }
            } elseif ($storedProduct->image === $image) {
                $storedProduct->image = null;
            }

            $storedProduct->save();
        }

        $catalogItems = [
            [
                'name_ar' => 'بكج روتين التصبغات',
                'name_en' => 'Pigmentation Routine Bundle',
                'price' => 62,
                'cost' => 45,
                'category' => 'Bundles',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'bundle',
                'bundle_product_names' => [
                    'Gentle Medical Cleanser',
                    'Niacinamide & Arbutin Serum',
                    'Sunscreen SPF 50',
                ],
                'description_ar' => 'روتين متكامل للتصبغات يجمع الغسول اللطيف وسيروم النياسيناميد والأربوتين وواقي الشمس بسعر بكج خاص.',
                'description_en' => 'A complete pigmentation routine with a gentle cleanser, niacinamide and arbutin serum, and SPF 50 at a bundle price.',
                'usage_ar' => 'الغسول صباحاً ومساءً، السيروم مساءً بالتدرج، وواقي الشمس صباحاً مع التجديد.',
                'usage_en' => 'Cleanse morning and evening, introduce the serum gradually at night, and use SPF every morning with reapplication.',
            ],
            [
                'name_ar' => 'بكج العناية بالبشرة المعرضة للحبوب',
                'name_en' => 'Acne Care Starter Bundle',
                'price' => 69,
                'cost' => 49,
                'category' => 'Bundles',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'bundle',
                'bundle_product_names' => [
                    'Gentle Medical Cleanser',
                    'Azelaic Acid Cream',
                    'Medical Moisturizer',
                    'Sunscreen SPF 50',
                ],
                'description_ar' => 'بداية عملية وروتينية للبشرة المعرضة للحبوب تشمل التنظيف والعلاج والترطيب والحماية اليومية.',
                'description_en' => 'A practical starter routine for acne-prone skin covering cleansing, treatment, moisturizing, and daily protection.',
                'usage_ar' => 'يُستخدم الكريم العلاجي تدريجياً وفق تحمّل البشرة، مع الالتزام بالترطيب وواقي الشمس.',
                'usage_en' => 'Introduce the treatment cream gradually based on tolerance, while maintaining moisturizer and sunscreen.',
            ],
            [
                'name_ar' => 'بكج دعم الشعر وفروة الرأس',
                'name_en' => 'Hair and Scalp Support Bundle',
                'price' => 46,
                'cost' => 31,
                'category' => 'Bundles',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'bundle',
                'bundle_product_names' => [
                    'Hair Strengthening Serum',
                    'Scalp Support Spray',
                ],
                'description_ar' => 'بكج داعم للشعر الضعيف وفروة الرأس مع تعليمات استخدام واضحة، ولا يغني عن تقييم أسباب التساقط المتكرر.',
                'description_en' => 'A supportive bundle for weak hair and scalp with clear usage guidance; recurring hair loss still needs assessment.',
                'usage_ar' => 'يطبّق على فروة رأس نظيفة حسب التعليمات، مع التوقف عند حدوث تهيج.',
                'usage_en' => 'Apply to a clean scalp as directed and stop if irritation occurs.',
            ],
            [
                'name_ar' => 'جلسة تنظيف عميق للبشرة',
                'name_en' => 'Deep Skin Cleansing Session',
                'price' => 35,
                'cost' => 18,
                'category' => 'Clinic Sessions',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'session',
                'description_ar' => 'جلسة عيادة مدتها 60 دقيقة لتنظيف البشرة وتقييم احتياجاتها واختيار عناية منزلية مناسبة.',
                'description_en' => 'A 60-minute clinic session for deep cleansing, skin assessment, and tailored home-care guidance.',
                'suitable_for_ar' => 'البشرة المجهدة، المسام والدهون المتراكمة والحاجة إلى تنظيف احترافي.',
                'suitable_for_en' => 'Congested or tired skin, visible pores, and clients who need professional cleansing.',
            ],
            [
                'name_ar' => 'جلسة عناية بالتصبغات',
                'name_en' => 'Pigmentation Care Session',
                'price' => 45,
                'cost' => 24,
                'category' => 'Clinic Sessions',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'session',
                'description_ar' => 'جلسة عيادة مدتها 60 دقيقة تُحدد تفاصيلها بعد تقييم نوع التصبغ وحالة البشرة.',
                'description_en' => 'A 60-minute clinic session tailored after assessing the pigmentation type and current skin condition.',
                'warnings_ar' => 'نوع الجلسة والمواد المستخدمة يحددان بعد التقييم، وقد يلزم تأجيلها عند تهيج البشرة.',
                'warnings_en' => 'The procedure and products are selected after assessment and may be postponed if the skin is irritated.',
            ],
            [
                'name_ar' => 'تقييم الشعر وفروة الرأس',
                'name_en' => 'Hair and Scalp Assessment',
                'price' => 25,
                'cost' => 12,
                'category' => 'Clinic Sessions',
                'brand' => 'Hanova Clinic',
                'catalog_type' => 'session',
                'description_ar' => 'موعد تقييم مدته 30 دقيقة لمراجعة نمط التساقط والعناية الحالية وتحديد الخطوة التالية.',
                'description_en' => 'A 30-minute assessment to review hair loss patterns, current care, and the appropriate next step.',
            ],
            [
                'name_ar' => 'استشارة تغذية أونلاين',
                'name_en' => 'Online Nutrition Consultation',
                'price' => 18,
                'cost' => 8,
                'category' => 'Nutrition',
                'brand' => 'Hanova Nutrition',
                'catalog_type' => 'nutrition',
                'description_ar' => 'استشارة تغذية أونلاين مدتها 15 دقيقة لمراجعة الهدف والعادات الحالية وتحديد بداية مناسبة.',
                'description_en' => 'A 15-minute online nutrition consultation to review goals, current habits, and define a suitable starting point.',
            ],
            [
                'name_ar' => 'خطة تغذية شخصية لمدة شهر',
                'name_en' => 'Personalized Monthly Nutrition Plan',
                'price' => 42,
                'cost' => 20,
                'category' => 'Nutrition',
                'brand' => 'Hanova Nutrition',
                'catalog_type' => 'nutrition',
                'description_ar' => 'خطة شهرية مرنة تُبنى على الهدف ونمط الحياة، مع بدائل عملية قابلة للتطبيق والمتابعة.',
                'description_en' => 'A flexible monthly plan based on goals and lifestyle, with practical alternatives and follow-up.',
                'suitable_for_ar' => 'تنظيم الوجبات، تحسين العادات، إدارة الوزن ودعم أهداف العناية العامة.',
                'suitable_for_en' => 'Meal organization, habit improvement, weight management, and general wellness goals.',
            ],
            [
                'name_ar' => 'متابعة تغذية أونلاين',
                'name_en' => 'Online Nutrition Follow-up',
                'price' => 14,
                'cost' => 6,
                'category' => 'Nutrition',
                'brand' => 'Hanova Nutrition',
                'catalog_type' => 'nutrition',
                'description_ar' => 'جلسة متابعة أونلاين لمراجعة الالتزام والنتائج وتعديل الخطة حسب الحاجة.',
                'description_en' => 'An online follow-up to review adherence and results and adjust the plan as needed.',
            ],
        ];

        foreach ($catalogItems as $catalogItem) {
            $bundleProductNames = $catalogItem['bundle_product_names'] ?? [];
            unset($catalogItem['bundle_product_names']);

            $catalogItem['bundle_product_ids'] = $bundleProductNames === []
                ? null
                : Product::query()
                    ->whereIn('name_en', $bundleProductNames)
                    ->pluck('id')
                    ->values()
                    ->all();
            $catalogItem['track_inventory'] = false;
            $catalogItem['stock_quantity'] = 0;
            $catalogItem['low_stock_threshold'] = 0;

            Product::updateOrCreate(
                ['name_en' => $catalogItem['name_en']],
                $catalogItem,
            );
        }
    }

}
