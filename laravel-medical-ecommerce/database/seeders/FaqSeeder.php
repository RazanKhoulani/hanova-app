<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question_ar' => 'ما سبب تكرار حب الشباب العميق؟',
                'question_en' => 'What causes recurring deep acne?',
                'answer_ar' => 'تكرار الحبوب يعني أن المشكلة ليست سطحية فقط. غالبا يوجد سبب داخلي مثل اضطراب الهرمونات، مقاومة الإنسولين، نمط غذائي غير مناسب، أو توتر. العلاج الموضعي يساعد، لكنه لا يكفي وحده إذا كانت الحبوب متكررة أو مؤلمة، ويجب تقييم السبب ووضع خطة علاجية مناسبة.',
                'answer_en' => 'Recurring deep acne usually means the issue is not only superficial. Possible internal drivers include hormonal imbalance, insulin resistance, diet, or stress. Topicals can help, but persistent or painful acne needs assessment and a structured treatment plan.',
                'keywords' => 'acne, deep acne, cystic acne, pimples, حب الشباب, الحبوب, حبوب عميقة, حبوب كيسية',
            ],
            [
                'question_ar' => 'ما الروتين المناسب للتصبغات والكلف؟',
                'question_en' => 'What routine helps pigmentation and melasma?',
                'answer_ar' => 'التصبغات تحتاج التزام يومي بواقي الشمس أولا، ثم يمكن إضافة غسول لطيف، مرطب، كريم تفتيح، وفيتامين C أو تقشير خفيف حسب نوع التصبغ. الكلف الهرموني يحتاج تقييم السبب الداخلي ولا يكفي علاجه بالكريمات فقط.',
                'answer_en' => 'Pigmentation care starts with daily sunscreen. A routine may include a gentle cleanser, moisturizer, brightening cream, vitamin C, or mild exfoliation depending on depth. Hormonal melasma also needs internal trigger assessment, not creams alone.',
                'keywords' => 'pigmentation, melasma, dark spots, تصبغات, التصبغات, كلف, بقع',
            ],
            [
                'question_ar' => 'كيف أعالج تصبغات الجسم؟',
                'question_en' => 'How can body pigmentation be treated?',
                'answer_ar' => 'تصبغات الجسم غالبا ترتبط بالاحتكاك، الجفاف، إزالة الشعر، أو التهاب سابق. الخطة تكون بتقليل الاحتكاك، ترطيب منتظم، واقي شمس للمناطق المكشوفة، ومقشر أو كريم تفتيح مناسب للجسم حسب حساسية الجلد.',
                'answer_en' => 'Body pigmentation is often related to friction, dryness, hair removal, or previous inflammation. Care focuses on reducing friction, consistent moisturization, sunscreen for exposed areas, and suitable body brightening or exfoliating products.',
                'keywords' => 'body pigmentation, body darkening, friction, تصبغات الجسم, سواد الجسم, احتكاك',
            ],
            [
                'question_ar' => 'ما أسباب الهالات السوداء؟',
                'question_en' => 'What causes dark circles?',
                'answer_ar' => 'الهالات السوداء قد تكون بسبب إرهاق، وراثة، حساسية، نقص حديد أو B12، تصبغ، أو تجويف تحت العين. السيرومات تساعد بالمظهر الخفيف، لكن الهالات المستمرة تحتاج تحديد السبب قبل اختيار العلاج.',
                'answer_en' => 'Dark circles may be caused by fatigue, genetics, allergy, iron or B12 deficiency, pigmentation, or under-eye hollowing. Serums can help mild appearance, but persistent circles need cause-based evaluation.',
                'keywords' => 'dark circles, under eye, الهالات السوداء, هالات, تحت العين',
            ],
            [
                'question_ar' => 'كيف أتعامل مع توسع المسامات؟',
                'question_en' => 'How can enlarged pores be managed?',
                'answer_ar' => 'توسع المسامات يتحسن بتنظيم الدهون وتنظيف لطيف وتجنب الفرك. يمكن استخدام نياسيناميد أو تقشير خفيف تدريجي، لكن لا يوجد منتج يغلق المسام نهائيا. الهدف هو تحسين المظهر والملمس.',
                'answer_en' => 'Enlarged pores improve by controlling oil, gentle cleansing, and avoiding harsh scrubbing. Niacinamide or gradual mild exfoliation can help, but no product closes pores permanently. The goal is better texture and appearance.',
                'keywords' => 'pores, large pores, enlarged pores, مسامات, توسع المسامات, مسام واسعة',
            ],
            [
                'question_ar' => 'ما أسباب مشاكل وتساقط الشعر؟',
                'question_en' => 'What causes hair issues and shedding?',
                'answer_ar' => 'تساقط الشعر قد يرتبط بنقص الحديد أو فيتامين D أو اضطرابات الغدة والهرمونات أو التوتر أو ما بعد الولادة. السيرومات داعمة فقط، أما التساقط المتكرر فيحتاج تحليل السبب وخطة علاجية.',
                'answer_en' => 'Hair shedding may be related to ferritin or vitamin D deficiency, thyroid or hormonal issues, stress, or postpartum changes. Serums are supportive, but recurring shedding needs cause assessment and a treatment plan.',
                'keywords' => 'hair issues, hair loss, hair shedding, scalp, مشاكل الشعر, تساقط الشعر, فروة الرأس',
            ],
            [
                'question_ar' => 'ما علاج السيلوليت؟',
                'question_en' => 'What helps cellulite?',
                'answer_ar' => 'السيلوليت مرتبط ببنية الجلد والدهون والدورة الدموية، ولا يختفي بكريم فقط. التحسن يحتاج حركة، ترطيب، مساج، خطة غذائية، وقد يفيد جل موضعي كعامل مساعد لتحسين المظهر.',
                'answer_en' => 'Cellulite is related to skin structure, fat distribution, and circulation. Creams alone do not remove it. Improvement needs movement, hydration, massage, nutrition planning, and topical firming products as support.',
                'keywords' => 'cellulite, firming, سيلوليت, السيلوليت, شد الجسم',
            ],
            [
                'question_ar' => 'هل يمكن علاج علامات التمدد؟',
                'question_en' => 'Can stretch marks be treated?',
                'answer_ar' => 'علامات التمدد الحديثة الحمراء تستجيب أفضل من القديمة البيضاء. العلاج يركز على ترطيب قوي، دعم مرونة الجلد، وممكن جلسات عيادية عند الحاجة. النتائج تدريجية ولا تختفي العلامات بالكامل غالبا.',
                'answer_en' => 'New red stretch marks respond better than older white ones. Care focuses on strong moisturization, supporting elasticity, and clinic sessions when needed. Results are gradual and marks often do not disappear completely.',
                'keywords' => 'stretch marks, striae, علامات التمدد, تمدد الجلد',
            ],
            [
                'question_ar' => 'ما علاقة اضطراب الهرمونات بالبشرة؟',
                'question_en' => 'How do hormonal imbalances affect skin?',
                'answer_ar' => 'اضطراب الهرمونات قد يظهر على شكل حب شباب متكرر، تساقط شعر، كلف، أو زيادة دهون البشرة. عند وجود تكرار أو أعراض مرافقة، الأفضل تقييم طبي بدل الاعتماد على منتجات موضعية فقط.',
                'answer_en' => 'Hormonal imbalance can show as recurring acne, hair shedding, melasma, or increased oiliness. If the issue repeats or comes with other symptoms, medical assessment is better than relying only on topical products.',
                'keywords' => 'hormonal imbalance, hormones, pcos, اضطراب الهرمونات, هرمونات, تكيس',
            ],
            [
                'question_ar' => 'ما أهمية الترطيب في الروتين؟',
                'question_en' => 'Why is hydration important in a routine?',
                'answer_ar' => 'الترطيب يحمي حاجز البشرة ويقلل التهيج والجفاف، وهذا مهم حتى للبشرة الدهنية والمعرضة للحبوب. اختيار المرطب يعتمد على نوع البشرة وحساسية الجلد.',
                'answer_en' => 'Hydration protects the skin barrier and reduces irritation and dryness, even for oily or acne-prone skin. The moisturizer should match skin type and sensitivity.',
                'keywords' => 'hydration, moisturizer, dry skin, ترطيب, مرطب, جفاف',
            ],
            [
                'question_ar' => 'كيف أختار الغسول المناسب؟',
                'question_en' => 'How do I choose the right cleanser?',
                'answer_ar' => 'الغسول المناسب ينظف بدون شد أو حرقان. للبشرة الحساسة أو المعرضة للحبوب نبدأ بغسول لطيف، ونبتعد عن الفرك القاسي أو التنظيف المتكرر لأنه يزيد التهيج.',
                'answer_en' => 'A suitable cleanser cleans without tightness or burning. For sensitive or acne-prone skin, start gentle and avoid harsh scrubbing or over-cleansing because it increases irritation.',
                'keywords' => 'cleansing, cleanser, wash, غسول, تنظيف البشرة, غسيل الوجه',
            ],
            [
                'question_ar' => 'لماذا واقي الشمس ضروري؟',
                'question_en' => 'Why is sunscreen necessary?',
                'answer_ar' => 'واقي الشمس هو أهم خطوة في علاج التصبغات وآثار الحبوب وحماية البشرة بعد التقشير أو التفتيح. بدون واقي شمس منتظم قد ترجع التصبغات حتى مع أفضل المنتجات.',
                'answer_en' => 'Sunscreen is the most important step for pigmentation, acne marks, and protecting skin after exfoliation or brightening. Without consistent sunscreen, pigmentation can return even with good products.',
                'keywords' => 'sun protection, sunscreen, spf, واقي شمسي, واقي الشمس, حماية الشمس',
            ],
            [
                'question_ar' => 'لست متأكدة ما مشكلة بشرتي، ماذا أفعل؟',
                'question_en' => 'I am not sure what my skin concern is. What should I do?',
                'answer_ar' => 'إذا لم تكوني متأكدة، ابدئي بوصف المشكلة: متى ظهرت، هل يوجد ألم أو حكة، هل تتكرر، وما المنتجات المستخدمة حاليا. إذا كانت المشكلة مستمرة أو تتفاقم، احجزي استشارة لتقييم أدق.',
                'answer_en' => 'If you are not sure, start by describing when it appeared, whether there is pain or itching, if it recurs, and what products you currently use. If it persists or worsens, book a consultation for accurate assessment.',
                'keywords' => 'not sure, unknown, unsure, غير متأكدة, لا أعرف, مو متأكدة',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question_en' => $faq['question_en']],
                $faq + ['is_active' => true]
            );
        }
    }
}
