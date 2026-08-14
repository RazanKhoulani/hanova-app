<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Str;

class FaqBotService
{
    private const TOPICS = [
        'acne' => [
            'aliases' => ['acne', 'pimples', 'breakouts', 'حب الشباب', 'الحبوب', 'حبوب'],
            'summary' => [
                'ar' => 'حب الشباب المتكرر غالبا يحتاج تقييم السبب الداخلي مثل الهرمونات، مقاومة الإنسولين، التوتر أو نمط الغذاء. الروتين الأساسي: غسول لطيف، مرطب، واقي شمس، ثم علاج موجه حسب شدة الحالة.',
                'en' => 'Recurring acne often needs assessment of internal triggers such as hormones, insulin resistance, stress, or diet. Basic care starts with cleanser, moisturizer, sunscreen, then targeted treatment by severity.',
            ],
            'questions' => [
                [
                    'ar' => 'لماذا تتكرر الحبوب عندي؟',
                    'en' => 'Why does my acne keep coming back?',
                    'answer_ar' => 'تكرار الحبوب يعني أن السبب قد لا يكون سطحيا فقط. الأسباب الشائعة: اضطراب الهرمونات، مقاومة الإنسولين، التوتر، نمط الغذاء، أو استخدام منتجات غير مناسبة. لذلك نحتاج معرفة النمط والمكان والتكرار قبل اختيار العلاج.',
                    'answer_en' => 'Recurring acne may not be only superficial. Common triggers include hormones, insulin resistance, stress, diet, or unsuitable products. The pattern, location, and frequency guide the treatment plan.',
                ],
                [
                    'ar' => 'ما الروتين الأساسي لحب الشباب؟',
                    'en' => 'What is the basic acne routine?',
                    'answer_ar' => 'الروتين الأساسي يكون: غسول لطيف، مرطب خفيف، واقي شمس، ثم علاج موجه مثل أزيليك أسيد أو نياسيناميد أو علاج دوائي حسب شدة الحالة. لا يفضل الجمع بين عدة مقشرات من البداية.',
                    'answer_en' => 'A basic acne routine includes a gentle cleanser, lightweight moisturizer, sunscreen, then targeted care such as azelaic acid, niacinamide, or medication depending on severity. Avoid starting many exfoliants together.',
                ],
                [
                    'ar' => 'متى أحتاج علاج دوائي للحبوب؟',
                    'en' => 'When does acne need medication?',
                    'answer_ar' => 'إذا كانت الحبوب عميقة، مؤلمة، تترك آثارا، أو تتكرر رغم الروتين، فقد تحتاج علاج دوائي ومتابعة طبية. لا ننصح بالأدوية الفموية بدون تقييم وتحاليل عند الحاجة.',
                    'answer_en' => 'If acne is deep, painful, scarring, or persistent despite routine care, it may need medication and medical follow-up. Oral medication should not be used without assessment.',
                ],
            ],
        ],
        'pigmentation' => [
            'aliases' => ['pigmentation', 'melasma', 'dark spots', 'تصبغات', 'التصبغات', 'كلف', 'بقع'],
            'summary' => [
                'ar' => 'التصبغات تحتاج واقي شمس يومي كخطوة أساسية، ثم كريم تفتيح أو فيتامين C أو تقشير خفيف حسب النوع والعمق. إذا كان الكلف هرمونيا أو متكررا، يجب تقييم السبب الداخلي.',
                'en' => 'Pigmentation needs daily sunscreen first, then brightening cream, vitamin C, or mild exfoliation depending on type and depth. Hormonal or recurring melasma needs internal trigger assessment.',
            ],
            'questions' => [
                [
                    'ar' => 'هل التصبغات بسبب الشمس؟',
                    'en' => 'Is pigmentation caused by the sun?',
                    'answer_ar' => 'الشمس من أهم أسباب زيادة التصبغات وتثبيتها، حتى لو كان السبب الأساسي هرمونيا أو التهابيا. لذلك واقي الشمس اليومي هو أول خطوة، وبدونه قد لا تظهر نتيجة واضحة من كريمات التفتيح.',
                    'answer_en' => 'Sun exposure is one of the strongest reasons pigmentation worsens or persists, even when the original trigger is hormonal or inflammatory. Daily sunscreen is the first step.',
                ],
                [
                    'ar' => 'ما الفرق بين الكلف وآثار الحبوب؟',
                    'en' => 'What is the difference between melasma and acne marks?',
                    'answer_ar' => 'آثار الحبوب غالبا تظهر بعد التهاب أو حبة وتكون محددة بمكانها. الكلف غالبا أوسع ومتكرر وقد يرتبط بالهرمونات والشمس. الخطة تختلف، لذلك مهم تحديد النوع قبل العلاج.',
                    'answer_en' => 'Acne marks usually appear where inflammation happened. Melasma is often broader, recurrent, and linked to hormones and sun exposure. Treatment differs, so identifying the type matters.',
                ],
                [
                    'ar' => 'كم يحتاج علاج التصبغات؟',
                    'en' => 'How long does pigmentation treatment take?',
                    'answer_ar' => 'علاج التصبغات يحتاج وقتا والتزاما. التحسن غالبا يظهر تدريجيا خلال أسابيع إلى أشهر حسب العمق والسبب والالتزام بواقي الشمس. التصبغات العميقة أو الهرمونية تحتاج متابعة أطول.',
                    'answer_en' => 'Pigmentation improves gradually over weeks to months depending on depth, cause, and sunscreen consistency. Deep or hormonal pigmentation usually needs longer follow-up.',
                ],
            ],
        ],
        'body-pigmentation' => [
            'aliases' => ['body pigmentation', 'body darkening', 'تصبغات الجسم', 'سواد الجسم'],
            'summary' => [
                'ar' => 'تصبغات الجسم غالبا تنتج عن احتكاك، جفاف أو التهاب سابق. الأفضل تقليل الاحتكاك، ترطيب يومي، واستخدام مقشر أو كريم تفتيح مناسب للجسم تدريجيا.',
                'en' => 'Body pigmentation is often caused by friction, dryness, or previous inflammation. Reduce friction, moisturize daily, and add suitable body brightening or exfoliating care gradually.',
            ],
            'questions' => [
                [
                    'ar' => 'ما أسباب سواد مناطق الجسم؟',
                    'en' => 'What causes body darkening?',
                    'answer_ar' => 'الأسباب الشائعة: الاحتكاك، الجفاف، إزالة الشعر، التهاب سابق، زيادة الوزن، أو مقاومة الإنسولين في بعض المناطق. العلاج يبدأ بتقليل السبب وليس التفتيح فقط.',
                    'answer_en' => 'Common causes include friction, dryness, hair removal, previous inflammation, weight changes, or insulin resistance in some areas. Treatment starts by reducing the trigger.',
                ],
                [
                    'ar' => 'هل التقشير مناسب للجسم؟',
                    'en' => 'Is exfoliation suitable for body pigmentation?',
                    'answer_ar' => 'التقشير قد يساعد، لكن يجب أن يكون تدريجيا ومناسبا للجسم. الإفراط بالتقشير أو الفرك يزيد الالتهاب والتصبغ. الترطيب وتقليل الاحتكاك مهمان مع أي مقشر.',
                    'answer_en' => 'Exfoliation can help, but it should be gradual and body-safe. Over-exfoliation or scrubbing can worsen inflammation and pigmentation. Moisturizing and friction control remain essential.',
                ],
                [
                    'ar' => 'متى أحتاج فحص للتصبغات؟',
                    'en' => 'When should body pigmentation be checked?',
                    'answer_ar' => 'إذا كان السواد مخمليا أو سريع الانتشار أو في الرقبة وتحت الإبط مع زيادة وزن أو اضطراب دورة، يفضل تقييم مقاومة الإنسولين والهرمونات.',
                    'answer_en' => 'If darkening is velvety, spreading quickly, or appears on the neck/underarms with weight gain or cycle changes, insulin resistance and hormones should be assessed.',
                ],
            ],
        ],
        'dark-circles' => [
            'aliases' => ['dark circles', 'under eye', 'هالات', 'الهالات السوداء', 'تحت العين'],
            'summary' => [
                'ar' => 'الهالات السوداء لها أسباب مختلفة: وراثة، إرهاق، حساسية، نقص حديد أو B12، أو تجويف تحت العين. السيروم يساعد في الحالات الخفيفة، لكن الهالات المستمرة تحتاج تحديد السبب.',
                'en' => 'Dark circles have different causes: genetics, fatigue, allergy, iron or B12 deficiency, or under-eye hollowing. Serums help mild cases, but persistent circles need cause-based evaluation.',
            ],
            'questions' => [
                [
                    'ar' => 'هل الهالات من نقص الفيتامينات؟',
                    'en' => 'Can deficiencies cause dark circles?',
                    'answer_ar' => 'نقص الحديد أو B12 أو الإرهاق قد يزيد مظهر الهالات عند بعض الأشخاص، لكنه ليس السبب الوحيد. يجب النظر للوراثة والحساسية والتجويف تحت العين أيضا.',
                    'answer_en' => 'Iron or B12 deficiency and fatigue can worsen dark circles for some people, but they are not the only causes. Genetics, allergy, and hollowing also matter.',
                ],
                [
                    'ar' => 'هل سيروم الكافيين يكفي للهالات؟',
                    'en' => 'Is caffeine serum enough for dark circles?',
                    'answer_ar' => 'سيروم الكافيين قد يساعد الانتفاخ والمظهر الخفيف المرتبط بالإرهاق، لكنه لا يكفي إذا كانت الهالات بسبب تصبغ عميق أو نقص أو تجويف.',
                    'answer_en' => 'Caffeine serum may help puffiness and mild fatigue-related darkness, but it is not enough for deep pigmentation, deficiencies, or hollowing.',
                ],
                [
                    'ar' => 'متى تحتاج الهالات استشارة؟',
                    'en' => 'When do dark circles need consultation?',
                    'answer_ar' => 'إذا كانت الهالات شديدة، مفاجئة، مع تعب عام، أو لا تتحسن بالنوم والترطيب، يفضل تقييم السبب واختيار الخطة المناسبة.',
                    'answer_en' => 'If dark circles are severe, sudden, linked with fatigue, or do not improve with sleep and hydration, a cause-based assessment is recommended.',
                ],
            ],
        ],
        'pores' => [
            'aliases' => ['large pores', 'enlarged pores', 'pores', 'مسامات', 'توسع المسامات', 'مسام واسعة'],
            'summary' => [
                'ar' => 'توسع المسامات يتحسن بتنظيم الدهون وتنظيف لطيف وتجنب الفرك. النياسيناميد والتقشير الخفيف يساعدان على تحسين المظهر، لكن المسامات لا تغلق نهائيا.',
                'en' => 'Enlarged pores improve with oil control, gentle cleansing, and avoiding harsh scrubbing. Niacinamide and mild exfoliation can improve appearance, but pores do not close permanently.',
            ],
            'questions' => [
                [
                    'ar' => 'هل يمكن إغلاق المسامات؟',
                    'en' => 'Can pores be closed?',
                    'answer_ar' => 'لا يمكن إغلاق المسامات نهائيا لأنها جزء طبيعي من الجلد. الهدف هو تقليل مظهرها بتنظيم الدهون وتحسين الملمس.',
                    'answer_en' => 'Pores cannot be permanently closed because they are a normal part of skin. The goal is to reduce their appearance by controlling oil and improving texture.',
                ],
                [
                    'ar' => 'ما أفضل روتين للمسامات؟',
                    'en' => 'What routine helps enlarged pores?',
                    'answer_ar' => 'غسول لطيف، مرطب خفيف، واقي شمس، ثم نياسيناميد أو تقشير خفيف تدريجي. تجنبي الفرك القاسي لأنه يزيد التهيج.',
                    'answer_en' => 'Use a gentle cleanser, lightweight moisturizer, sunscreen, then niacinamide or gradual mild exfoliation. Avoid harsh scrubbing because it increases irritation.',
                ],
                [
                    'ar' => 'هل المسامات مرتبطة بالدهون؟',
                    'en' => 'Are pores related to oily skin?',
                    'answer_ar' => 'غالبا نعم. زيادة الدهون قد تجعل المسامات أوضح، لذلك تنظيم الدهون بدون تجفيف قوي هو الأساس.',
                    'answer_en' => 'Often yes. Excess oil can make pores look larger, so balanced oil control without over-drying is key.',
                ],
            ],
        ],
        'hair-problems' => [
            'aliases' => ['hair issues', 'hair problems', 'hair loss', 'hair shedding', 'مشاكل الشعر', 'تساقط الشعر', 'الشعر'],
            'summary' => [
                'ar' => 'مشاكل الشعر أو التساقط تحتاج معرفة السبب: نقص حديد أو فيتامين D، اضطراب الغدة أو الهرمونات، توتر أو ما بعد الولادة. السيرومات داعمة، لكن التساقط المتكرر يحتاج تقييم.',
                'en' => 'Hair issues or shedding need cause assessment: ferritin or vitamin D deficiency, thyroid or hormonal issues, stress, or postpartum changes. Serums are supportive, but recurring shedding needs evaluation.',
            ],
            'questions' => [
                [
                    'ar' => 'ما أسباب تساقط الشعر؟',
                    'en' => 'What causes hair shedding?',
                    'answer_ar' => 'الأسباب الشائعة: نقص الحديد، فيتامين D، اضطراب الغدة، اضطراب الهرمونات، التوتر، الحمية القاسية، أو ما بعد الولادة. تحديد السبب أهم من استخدام منتج عشوائي.',
                    'answer_en' => 'Common causes include low ferritin, vitamin D deficiency, thyroid or hormonal issues, stress, strict dieting, or postpartum changes. Identifying the cause matters more than random products.',
                ],
                [
                    'ar' => 'هل السيروم يكفي للتساقط؟',
                    'en' => 'Is serum enough for hair loss?',
                    'answer_ar' => 'السيروم عامل مساعد، لكنه لا يكفي إذا كان السبب نقصا داخليا أو هرمونيا. التساقط المتكرر يحتاج تقييم وتحاليل حسب الحالة.',
                    'answer_en' => 'Serum is supportive, but it is not enough when the trigger is internal deficiency or hormonal. Recurring shedding needs assessment and labs when indicated.',
                ],
                [
                    'ar' => 'متى أقلق من تساقط الشعر؟',
                    'en' => 'When should hair shedding be checked?',
                    'answer_ar' => 'إذا استمر التساقط أكثر من عدة أسابيع، أو ظهر فراغات، أو ترافق مع تعب أو اضطراب دورة، يفضل تقييم السبب مبكرا.',
                    'answer_en' => 'If shedding continues for weeks, creates visible thinning, or comes with fatigue/cycle changes, early assessment is recommended.',
                ],
            ],
        ],
        'hormonal-imbalance' => [
            'aliases' => ['hormonal imbalance', 'hormones', 'pcos', 'اضطراب الهرمونات', 'هرمونات', 'تكيس'],
            'summary' => [
                'ar' => 'اضطراب الهرمونات قد يظهر كحب شباب متكرر، تساقط شعر أو كلف. عند تكرار المشكلة أو وجود أعراض مرافقة، لا يكفي العلاج الموضعي ويجب تقييم السبب طبيا.',
                'en' => 'Hormonal imbalance can show as recurring acne, hair shedding, or melasma. When issues repeat or come with other symptoms, topical care is not enough and medical assessment is needed.',
            ],
            'questions' => [
                [
                    'ar' => 'كيف أعرف أن المشكلة هرمونية؟',
                    'en' => 'How do I know if my concern is hormonal?',
                    'answer_ar' => 'نشك بالسبب الهرموني عند وجود حبوب متكررة حول الذقن، اضطراب دورة، زيادة شعر، تساقط شعر، أو كلف متكرر. التشخيص يحتاج تقييم طبي وتحاليل عند اللزوم.',
                    'answer_en' => 'Hormonal causes are suspected with recurring chin/jaw acne, irregular cycles, excess hair growth, hair shedding, or recurrent melasma. Diagnosis needs medical assessment and labs if needed.',
                ],
                [
                    'ar' => 'هل الكريمات تكفي للمشكلة الهرمونية؟',
                    'en' => 'Are creams enough for hormonal issues?',
                    'answer_ar' => 'الكريمات قد تحسن المظهر، لكنها لا تعالج السبب الداخلي. إذا كان السبب هرمونيا، نحتاج معالجة السبب مع روتين مناسب.',
                    'answer_en' => 'Creams can improve appearance, but they do not treat the internal trigger. Hormonal issues need cause-based treatment plus a suitable routine.',
                ],
                [
                    'ar' => 'هل التكيس يؤثر على البشرة؟',
                    'en' => 'Can PCOS affect skin?',
                    'answer_ar' => 'نعم، التكيس قد يرتبط بحب شباب متكرر، زيادة دهون، تساقط شعر أو زيادة شعر غير مرغوب. الخطة تعتمد على الأعراض والتحاليل.',
                    'answer_en' => 'Yes. PCOS may be linked with recurring acne, oily skin, hair shedding, or unwanted hair growth. The plan depends on symptoms and lab findings.',
                ],
            ],
        ],
        'hydration' => [
            'aliases' => ['hydration', 'moisturizer', 'dry skin', 'ترطيب', 'مرطب', 'جفاف'],
            'summary' => [
                'ar' => 'الترطيب خطوة أساسية لحماية حاجز البشرة وتقليل التهيج. حتى البشرة الدهنية تحتاج مرطب مناسب وخفيف حتى تتحمل علاجات الحبوب والتصبغات.',
                'en' => 'Hydration protects the skin barrier and reduces irritation. Even oily skin needs a suitable lightweight moisturizer to tolerate acne and pigmentation treatments.',
            ],
            'questions' => [
                [
                    'ar' => 'هل البشرة الدهنية تحتاج مرطب؟',
                    'en' => 'Does oily skin need moisturizer?',
                    'answer_ar' => 'نعم، البشرة الدهنية تحتاج مرطب خفيف غير كوميدوجينيك. إهمال الترطيب قد يزيد التهيج ويضعف تحمل العلاجات.',
                    'answer_en' => 'Yes. Oily skin needs a lightweight non-comedogenic moisturizer. Skipping moisturizer can increase irritation and reduce treatment tolerance.',
                ],
                [
                    'ar' => 'كيف أختار المرطب؟',
                    'en' => 'How do I choose a moisturizer?',
                    'answer_ar' => 'اختاري مرطبا حسب نوع البشرة: خفيف للبشرة الدهنية، أغنى للبشرة الجافة، ومهدئ للحساسة. الأهم ألا يسبب حرقان أو حبوب جديدة.',
                    'answer_en' => 'Choose by skin type: lightweight for oily skin, richer for dry skin, soothing for sensitive skin. It should not cause burning or new breakouts.',
                ],
                [
                    'ar' => 'هل الجفاف يزيد التصبغات؟',
                    'en' => 'Can dryness worsen pigmentation?',
                    'answer_ar' => 'الجفاف يضعف حاجز البشرة ويزيد التهيج، والتهيج قد يزيد التصبغات عند بعض الأشخاص. لذلك الترطيب جزء مهم من خطة التفتيح.',
                    'answer_en' => 'Dryness weakens the skin barrier and increases irritation, which can worsen pigmentation in some people. Moisturizing is part of brightening care.',
                ],
            ],
        ],
        'cleansing' => [
            'aliases' => ['cleansing', 'cleanser', 'wash', 'غسول', 'تنظيف البشرة'],
            'summary' => [
                'ar' => 'الغسول المناسب ينظف بدون شد أو حرقان. للبشرة الحساسة أو المعرضة للحبوب نبدأ بغسول لطيف ونبتعد عن الفرك والتنظيف الزائد.',
                'en' => 'A suitable cleanser cleans without tightness or burning. For sensitive or acne-prone skin, start gentle and avoid scrubbing or over-cleansing.',
            ],
            'questions' => [
                [
                    'ar' => 'كم مرة أستخدم الغسول؟',
                    'en' => 'How often should I cleanse?',
                    'answer_ar' => 'غالبا مرة إلى مرتين يوميا تكفي. التنظيف المتكرر أو القاسي قد يزيد الجفاف والتهيج والحبوب.',
                    'answer_en' => 'Usually once or twice daily is enough. Over-cleansing or harsh cleansing can increase dryness, irritation, and breakouts.',
                ],
                [
                    'ar' => 'كيف أعرف أن الغسول غير مناسب؟',
                    'en' => 'How do I know a cleanser is unsuitable?',
                    'answer_ar' => 'إذا سبب شد قوي، حرقان، احمرار، جفاف واضح، أو زاد الحبوب، فهو غالبا غير مناسب أو يستخدم أكثر من اللازم.',
                    'answer_en' => 'If it causes strong tightness, burning, redness, obvious dryness, or worsens breakouts, it may be unsuitable or overused.',
                ],
                [
                    'ar' => 'هل الغسول يعالج الحبوب وحده؟',
                    'en' => 'Can cleanser treat acne alone?',
                    'answer_ar' => 'الغسول ينظف ويدعم الروتين، لكنه غالبا لا يكفي وحده لعلاج الحبوب المتكررة أو الالتهابية.',
                    'answer_en' => 'Cleanser supports the routine, but it is usually not enough alone for recurring or inflammatory acne.',
                ],
            ],
        ],
        'sun-protection' => [
            'aliases' => ['sun protection', 'sunscreen', 'spf', 'واقي شمسي', 'واقي الشمس'],
            'summary' => [
                'ar' => 'واقي الشمس ضروري لعلاج التصبغات وآثار الحبوب وحماية الجلد بعد التقشير. بدون استخدام منتظم ممكن ترجع التصبغات حتى لو الروتين جيد.',
                'en' => 'Sunscreen is essential for pigmentation, acne marks, and protecting skin after exfoliation. Without consistent use, pigmentation can return even with a good routine.',
            ],
            'questions' => [
                [
                    'ar' => 'كم مرة أجدد واقي الشمس؟',
                    'en' => 'How often should sunscreen be reapplied?',
                    'answer_ar' => 'عند التعرض للشمس يفضل تجديده كل ساعتين إلى ثلاث ساعات، وبعد التعرق أو الغسل. داخل المنزل يعتمد على التعرض للضوء والشمس.',
                    'answer_en' => 'With sun exposure, reapply every 2-3 hours and after sweating or washing. Indoors depends on light and sun exposure.',
                ],
                [
                    'ar' => 'هل واقي الشمس مهم للتصبغات؟',
                    'en' => 'Is sunscreen important for pigmentation?',
                    'answer_ar' => 'نعم، هو أهم خطوة. بدون واقي شمس منتظم قد لا تتحسن التصبغات أو تعود بسرعة حتى مع كريمات التفتيح.',
                    'answer_en' => 'Yes, it is the most important step. Without consistent sunscreen, pigmentation may not improve or may return quickly despite brightening products.',
                ],
                [
                    'ar' => 'كيف أختار واقي الشمس؟',
                    'en' => 'How do I choose sunscreen?',
                    'answer_ar' => 'اختاري واقي واسع الطيف SPF 30 أو أعلى، مناسب لنوع بشرتك، ولا يسبب حبوب أو حرقان. الأهم أن تستطيعي استخدامه يوميا.',
                    'answer_en' => 'Choose broad-spectrum SPF 30 or higher, suitable for your skin type, and not causing breakouts or burning. The best sunscreen is one you can use daily.',
                ],
            ],
        ],
        'stretch-marks' => [
            'aliases' => ['stretch marks', 'striae', 'علامات التمدد', 'تمدد الجلد'],
            'summary' => [
                'ar' => 'علامات التمدد الحديثة تستجيب أفضل من القديمة. الخطة تشمل ترطيب قوي ودعم مرونة الجلد، وقد تحتاج جلسات عيادية لتحسين الشكل. النتائج تدريجية.',
                'en' => 'Newer stretch marks respond better than older ones. Care includes strong moisturization and elasticity support, and clinic sessions may improve appearance. Results are gradual.',
            ],
            'questions' => [
                [
                    'ar' => 'هل تختفي علامات التمدد تماما؟',
                    'en' => 'Do stretch marks disappear completely?',
                    'answer_ar' => 'غالبا لا تختفي بالكامل، لكن يمكن تحسين لونها وملمسها ومظهرها. العلامات الحديثة الحمراء تستجيب أفضل من البيضاء القديمة.',
                    'answer_en' => 'They usually do not disappear completely, but their color, texture, and appearance can improve. New red marks respond better than older white ones.',
                ],
                [
                    'ar' => 'ما أفضل وقت لعلاج علامات التمدد؟',
                    'en' => 'When is the best time to treat stretch marks?',
                    'answer_ar' => 'كلما بدأ العلاج مبكرا كانت الاستجابة أفضل، خاصة عندما تكون العلامات حمراء أو بنفسجية وليست بيضاء قديمة.',
                    'answer_en' => 'The earlier treatment starts, the better the response, especially when marks are red or purple rather than old white marks.',
                ],
                [
                    'ar' => 'هل الترطيب يفيد علامات التمدد؟',
                    'en' => 'Does moisturizing help stretch marks?',
                    'answer_ar' => 'الترطيب يدعم مرونة الجلد ويحسن الملمس، لكنه وحده قد لا يكفي للعلامات العميقة أو القديمة.',
                    'answer_en' => 'Moisturizing supports elasticity and texture, but alone may not be enough for deep or old marks.',
                ],
            ],
        ],
        'cellulite' => [
            'aliases' => ['cellulite', 'firming', 'سيلوليت', 'السيلوليت'],
            'summary' => [
                'ar' => 'السيلوليت لا يختفي بكريم فقط لأنه مرتبط ببنية الجلد والدورة الدموية والدهون. الأفضل دمج الحركة، المساج، الترطيب، وخطة غذائية، والجل الموضعي يكون عامل مساعد.',
                'en' => 'Cellulite does not disappear with cream alone because it relates to skin structure, circulation, and fat distribution. Combine movement, massage, hydration, nutrition planning, and topical firming support.',
            ],
            'questions' => [
                [
                    'ar' => 'هل كريم السيلوليت يكفي؟',
                    'en' => 'Is cellulite cream enough?',
                    'answer_ar' => 'الكريم أو الجل عامل مساعد فقط. السيلوليت يحتاج خطة تشمل حركة، مساج، ترطيب، وتحسين نمط الغذاء والنوم.',
                    'answer_en' => 'Cream or gel is only supportive. Cellulite care needs movement, massage, hydration, and improvement in nutrition and sleep patterns.',
                ],
                [
                    'ar' => 'لماذا يظهر السيلوليت؟',
                    'en' => 'Why does cellulite appear?',
                    'answer_ar' => 'يظهر بسبب بنية الجلد وتوزع الدهون والدورة الدموية والعوامل الهرمونية والوراثية. لذلك لا يعتبر مشكلة وزن فقط.',
                    'answer_en' => 'It appears due to skin structure, fat distribution, circulation, hormonal, and genetic factors. It is not only a weight issue.',
                ],
                [
                    'ar' => 'كيف أحسن مظهر السيلوليت؟',
                    'en' => 'How can cellulite appearance improve?',
                    'answer_ar' => 'التحسن يكون بالتزام تدريجي: حركة منتظمة، شرب ماء، مساج، ترطيب، ومنتج موضعي مساعد. النتائج تحتاج وقتا.',
                    'answer_en' => 'Improvement is gradual: regular movement, hydration, massage, moisturizing, and supportive topical products. Results take time.',
                ],
            ],
        ],
        'not-sure' => [
            'aliases' => ['not sure', 'unsure', 'unknown', 'غير متأكدة', 'مو متأكدة', 'لا أعرف'],
            'summary' => [
                'ar' => 'إذا لم تكوني متأكدة، اكتبي متى بدأت المشكلة، مكانها، هل يوجد ألم أو حكة، وهل تتكرر. إذا كانت مستمرة أو متفاقمة، الاستشارة أفضل لتحديد الخطة بدقة.',
                'en' => 'If you are not sure, describe when it started, where it appears, whether there is pain or itching, and whether it recurs. Persistent or worsening cases need consultation.',
            ],
            'questions' => [
                [
                    'ar' => 'ما المعلومات التي أرسلها؟',
                    'en' => 'What information should I send?',
                    'answer_ar' => 'أرسلي: متى بدأت المشكلة، مكانها، هل تتكرر، هل يوجد ألم أو حكة، نوع البشرة، والمنتجات المستخدمة حاليا. الصور تساعد إذا كانت واضحة وبموافقتك.',
                    'answer_en' => 'Send when it started, location, recurrence, pain/itching, skin type, and current products. Clear photos help if you consent.',
                ],
                [
                    'ar' => 'هل أبدأ بروتين عام؟',
                    'en' => 'Can I start a general routine?',
                    'answer_ar' => 'يمكن البدء بروتين آمن: غسول لطيف، مرطب، واقي شمس. لا تبدئي علاجات قوية قبل معرفة المشكلة حتى لا تزيد التهيج.',
                    'answer_en' => 'You can start a safe routine: gentle cleanser, moisturizer, sunscreen. Avoid strong treatments before identifying the concern to prevent irritation.',
                ],
                [
                    'ar' => 'متى أحجز استشارة؟',
                    'en' => 'When should I book a consultation?',
                    'answer_ar' => 'احجزي استشارة إذا كانت المشكلة مؤلمة، تنتشر، مستمرة، تترك آثارا، أو مرتبطة بتساقط شعر أو اضطراب دورة أو أعراض عامة.',
                    'answer_en' => 'Book a consultation if the issue is painful, spreading, persistent, scarring, or linked with hair loss, cycle changes, or systemic symptoms.',
                ],
            ],
        ],
    ];

    public function findAnswer(string $query, string $lang = 'ar', array $context = []): array
    {
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';
        $query = trim($query);
        $askedQuestions = collect($context['asked_questions'] ?? [])
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->values()
            ->all();

        if ($query === '' || $this->isStartQuery($query)) {
            return $this->defaultResponse($lang);
        }

        $topicQuestion = $this->detectTopicQuestion($query);
        if ($topicQuestion !== null) {
            [$topic, $question] = $topicQuestion;

            return [
                'answer' => $lang === 'en' ? $question['answer_en'] : $question['answer_ar'],
                'options' => $this->topicOptions(
                    $topic,
                    $lang,
                    array_merge($askedQuestions, [$question['ar'], $question['en']])
                ),
            ];
        }

        $topic = $this->detectTopic($query);
        if ($topic !== null) {
            return $this->topicResponse($topic, $lang, $askedQuestions);
        }

        // Product context should enrich general product questions, not block the
        // concern-specific flow after the user chooses a topic or follow-up.
        if (! empty($context['product_name'])) {
            return $this->productResponse($query, $lang, $context);
        }

        $faq = $this->findMatchingFaq($query);
        if ($faq) {
            return [
                'answer' => $lang === 'en' ? $faq->answer_en : $faq->answer_ar,
                'options' => $this->defaultOptions($lang),
            ];
        }

        return $this->fallbackResponse($lang);
    }

    private function detectTopicQuestion(string $query): ?array
    {
        $normalizedQuery = $this->normalize($query);

        foreach (self::TOPICS as $topic => $config) {
            foreach ($config['questions'] as $question) {
                $questionAr = $this->normalize($question['ar']);
                $questionEn = $this->normalize($question['en']);

                if ($normalizedQuery === $questionAr || $normalizedQuery === $questionEn) {
                    return [$topic, $question];
                }
            }
        }

        return null;
    }

    private function detectTopic(string $query): ?string
    {
        $normalizedQuery = $this->normalize($query);

        foreach (self::TOPICS as $topic => $config) {
            foreach ($config['aliases'] as $alias) {
                if ($normalizedQuery === $this->normalize($alias)) {
                    return $topic;
                }
            }
        }

        foreach (self::TOPICS as $topic => $config) {
            foreach ($config['aliases'] as $alias) {
                $normalizedAlias = $this->normalize($alias);

                if ($normalizedAlias !== '' && Str::contains($normalizedQuery, $normalizedAlias)) {
                    return $topic;
                }
            }
        }

        return null;
    }

    private function findMatchingFaq(string $query): ?Faq
    {
        $normalizedQuery = $this->normalize($query);

        return Faq::where('is_active', true)
            ->get()
            ->first(function (Faq $faq) use ($normalizedQuery) {
                $questionAr = $this->normalize((string) $faq->question_ar);
                $questionEn = $this->normalize((string) $faq->question_en);
                $keywords = collect(explode(',', (string) $faq->keywords))
                    ->map(fn (string $keyword) => $this->normalize($keyword))
                    ->filter()
                    ->values();

                if (
                    $questionAr === $normalizedQuery
                    || $questionEn === $normalizedQuery
                    || Str::contains($questionAr, $normalizedQuery)
                    || Str::contains($questionEn, $normalizedQuery)
                ) {
                    return true;
                }

                return $keywords->contains(function (string $keyword) use ($normalizedQuery) {
                    return $keyword === $normalizedQuery
                        || Str::contains($normalizedQuery, $keyword)
                        || (strlen($normalizedQuery) >= 4 && Str::contains($keyword, $normalizedQuery));
                });
            });
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function isStartQuery(string $query): bool
    {
        return in_array($this->normalize($query), [
            'start',
            'home',
            'main',
            'all topics',
            'other topics',
            'البداية',
            'الرئيسية',
            'ارجع للبداية',
            'مواضيع أخرى',
            'كل المواضيع',
        ], true);
    }

    private function productResponse(string $query, string $lang, array $context): array
    {
        $productName = trim((string) $context['product_name']);
        $description = trim((string) ($context['product_description'] ?? ''));
        $normalizedQuery = $this->normalize($query);

        $askingUsage = Str::contains($normalizedQuery, [
            'كيف',
            'طريقة',
            'استخدم',
            'استعمل',
            'usage',
            'use',
            'routine',
        ]);

        if ($lang === 'en') {
            $answer = $askingUsage
                ? "For {$productName}, use it only as part of a routine recommended for your skin condition. "
                : "{$productName} may be suitable depending on your concern, but it should not replace a consultation when symptoms are persistent. ";

            if ($description !== '') {
                $answer .= "Product note: {$description}. ";
            }

            $answer .= 'Choose your main concern below so I can ask more specific follow-up questions.';
        } else {
            $answer = $askingUsage
                ? "بالنسبة لـ {$productName}، الأفضل استخدامه ضمن روتين مناسب لحالة بشرتك وليس بشكل عشوائي. "
                : "{$productName} ممكن يكون مناسب حسب المشكلة الجلدية، لكنه لا يكفي وحده إذا كانت المشكلة متكررة أو مرتبطة بسبب داخلي. ";

            if ($description !== '') {
                $answer .= "ملاحظة المنتج: {$description}. ";
            }

            $answer .= 'اختاري المشكلة الأساسية من الأسفل حتى أرجع لك أسئلة أدق.';
        }

        return [
            'answer' => $answer,
            'options' => $this->defaultOptions($lang),
        ];
    }

    private function topicResponse(string $topic, string $lang, array $askedQuestions = []): array
    {
        return [
            'answer' => self::TOPICS[$topic]['summary'][$lang],
            'options' => $this->topicOptions($topic, $lang, $askedQuestions),
        ];
    }

    private function topicOptions(string $topic, string $lang, array $excludedQuestions = []): array
    {
        $excluded = collect($excludedQuestions)
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->map(fn (string $question) => $this->normalize($question))
            ->filter()
            ->unique()
            ->values();

        $questions = collect(self::TOPICS[$topic]['questions'])
            ->reject(function (array $question) use ($excluded) {
                return $excluded->contains($this->normalize($question['ar']))
                    || $excluded->contains($this->normalize($question['en']));
            })
            ->map(fn (array $question) => $lang === 'en' ? $question['en'] : $question['ar'])
            ->all();

        if ($lang === 'en') {
            return array_merge($questions, ['Other topics', 'Book Consultation']);
        }

        return array_merge($questions, ['مواضيع أخرى', 'احجزي استشارة']);
    }

    private function defaultResponse(string $lang): array
    {
        return [
            'answer' => $lang === 'en'
                ? 'Welcome to Hanova beauty assistant. Choose a concern so I can ask topic-specific follow-up questions.'
                : 'أهلاً بك في مساعد Hanova للجمال. اختاري المشكلة حتى أرجع لك أسئلة خاصة فيها.',
            'options' => $this->defaultOptions($lang),
        ];
    }

    private function fallbackResponse(string $lang): array
    {
        return [
            'answer' => $lang === 'en'
                ? 'I could not find an exact answer. Choose one of the topics below or book a consultation for a personalized plan.'
                : 'لم أجد إجابة دقيقة. اختاري أحد المواضيع التالية أو احجزي استشارة للحصول على خطة مناسبة لحالتك.',
            'options' => $this->defaultOptions($lang),
        ];
    }

    private function defaultOptions(string $lang): array
    {
        if ($lang === 'en') {
            return [
                'Acne',
                'Pigmentation',
                'Body pigmentation',
                'Dark circles',
                'Large pores',
                'Hair issues',
                'Hormonal imbalance',
                'Hydration',
                'Cleansing',
                'Sun protection',
                'Stretch marks',
                'Cellulite',
                'Not sure',
                'Book Consultation',
            ];
        }

        return [
            'حب الشباب',
            'التصبغات',
            'تصبغات الجسم',
            'الهالات السوداء',
            'توسع المسامات',
            'مشاكل الشعر',
            'اضطراب الهرمونات',
            'ترطيب',
            'تنظيف البشرة',
            'واقي الشمس',
            'علامات التمدد',
            'السيلوليت',
            'غير متأكدة',
            'احجزي استشارة',
        ];
    }
}
