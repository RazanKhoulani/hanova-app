<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = array_merge($this->detailedFaqs(), [
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
        ]);

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question_en' => $faq['question_en']],
                $faq + ['is_active' => true]
            );
        }
    }

    private function detailedFaqs(): array
    {
        return [
            [
                'question_ar' => 'لماذا تتكرر الحبوب عندي؟',
                'question_en' => 'Why does my acne keep coming back?',
                'answer_ar' => 'تكرار الحبوب يعني أن السبب قد لا يكون سطحيا فقط. الأسباب الشائعة: اضطراب الهرمونات، مقاومة الإنسولين، التوتر، نمط الغذاء، أو استخدام منتجات غير مناسبة. لذلك نحتاج معرفة النمط والمكان والتكرار قبل اختيار العلاج.',
                'answer_en' => 'Recurring acne may not be only superficial. Common triggers include hormones, insulin resistance, stress, diet, or unsuitable products. The pattern, location, and frequency guide the treatment plan.',
                'keywords' => 'recurring acne, breakouts, acne causes, تكرار الحبوب, أسباب الحبوب, حبوب متكررة',
            ],
            [
                'question_ar' => 'ما الروتين الأساسي لحب الشباب؟',
                'question_en' => 'What is the basic acne routine?',
                'answer_ar' => 'الروتين الأساسي يكون: غسول لطيف، مرطب خفيف، واقي شمس، ثم علاج موجه مثل أزيليك أسيد أو نياسيناميد أو علاج دوائي حسب شدة الحالة. لا يفضل الجمع بين عدة مقشرات من البداية.',
                'answer_en' => 'A basic acne routine includes a gentle cleanser, lightweight moisturizer, sunscreen, then targeted care such as azelaic acid, niacinamide, or medication depending on severity. Avoid starting many exfoliants together.',
                'keywords' => 'acne routine, acne skincare, روتين الحبوب, روتين حب الشباب, علاج الحبوب',
            ],
            [
                'question_ar' => 'متى أحتاج علاج دوائي للحبوب؟',
                'question_en' => 'When does acne need medication?',
                'answer_ar' => 'إذا كانت الحبوب عميقة، مؤلمة، تترك آثارا، أو تتكرر رغم الروتين، فقد تحتاج علاج دوائي ومتابعة طبية. لا ننصح بالأدوية الفموية بدون تقييم وتحاليل عند الحاجة.',
                'answer_en' => 'If acne is deep, painful, scarring, or persistent despite routine care, it may need medication and medical follow-up. Oral medication should not be used without assessment.',
                'keywords' => 'acne medication, severe acne, painful acne, دواء الحبوب, علاج دوائي, حبوب مؤلمة',
            ],
            [
                'question_ar' => 'هل التصبغات بسبب الشمس؟',
                'question_en' => 'Is pigmentation caused by the sun?',
                'answer_ar' => 'الشمس من أهم أسباب زيادة التصبغات وتثبيتها، حتى لو كان السبب الأساسي هرمونيا أو التهابيا. لذلك واقي الشمس اليومي هو أول خطوة، وبدونه قد لا تظهر نتيجة واضحة من كريمات التفتيح.',
                'answer_en' => 'Sun exposure is one of the strongest reasons pigmentation worsens or persists, even when the original trigger is hormonal or inflammatory. Daily sunscreen is the first step.',
                'keywords' => 'sun pigmentation, sun spots, تصبغات الشمس, بقع الشمس, الشمس والتصبغات',
            ],
            [
                'question_ar' => 'ما الفرق بين الكلف وآثار الحبوب؟',
                'question_en' => 'What is the difference between melasma and acne marks?',
                'answer_ar' => 'آثار الحبوب غالبا تظهر بعد التهاب أو حبة وتكون محددة بمكانها. الكلف غالبا أوسع ومتكرر وقد يرتبط بالهرمونات والشمس. الخطة تختلف، لذلك مهم تحديد النوع قبل العلاج.',
                'answer_en' => 'Acne marks usually appear where inflammation happened. Melasma is often broader, recurrent, and linked to hormones and sun exposure. Treatment differs, so identifying the type matters.',
                'keywords' => 'melasma vs acne marks, acne scars, كلف, آثار الحبوب, الفرق بين الكلف وآثار الحبوب',
            ],
            [
                'question_ar' => 'كم يحتاج علاج التصبغات؟',
                'question_en' => 'How long does pigmentation treatment take?',
                'answer_ar' => 'علاج التصبغات يحتاج وقتا والتزاما. التحسن غالبا يظهر تدريجيا خلال أسابيع إلى أشهر حسب العمق والسبب والالتزام بواقي الشمس. التصبغات العميقة أو الهرمونية تحتاج متابعة أطول.',
                'answer_en' => 'Pigmentation improves gradually over weeks to months depending on depth, cause, and sunscreen consistency. Deep or hormonal pigmentation usually needs longer follow-up.',
                'keywords' => 'pigmentation duration, pigmentation results, مدة علاج التصبغات, نتيجة التفتيح, علاج الكلف',
            ],
            [
                'question_ar' => 'ما أسباب سواد مناطق الجسم؟',
                'question_en' => 'What causes body darkening?',
                'answer_ar' => 'الأسباب الشائعة: الاحتكاك، الجفاف، إزالة الشعر، التهاب سابق، زيادة الوزن، أو مقاومة الإنسولين في بعض المناطق. العلاج يبدأ بتقليل السبب وليس التفتيح فقط.',
                'answer_en' => 'Common causes include friction, dryness, hair removal, previous inflammation, weight changes, or insulin resistance in some areas. Treatment starts by reducing the trigger.',
                'keywords' => 'body darkening causes, friction pigmentation, سواد الجسم, أسباب التصبغات, احتكاك',
            ],
            [
                'question_ar' => 'هل التقشير مناسب للجسم؟',
                'question_en' => 'Is exfoliation suitable for body pigmentation?',
                'answer_ar' => 'التقشير قد يساعد، لكن يجب أن يكون تدريجيا ومناسبا للجسم. الإفراط بالتقشير أو الفرك يزيد الالتهاب والتصبغ. الترطيب وتقليل الاحتكاك مهمان مع أي مقشر.',
                'answer_en' => 'Exfoliation can help, but it should be gradual and body-safe. Over-exfoliation or scrubbing can worsen inflammation and pigmentation. Moisturizing and friction control remain essential.',
                'keywords' => 'body exfoliation, pigmentation exfoliation, تقشير الجسم, مقشر للجسم, تقشير التصبغات',
            ],
            [
                'question_ar' => 'متى أحتاج فحص للتصبغات؟',
                'question_en' => 'When should body pigmentation be checked?',
                'answer_ar' => 'إذا كان السواد مخمليا أو سريع الانتشار أو في الرقبة وتحت الإبط مع زيادة وزن أو اضطراب دورة، يفضل تقييم مقاومة الإنسولين والهرمونات.',
                'answer_en' => 'If darkening is velvety, spreading quickly, or appears on the neck/underarms with weight gain or cycle changes, insulin resistance and hormones should be assessed.',
                'keywords' => 'pigmentation check, insulin resistance pigmentation, فحص التصبغات, سواد الرقبة, مقاومة الإنسولين',
            ],
            [
                'question_ar' => 'هل الهالات من نقص الفيتامينات؟',
                'question_en' => 'Can deficiencies cause dark circles?',
                'answer_ar' => 'نقص الحديد أو B12 أو الإرهاق قد يزيد مظهر الهالات عند بعض الأشخاص، لكنه ليس السبب الوحيد. يجب النظر للوراثة والحساسية والتجويف تحت العين أيضا.',
                'answer_en' => 'Iron or B12 deficiency and fatigue can worsen dark circles for some people, but they are not the only causes. Genetics, allergy, and hollowing also matter.',
                'keywords' => 'dark circles deficiency, iron b12, نقص الفيتامينات, نقص الحديد, هالات',
            ],
            [
                'question_ar' => 'هل سيروم الكافيين يكفي للهالات؟',
                'question_en' => 'Is caffeine serum enough for dark circles?',
                'answer_ar' => 'سيروم الكافيين قد يساعد الانتفاخ والمظهر الخفيف المرتبط بالإرهاق، لكنه لا يكفي إذا كانت الهالات بسبب تصبغ عميق أو نقص أو تجويف.',
                'answer_en' => 'Caffeine serum may help puffiness and mild fatigue-related darkness, but it is not enough for deep pigmentation, deficiencies, or hollowing.',
                'keywords' => 'caffeine serum, under eye serum, سيروم الكافيين, سيروم الهالات, انتفاخ العين',
            ],
            [
                'question_ar' => 'متى تحتاج الهالات استشارة؟',
                'question_en' => 'When do dark circles need consultation?',
                'answer_ar' => 'إذا كانت الهالات شديدة، مفاجئة، مع تعب عام، أو لا تتحسن بالنوم والترطيب، يفضل تقييم السبب واختيار الخطة المناسبة.',
                'answer_en' => 'If dark circles are severe, sudden, linked with fatigue, or do not improve with sleep and hydration, a cause-based assessment is recommended.',
                'keywords' => 'dark circles consultation, severe dark circles, استشارة الهالات, هالات شديدة, علاج الهالات',
            ],
            [
                'question_ar' => 'هل يمكن إغلاق المسامات؟',
                'question_en' => 'Can pores be closed?',
                'answer_ar' => 'لا يمكن إغلاق المسامات نهائيا لأنها جزء طبيعي من الجلد. الهدف هو تقليل مظهرها بتنظيم الدهون وتحسين الملمس.',
                'answer_en' => 'Pores cannot be permanently closed because they are a normal part of skin. The goal is to reduce their appearance by controlling oil and improving texture.',
                'keywords' => 'close pores, open pores, إغلاق المسام, إغلاق المسامات, مسام مفتوحة',
            ],
            [
                'question_ar' => 'ما أفضل روتين للمسامات؟',
                'question_en' => 'What routine helps enlarged pores?',
                'answer_ar' => 'غسول لطيف، مرطب خفيف، واقي شمس، ثم نياسيناميد أو تقشير خفيف تدريجي. تجنبي الفرك القاسي لأنه يزيد التهيج.',
                'answer_en' => 'Use a gentle cleanser, lightweight moisturizer, sunscreen, then niacinamide or gradual mild exfoliation. Avoid harsh scrubbing because it increases irritation.',
                'keywords' => 'pores routine, niacinamide pores, روتين المسامات, علاج المسام, نياسيناميد',
            ],
            [
                'question_ar' => 'هل المسامات مرتبطة بالدهون؟',
                'question_en' => 'Are pores related to oily skin?',
                'answer_ar' => 'غالبا نعم. زيادة الدهون قد تجعل المسامات أوضح، لذلك تنظيم الدهون بدون تجفيف قوي هو الأساس.',
                'answer_en' => 'Often yes. Excess oil can make pores look larger, so balanced oil control without over-drying is key.',
                'keywords' => 'oily skin pores, excess oil, البشرة الدهنية, دهون البشرة, المسامات والدهون',
            ],
            [
                'question_ar' => 'ما أسباب تساقط الشعر؟',
                'question_en' => 'What causes hair shedding?',
                'answer_ar' => 'الأسباب الشائعة: نقص الحديد، فيتامين D، اضطراب الغدة، اضطراب الهرمونات، التوتر، الحمية القاسية، أو ما بعد الولادة. تحديد السبب أهم من استخدام منتج عشوائي.',
                'answer_en' => 'Common causes include low ferritin, vitamin D deficiency, thyroid or hormonal issues, stress, strict dieting, or postpartum changes. Identifying the cause matters more than random products.',
                'keywords' => 'hair shedding causes, hair loss causes, أسباب تساقط الشعر, تساقط الشعر, نقص الحديد',
            ],
            [
                'question_ar' => 'هل السيروم يكفي للتساقط؟',
                'question_en' => 'Is serum enough for hair loss?',
                'answer_ar' => 'السيروم عامل مساعد، لكنه لا يكفي إذا كان السبب نقصا داخليا أو هرمونيا. التساقط المتكرر يحتاج تقييم وتحاليل حسب الحالة.',
                'answer_en' => 'Serum is supportive, but it is not enough when the trigger is internal deficiency or hormonal. Recurring shedding needs assessment and labs when indicated.',
                'keywords' => 'hair serum, hair loss serum, سيروم الشعر, سيروم التساقط, علاج تساقط الشعر',
            ],
            [
                'question_ar' => 'متى أقلق من تساقط الشعر؟',
                'question_en' => 'When should hair shedding be checked?',
                'answer_ar' => 'إذا استمر التساقط أكثر من عدة أسابيع، أو ظهر فراغات، أو ترافق مع تعب أو اضطراب دورة، يفضل تقييم السبب مبكرا.',
                'answer_en' => 'If shedding continues for weeks, creates visible thinning, or comes with fatigue/cycle changes, early assessment is recommended.',
                'keywords' => 'severe hair loss, hair loss check, تساقط شعر شديد, فراغات الشعر, فحص التساقط',
            ],
            [
                'question_ar' => 'كيف أعرف أن المشكلة هرمونية؟',
                'question_en' => 'How do I know if my concern is hormonal?',
                'answer_ar' => 'نشك بالسبب الهرموني عند وجود حبوب متكررة حول الذقن، اضطراب دورة، زيادة شعر، تساقط شعر، أو كلف متكرر. التشخيص يحتاج تقييم طبي وتحاليل عند اللزوم.',
                'answer_en' => 'Hormonal causes are suspected with recurring chin/jaw acne, irregular cycles, excess hair growth, hair shedding, or recurrent melasma. Diagnosis needs medical assessment and labs if needed.',
                'keywords' => 'hormonal symptoms, hormonal acne, أعراض هرمونية, حبوب هرمونية, اضطراب الدورة',
            ],
            [
                'question_ar' => 'هل الكريمات تكفي للمشكلة الهرمونية؟',
                'question_en' => 'Are creams enough for hormonal issues?',
                'answer_ar' => 'الكريمات قد تحسن المظهر، لكنها لا تعالج السبب الداخلي. إذا كان السبب هرمونيا، نحتاج معالجة السبب مع روتين مناسب.',
                'answer_en' => 'Creams can improve appearance, but they do not treat the internal trigger. Hormonal issues need cause-based treatment plus a suitable routine.',
                'keywords' => 'hormonal treatment, creams hormones, كريمات الهرمونات, علاج هرموني, مشكلة هرمونية',
            ],
            [
                'question_ar' => 'هل التكيس يؤثر على البشرة؟',
                'question_en' => 'Can PCOS affect skin?',
                'answer_ar' => 'نعم، التكيس قد يرتبط بحب شباب متكرر، زيادة دهون، تساقط شعر أو زيادة شعر غير مرغوب. الخطة تعتمد على الأعراض والتحاليل.',
                'answer_en' => 'Yes. PCOS may be linked with recurring acne, oily skin, hair shedding, or unwanted hair growth. The plan depends on symptoms and lab findings.',
                'keywords' => 'pcos skin, polycystic ovaries, تكيس المبايض, التكيس والبشرة, تكيس',
            ],
            [
                'question_ar' => 'هل البشرة الدهنية تحتاج مرطب؟',
                'question_en' => 'Does oily skin need moisturizer?',
                'answer_ar' => 'نعم، البشرة الدهنية تحتاج مرطب خفيف غير كوميدوجينيك. إهمال الترطيب قد يزيد التهيج ويضعف تحمل العلاجات.',
                'answer_en' => 'Yes. Oily skin needs a lightweight non-comedogenic moisturizer. Skipping moisturizer can increase irritation and reduce treatment tolerance.',
                'keywords' => 'oily skin moisturizer, non comedogenic, مرطب للبشرة الدهنية, ترطيب البشرة الدهنية',
            ],
            [
                'question_ar' => 'كيف أختار المرطب؟',
                'question_en' => 'How do I choose a moisturizer?',
                'answer_ar' => 'اختاري مرطبا حسب نوع البشرة: خفيف للبشرة الدهنية، أغنى للبشرة الجافة، ومهدئ للحساسة. الأهم ألا يسبب حرقان أو حبوب جديدة.',
                'answer_en' => 'Choose by skin type: lightweight for oily skin, richer for dry skin, soothing for sensitive skin. It should not cause burning or new breakouts.',
                'keywords' => 'choose moisturizer, best moisturizer, اختيار المرطب, أفضل مرطب, نوع البشرة',
            ],
            [
                'question_ar' => 'هل الجفاف يزيد التصبغات؟',
                'question_en' => 'Can dryness worsen pigmentation?',
                'answer_ar' => 'الجفاف يضعف حاجز البشرة ويزيد التهيج، والتهيج قد يزيد التصبغات عند بعض الأشخاص. لذلك الترطيب جزء مهم من خطة التفتيح.',
                'answer_en' => 'Dryness weakens the skin barrier and increases irritation, which can worsen pigmentation in some people. Moisturizing is part of brightening care.',
                'keywords' => 'dryness pigmentation, skin barrier, الجفاف والتصبغات, حاجز البشرة, جفاف الجلد',
            ],
            [
                'question_ar' => 'كم مرة أستخدم الغسول؟',
                'question_en' => 'How often should I cleanse?',
                'answer_ar' => 'غالبا مرة إلى مرتين يوميا تكفي. التنظيف المتكرر أو القاسي قد يزيد الجفاف والتهيج والحبوب.',
                'answer_en' => 'Usually once or twice daily is enough. Over-cleansing or harsh cleansing can increase dryness, irritation, and breakouts.',
                'keywords' => 'cleanser frequency, wash face, عدد مرات الغسول, استخدام الغسول, غسل الوجه',
            ],
            [
                'question_ar' => 'كيف أعرف أن الغسول غير مناسب؟',
                'question_en' => 'How do I know a cleanser is unsuitable?',
                'answer_ar' => 'إذا سبب شد قوي، حرقان، احمرار، جفاف واضح، أو زاد الحبوب، فهو غالبا غير مناسب أو يستخدم أكثر من اللازم.',
                'answer_en' => 'If it causes strong tightness, burning, redness, obvious dryness, or worsens breakouts, it may be unsuitable or overused.',
                'keywords' => 'unsuitable cleanser, cleanser irritation, غسول غير مناسب, حرقان الغسول, جفاف الغسول',
            ],
            [
                'question_ar' => 'هل الغسول يعالج الحبوب وحده؟',
                'question_en' => 'Can cleanser treat acne alone?',
                'answer_ar' => 'الغسول ينظف ويدعم الروتين، لكنه غالبا لا يكفي وحده لعلاج الحبوب المتكررة أو الالتهابية.',
                'answer_en' => 'Cleanser supports the routine, but it is usually not enough alone for recurring or inflammatory acne.',
                'keywords' => 'acne cleanser, cleanser treatment, غسول الحبوب, علاج الحبوب بالغسول, غسول الوجه',
            ],
            [
                'question_ar' => 'كم مرة أجدد واقي الشمس؟',
                'question_en' => 'How often should sunscreen be reapplied?',
                'answer_ar' => 'عند التعرض للشمس يفضل تجديده كل ساعتين إلى ثلاث ساعات، وبعد التعرق أو الغسل. داخل المنزل يعتمد على التعرض للضوء والشمس.',
                'answer_en' => 'With sun exposure, reapply every 2-3 hours and after sweating or washing. Indoors depends on light and sun exposure.',
                'keywords' => 'reapply sunscreen, sunscreen frequency, تجديد واقي الشمس, كل كم ساعة واقي الشمس',
            ],
            [
                'question_ar' => 'هل واقي الشمس مهم للتصبغات؟',
                'question_en' => 'Is sunscreen important for pigmentation?',
                'answer_ar' => 'نعم، هو أهم خطوة. بدون واقي شمس منتظم قد لا تتحسن التصبغات أو تعود بسرعة حتى مع كريمات التفتيح.',
                'answer_en' => 'Yes, it is the most important step. Without consistent sunscreen, pigmentation may not improve or may return quickly despite brightening products.',
                'keywords' => 'sunscreen pigmentation, sunscreen dark spots, واقي الشمس للتصبغات, واقي الشمس والكلف',
            ],
            [
                'question_ar' => 'كيف أختار واقي الشمس؟',
                'question_en' => 'How do I choose sunscreen?',
                'answer_ar' => 'اختاري واقي واسع الطيف SPF 30 أو أعلى، مناسب لنوع بشرتك، ولا يسبب حبوب أو حرقان. الأهم أن تستطيعي استخدامه يوميا.',
                'answer_en' => 'Choose broad-spectrum SPF 30 or higher, suitable for your skin type, and not causing breakouts or burning. The best sunscreen is one you can use daily.',
                'keywords' => 'choose sunscreen, spf 30, broad spectrum, اختيار واقي الشمس, أفضل واقي شمس',
            ],
            [
                'question_ar' => 'هل تختفي علامات التمدد تماما؟',
                'question_en' => 'Do stretch marks disappear completely?',
                'answer_ar' => 'غالبا لا تختفي بالكامل، لكن يمكن تحسين لونها وملمسها ومظهرها. العلامات الحديثة الحمراء تستجيب أفضل من البيضاء القديمة.',
                'answer_en' => 'They usually do not disappear completely, but their color, texture, and appearance can improve. New red marks respond better than older white ones.',
                'keywords' => 'remove stretch marks, red white stretch marks, اختفاء علامات التمدد, تشققات الجلد',
            ],
            [
                'question_ar' => 'ما أفضل وقت لعلاج علامات التمدد؟',
                'question_en' => 'When is the best time to treat stretch marks?',
                'answer_ar' => 'كلما بدأ العلاج مبكرا كانت الاستجابة أفضل، خاصة عندما تكون العلامات حمراء أو بنفسجية وليست بيضاء قديمة.',
                'answer_en' => 'The earlier treatment starts, the better the response, especially when marks are red or purple rather than old white marks.',
                'keywords' => 'stretch marks treatment time, new stretch marks, وقت علاج التمدد, علامات حمراء, علامات بيضاء',
            ],
            [
                'question_ar' => 'هل الترطيب يفيد علامات التمدد؟',
                'question_en' => 'Does moisturizing help stretch marks?',
                'answer_ar' => 'الترطيب يدعم مرونة الجلد ويحسن الملمس، لكنه وحده قد لا يكفي للعلامات العميقة أو القديمة.',
                'answer_en' => 'Moisturizing supports elasticity and texture, but alone may not be enough for deep or old marks.',
                'keywords' => 'moisturizer stretch marks, skin elasticity, ترطيب علامات التمدد, مرونة الجلد',
            ],
            [
                'question_ar' => 'هل كريم السيلوليت يكفي؟',
                'question_en' => 'Is cellulite cream enough?',
                'answer_ar' => 'الكريم أو الجل عامل مساعد فقط. السيلوليت يحتاج خطة تشمل حركة، مساج، ترطيب، وتحسين نمط الغذاء والنوم.',
                'answer_en' => 'Cream or gel is only supportive. Cellulite care needs movement, massage, hydration, and improvement in nutrition and sleep patterns.',
                'keywords' => 'cellulite cream, firming gel, كريم السيلوليت, جل السيلوليت, شد الجسم',
            ],
            [
                'question_ar' => 'لماذا يظهر السيلوليت؟',
                'question_en' => 'Why does cellulite appear?',
                'answer_ar' => 'يظهر بسبب بنية الجلد وتوزع الدهون والدورة الدموية والعوامل الهرمونية والوراثية. لذلك لا يعتبر مشكلة وزن فقط.',
                'answer_en' => 'It appears due to skin structure, fat distribution, circulation, hormonal, and genetic factors. It is not only a weight issue.',
                'keywords' => 'cellulite causes, cellulite weight, أسباب السيلوليت, ظهور السيلوليت, توزيع الدهون',
            ],
            [
                'question_ar' => 'كيف أحسن مظهر السيلوليت؟',
                'question_en' => 'How can cellulite appearance improve?',
                'answer_ar' => 'التحسن يكون بالتزام تدريجي: حركة منتظمة، شرب ماء، مساج، ترطيب، ومنتج موضعي مساعد. النتائج تحتاج وقتا.',
                'answer_en' => 'Improvement is gradual: regular movement, hydration, massage, moisturizing, and supportive topical products. Results take time.',
                'keywords' => 'improve cellulite, cellulite massage, تحسين السيلوليت, مساج السيلوليت, مظهر السيلوليت',
            ],
            [
                'question_ar' => 'ما المعلومات التي أرسلها؟',
                'question_en' => 'What information should I send?',
                'answer_ar' => 'أرسلي: متى بدأت المشكلة، مكانها، هل تتكرر، هل يوجد ألم أو حكة، نوع البشرة، والمنتجات المستخدمة حاليا. الصور تساعد إذا كانت واضحة وبموافقتك.',
                'answer_en' => 'Send when it started, location, recurrence, pain or itching, skin type, and current products. Clear photos help if you consent.',
                'keywords' => 'consultation information, send details, معلومات الاستشارة, وصف المشكلة, إرسال صورة',
            ],
            [
                'question_ar' => 'هل أبدأ بروتين عام؟',
                'question_en' => 'Can I start a general routine?',
                'answer_ar' => 'يمكن البدء بروتين آمن: غسول لطيف، مرطب، واقي شمس. لا تبدئي علاجات قوية قبل معرفة المشكلة حتى لا تزيد التهيج.',
                'answer_en' => 'You can start a safe routine: gentle cleanser, moisturizer, sunscreen. Avoid strong treatments before identifying the concern to prevent irritation.',
                'keywords' => 'general routine, safe skincare routine, روتين عام, روتين آمن, بداية الروتين',
            ],
            [
                'question_ar' => 'متى أحجز استشارة؟',
                'question_en' => 'When should I book a consultation?',
                'answer_ar' => 'احجزي استشارة إذا كانت المشكلة مؤلمة، تنتشر، مستمرة، تترك آثارا، أو مرتبطة بتساقط شعر أو اضطراب دورة أو أعراض عامة.',
                'answer_en' => 'Book a consultation if the issue is painful, spreading, persistent, scarring, or linked with hair loss, cycle changes, or systemic symptoms.',
                'keywords' => 'book consultation, medical consultation, حجز استشارة, متى أراجع الطبيبة, استشارة جلدية',
            ],
        ];
    }
}
