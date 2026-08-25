<?php

if (!defined('ABSPATH')) {
    exit;
}

function hanan_clinic_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height' => 96,
        'width' => 96,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    register_nav_menus(['primary' => __('Primary menu', 'hanan-clinic')]);
}
add_action('after_setup_theme', 'hanan_clinic_setup');

function hanan_clinic_assets(): void
{
    $theme = wp_get_theme();
    wp_enqueue_style(
        'hanan-clinic-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('hanan-clinic-style', get_stylesheet_uri(), [], $theme->get('Version'));
    wp_enqueue_script(
        'hanan-clinic-site',
        get_template_directory_uri() . '/assets/js/site.js',
        [],
        filemtime(get_template_directory() . '/assets/js/site.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'hanan_clinic_assets');

function hanan_clinic_language(): string
{
    $requested = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if (in_array($requested, ['ar', 'en'], true)) {
        return $requested;
    }

    $saved = isset($_COOKIE['hanan_site_lang']) ? sanitize_key(wp_unslash($_COOKIE['hanan_site_lang'])) : '';
    return in_array($saved, ['ar', 'en'], true) ? $saved : 'ar';
}

function hanan_clinic_remember_language(): void
{
    if (!isset($_GET['lang'])) {
        return;
    }

    $language = sanitize_key(wp_unslash($_GET['lang']));
    if (!in_array($language, ['ar', 'en'], true)) {
        return;
    }

    setcookie('hanan_site_lang', $language, [
        'expires' => time() + YEAR_IN_SECONDS,
        'path' => COOKIEPATH ?: '/',
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
add_action('init', 'hanan_clinic_remember_language');

function hanan_clinic_is_rtl(): bool
{
    return hanan_clinic_language() === 'ar';
}

function hanan_clinic_language_url(string $language): string
{
    return esc_url(add_query_arg('lang', $language, home_url('/')));
}

function hanan_clinic_section_url(string $section): string
{
    return esc_url(add_query_arg('lang', hanan_clinic_language(), home_url('/')) . '#' . sanitize_title($section));
}

function hanan_clinic_booking_url(): string
{
    $url = trim((string) get_theme_mod('hanan_booking_url', ''));
    return $url === '' || $url === '#' ? hanan_clinic_section_url('contact') : esc_url_raw($url);
}

function hanan_clinic_app_url(): string
{
    $url = trim((string) get_theme_mod('hanan_app_download_url', ''));
    return $url === '' || $url === '#' ? hanan_clinic_booking_url() : esc_url_raw($url);
}

function hanan_clinic_text(string $key): string
{
    $copy = [
        'ar' => [
            'brand' => 'Hanova',
            'brand_tag' => 'الجمال · العيادة · العناية',
            'home' => 'الرئيسية',
            'about' => 'عن Hanova',
            'services' => 'الخدمات',
            'results' => 'النتائج',
            'products' => 'المنتجات',
            'articles' => 'المقالات',
            'contact' => 'تواصل معنا',
            'book_now' => 'احجزي الآن',
            'menu' => 'القائمة',
            'close' => 'إغلاق',
            'hero_kicker' => 'HANOVA CONNECTED CARE',
            'hero_title' => 'العناية التي تفهم بشرتكِ.',
            'hero_text' => 'تجربة واحدة تجمع الاستشارة، المواعيد، المنتجات المختارة، ومتابعة العناية بوضوح وأسهل.',
            'explore_services' => 'اكتشفي التجربة',
            'medical_consultation' => 'استشارة وخطة عناية',
            'natural_results' => 'مواعيد ومنتجات متاحة فعلياً',
            'doctor_photo' => 'صورة Hanova',
            'years' => 'سنوات خبرة',
            'patients' => 'حالة تمت متابعتها',
            'treatments' => 'خدمات علاجية',
            'about_kicker' => 'العناية التي تستحقينها',
            'about_title' => 'رحلة عناية واحدة، بقرارات أوضح.',
            'about_text' => 'نربط الاستشارة بالعناية اليومية حتى تختاري المناسب لبشرتكِ، وتحجزي الموعد الصحيح، وتتابعي طلباتكِ من مكان واحد.',
            'about_point_1' => 'مواعيد تُعرض حسب المتاح فعلياً',
            'about_point_2' => 'منتجات مختارة حسب احتياج البشرة',
            'about_point_3' => 'مساعدة ومتابعة في التطبيق',
            'services_kicker' => 'خدمات العيادة',
            'services_title' => 'عناية مصممة لكل مرحلة.',
            'services_text' => 'من صحة البشرة اليومية إلى الإجراءات التجميلية الدقيقة، نختار ما تحتاجه بشرتك فعلًا.',
            'service_skin_title' => 'علاجات البشرة',
            'service_skin_text' => 'خطط لعلاج التصبغات، آثار الحبوب، الجفاف والإجهاد.',
            'service_injection_title' => 'الحقن التجميلي',
            'service_injection_text' => 'إجراءات مدروسة للحفاظ على تعابير طبيعية ومتوازنة.',
            'service_laser_title' => 'الليزر والتقنيات',
            'service_laser_text' => 'حلول تقنية لتحسين الملمس، اللون وتحفيز نضارة البشرة.',
            'service_consult_title' => 'الاستشارة',
            'service_consult_text' => 'تقييم حضوري أو أونلاين لبناء خطة علاج مناسبة.',
            'learn_more' => 'اعرفي المزيد',
            'results_kicker' => 'نتائج حقيقية',
            'results_title' => 'تغيير هادئ يمكنكِ ملاحظته.',
            'results_text' => 'نعرض النتائج بموافقة أصحابها، مع اختلاف الاستجابة من شخص لآخر.',
            'before' => 'قبل',
            'after' => 'بعد',
            'result_1' => 'توحيد لون البشرة',
            'result_2' => 'تحسين ملمس البشرة',
            'result_3' => 'استعادة النضارة',
            'products_kicker' => 'مختارات العيادة',
            'products_title' => 'منتجات نثق بها لبشرتكِ.',
            'products_text' => 'تصل المختارات والأسعار مباشرة من نظام Hanova. أكملي الطلب بأمان من التطبيق.',
            'view_in_app' => 'تسوّقي من التطبيق',
            'concerns_kicker' => 'ابدئي من احتياجكِ',
            'concerns_title' => 'اختاري ما يهم بشرتكِ.',
            'featured_offer' => 'عرض مميز',
            'offer_title' => 'ابدئي بخطة عناية تناسب بشرتكِ.',
            'offer_text' => 'احجزي جلسة تقييم واحصلي على توصيات واضحة للروتين والجلسات المناسبة.',
            'articles_kicker' => 'من مدونة العيادة',
            'articles_title' => 'معرفة بسيطة لقرارات أفضل.',
            'articles_text' => 'مقالات قصيرة وموثوقة تساعدك على فهم بشرتك وخيارات العناية.',
            'read_article' => 'قراءة المقال',
            'article_1_title' => 'كيف تبنين روتين عناية مناسبًا لبشرتك؟',
            'article_1_text' => 'خطوات عملية لاختيار الغسول، المرطب والواقي الشمسي.',
            'article_2_title' => 'متى تحتاج التصبغات إلى تقييم طبي؟',
            'article_2_text' => 'علامات تساعدك على معرفة الوقت المناسب للاستشارة.',
            'article_3_title' => 'ما الذي يجب معرفته قبل جلسة تجميل؟',
            'article_3_text' => 'أسئلة مهمة وتوقعات واقعية قبل أي إجراء.',
            'app_kicker' => 'Hanova معكِ دائماً',
            'app_title' => 'الحجز والطلب والمساعد في تطبيق واحد.',
            'app_text' => 'تصفحي المنتجات، اسألي مساعد الجمال، تابعي طلبك واحجزي استشارتك بسهولة.',
            'download_app' => 'تحميل التطبيق',
            'contact_kicker' => 'نحن هنا لمساعدتك',
            'contact_title' => 'خطوتكِ الأولى تبدأ بسؤال.',
            'contact_text' => 'تواصلي معنا لمعرفة الخدمة الأنسب أو احجزي استشارة من التطبيق.',
            'call_us' => 'اتصلي بنا',
            'whatsapp' => 'واتساب',
            'address' => 'العنوان',
            'address_value' => 'دمشق، سوريا',
            'hours' => 'أوقات الدوام',
            'hours_value' => 'السبت - الخميس، 10 صباحًا - 7 مساءً',
            'footer_text' => 'عناية متصلة، واضحة، ومصممة حولكِ.',
            'rights' => 'جميع الحقوق محفوظة.',
            'privacy' => 'سياسة الخصوصية',
            'fallback_product_1' => 'مرطب طبي للبشرة',
            'fallback_product_2' => 'سيروم الإشراق',
            'fallback_product_3' => 'واقي شمسي يومي',
            'fallback_product_4' => 'غسول لطيف',
        ],
        'en' => [
            'brand' => 'Hanova',
            'brand_tag' => 'Beauty · Clinic · Care',
            'home' => 'Home',
            'about' => 'About Hanova',
            'services' => 'Services',
            'results' => 'Results',
            'products' => 'Products',
            'articles' => 'Journal',
            'contact' => 'Contact',
            'book_now' => 'Book now',
            'menu' => 'Menu',
            'close' => 'Close',
            'hero_kicker' => 'HANOVA CONNECTED CARE',
            'hero_title' => 'Care that understands your skin.',
            'hero_text' => 'One experience for consultations, live appointment availability, curated products, and clear care follow-up.',
            'explore_services' => 'Explore the experience',
            'medical_consultation' => 'Consultation and care plan',
            'natural_results' => 'Live appointments and products',
            'doctor_photo' => 'Hanova portrait',
            'years' => 'Years of experience',
            'patients' => 'Cases followed',
            'treatments' => 'Treatment options',
            'about_kicker' => 'The care you deserve',
            'about_title' => 'One care journey. Clearer choices.',
            'about_text' => 'We connect your consultation to your daily care so you can choose the right products, book the right appointment, and follow every order in one place.',
            'about_point_1' => 'Appointments shown from real availability',
            'about_point_2' => 'Products curated around skin needs',
            'about_point_3' => 'Assistant and follow-up in the app',
            'services_kicker' => 'Clinic services',
            'services_title' => 'Care designed for every chapter.',
            'services_text' => 'From daily skin health to precise aesthetic treatments, we recommend only what your skin truly needs.',
            'service_skin_title' => 'Skin treatments',
            'service_skin_text' => 'Plans for pigmentation, acne marks, dryness, and stressed skin.',
            'service_injection_title' => 'Injectables',
            'service_injection_text' => 'Considered treatments that preserve natural expression and balance.',
            'service_laser_title' => 'Laser and technology',
            'service_laser_text' => 'Technology-led options for texture, tone, and skin vitality.',
            'service_consult_title' => 'Consultations',
            'service_consult_text' => 'In-clinic or online assessment to build the right treatment plan.',
            'learn_more' => 'Learn more',
            'results_kicker' => 'Real results',
            'results_title' => 'Subtle change you can see and feel.',
            'results_text' => 'Results are shared with consent. Individual outcomes will always vary.',
            'before' => 'Before',
            'after' => 'After',
            'result_1' => 'More even skin tone',
            'result_2' => 'Improved skin texture',
            'result_3' => 'Restored radiance',
            'products_kicker' => 'Clinic edit',
            'products_title' => 'Products we trust for your skin.',
            'products_text' => 'Products and prices arrive directly from the Hanova system. Complete your order securely in the app.',
            'view_in_app' => 'Shop in the app',
            'concerns_kicker' => 'Start with your concern',
            'concerns_title' => 'Choose what matters to your skin.',
            'featured_offer' => 'Featured offer',
            'offer_title' => 'Start with a plan made for your skin.',
            'offer_text' => 'Book an assessment and receive clear recommendations for your routine and treatments.',
            'articles_kicker' => 'From the clinic journal',
            'articles_title' => 'Simple knowledge. Better decisions.',
            'articles_text' => 'Short, reliable reads to help you understand your skin and care choices.',
            'read_article' => 'Read article',
            'article_1_title' => 'How to build a routine that suits your skin',
            'article_1_text' => 'Practical steps for choosing a cleanser, moisturizer, and sunscreen.',
            'article_2_title' => 'When does pigmentation need medical advice?',
            'article_2_text' => 'The signs that tell you it may be time for a consultation.',
            'article_3_title' => 'What to know before an aesthetic treatment',
            'article_3_text' => 'Useful questions and realistic expectations before any procedure.',
            'app_kicker' => 'Hanova, always close',
            'app_title' => 'Booking, shopping, and your assistant in one app.',
            'app_text' => 'Browse products, ask the beauty assistant, track orders, and book consultations with ease.',
            'download_app' => 'Download the app',
            'contact_kicker' => 'We are here to help',
            'contact_title' => 'Your first step can be a simple question.',
            'contact_text' => 'Talk to us about the right service or book a consultation through the app.',
            'call_us' => 'Call us',
            'whatsapp' => 'WhatsApp',
            'address' => 'Address',
            'address_value' => 'Damascus, Syria',
            'hours' => 'Clinic hours',
            'hours_value' => 'Saturday - Thursday, 10 AM - 7 PM',
            'footer_text' => 'Connected, clear care designed around you.',
            'rights' => 'All rights reserved.',
            'privacy' => 'Privacy policy',
            'fallback_product_1' => 'Medical moisturizer',
            'fallback_product_2' => 'Radiance serum',
            'fallback_product_3' => 'Daily sunscreen',
            'fallback_product_4' => 'Gentle cleanser',
        ],
    ];

    $language = hanan_clinic_language();
    return $copy[$language][$key] ?? $copy['ar'][$key] ?? $key;
}

function hanan_clinic_body_classes(array $classes): array
{
    $classes[] = 'hanan-clinic';
    $classes[] = hanan_clinic_is_rtl() ? 'is-rtl' : 'is-ltr';
    return $classes;
}
add_filter('body_class', 'hanan_clinic_body_classes');

function hanan_clinic_customize(WP_Customize_Manager $customizer): void
{
    $customizer->add_section('hanan_clinic_settings', [
        'title' => 'Clinic details',
        'priority' => 30,
    ]);

    $fields = [
        'doctor_name' => ['Care lead name', 'Hanova Care', 'sanitize_text_field'],
        'phone' => ['Phone number', '+963 900 000 000', 'sanitize_text_field'],
        'whatsapp' => ['WhatsApp number', '963900000000', 'sanitize_text_field'],
        'instagram_url' => ['Instagram URL', '#', 'esc_url_raw'],
        'booking_url' => ['Booking URL', '', 'esc_url_raw'],
        'app_download_url' => ['App download URL', '', 'esc_url_raw'],
        'api_url' => ['Hanova API URL', 'https://hanova-api-production.up.railway.app/api', 'esc_url_raw'],
    ];

    foreach ($fields as $id => [$label, $default, $sanitize]) {
        $customizer->add_setting('hanan_' . $id, [
            'default' => $default,
            'sanitize_callback' => $sanitize,
        ]);
        $customizer->add_control('hanan_' . $id, [
            'label' => $label,
            'section' => 'hanan_clinic_settings',
            'type' => 'text',
        ]);
    }

    $customizer->add_setting('hanan_doctor_image', ['sanitize_callback' => 'absint']);
    $customizer->add_control(new WP_Customize_Media_Control($customizer, 'hanan_doctor_image', [
        'label' => 'Doctor portrait',
        'section' => 'hanan_clinic_settings',
        'mime_type' => 'image',
    ]));

    $customizer->add_setting('hanan_clinic_image', ['sanitize_callback' => 'absint']);
    $customizer->add_control(new WP_Customize_Media_Control($customizer, 'hanan_clinic_image', [
        'label' => 'Clinic / about image',
        'section' => 'hanan_clinic_settings',
        'mime_type' => 'image',
    ]));
}
add_action('customize_register', 'hanan_clinic_customize');

function hanan_clinic_register_content_types(): void
{
    register_post_type('clinic_service', [
        'labels' => ['name' => 'Clinic services', 'singular_name' => 'Clinic service'],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-heart',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'rewrite' => ['slug' => 'services'],
    ]);

    register_post_type('clinic_result', [
        'labels' => ['name' => 'Treatment results', 'singular_name' => 'Treatment result'],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'rewrite' => ['slug' => 'results'],
    ]);
}
add_action('init', 'hanan_clinic_register_content_types');

function hanan_clinic_api_url(): string
{
    $default = 'https://hanova-api-production.up.railway.app/api';
    $apiUrl = trim((string) get_theme_mod('hanan_api_url', $default));

    // Existing local installations stored the old development URL as a theme option.
    if ($apiUrl === '' || untrailingslashit($apiUrl) === 'http://127.0.0.1:8000/api') {
        $apiUrl = $default;
    }

    return untrailingslashit(esc_url_raw($apiUrl));
}

function hanan_clinic_api_asset_url(string $path): string
{
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return esc_url_raw($path);
    }

    $origin = preg_replace('#/api$#', '', hanan_clinic_api_url());
    return esc_url_raw(untrailingslashit((string) $origin) . '/' . ltrim($path, '/'));
}

function hanan_clinic_home_data(): array
{
    $language = hanan_clinic_language();
    $cacheKey = 'hanova_home_v2_' . $language;
    $cached = get_transient($cacheKey);
    if (is_array($cached)) {
        return $cached;
    }

    $home = ['products' => [], 'categories' => [], 'active_offer' => null];
    $response = wp_remote_get(hanan_clinic_api_url() . '/home', [
        // A short cached request keeps the marketing site fast if the API is unavailable.
        'timeout' => 1.5,
        'redirection' => 2,
        'headers' => [
            'Accept-Language' => $language,
            'Accept' => 'application/json',
        ],
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $payload = json_decode(wp_remote_retrieve_body($response), true);
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $home['products'] = is_array($data['products'] ?? null) ? $data['products'] : [];
        $home['categories'] = is_array($data['categories'] ?? null) ? $data['categories'] : [];
        $home['active_offer'] = is_array($data['active_offer'] ?? null) ? $data['active_offer'] : null;
    }

    set_transient($cacheKey, $home, 5 * MINUTE_IN_SECONDS);
    return $home;
}

function hanan_clinic_clear_home_cache(): void
{
    delete_transient('hanova_home_v2_ar');
    delete_transient('hanova_home_v2_en');
}
add_action('customize_save_after', 'hanan_clinic_clear_home_cache');

function hanan_clinic_products(): array
{
    $home = hanan_clinic_home_data();
    $products = [];

    foreach (array_slice($home['products'], 0, 6) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $products[] = [
            'id' => isset($item['id']) ? (int) $item['id'] : 0,
            'name' => (string) ($item['name'] ?? ''),
            'description' => (string) ($item['description'] ?? ''),
            'price' => isset($item['price']) ? (float) $item['price'] : null,
            'currency' => (string) ($item['currency_symbol'] ?? 'ل.س'),
            'image' => hanan_clinic_api_asset_url((string) ($item['image_url'] ?? $item['image'] ?? '')),
            'category' => (string) ($item['category'] ?? ''),
            'concerns' => is_array($item['concerns'] ?? null) ? $item['concerns'] : [],
        ];
    }

    if ($products !== []) {
        return $products;
    }

    foreach (range(1, 4) as $index) {
        $products[] = [
            'id' => 0,
            'name' => hanan_clinic_text('fallback_product_' . $index),
            'description' => '',
            'price' => null,
            'currency' => 'ل.س',
            'image' => '',
            'category' => '',
            'concerns' => [],
        ];
    }

    return $products;
}

function hanan_clinic_active_offer(): ?array
{
    $offer = hanan_clinic_home_data()['active_offer'];
    if (!is_array($offer)) {
        return null;
    }

    return [
        'title' => (string) ($offer['title'] ?? ''),
        'description' => (string) ($offer['description'] ?? ''),
        'discount_type' => (string) ($offer['discount_type'] ?? ''),
        'discount_value' => isset($offer['discount_value']) ? (float) $offer['discount_value'] : null,
        'image' => hanan_clinic_api_asset_url((string) ($offer['image'] ?? '')),
    ];
}

function hanan_clinic_format_price(?float $price, string $currency): string
{
    if ($price === null) {
        return '';
    }

    $decimals = floor($price) === $price ? 0 : 2;
    $amount = number_format_i18n($price, $decimals);

    if (hanan_clinic_is_rtl()) {
        return $amount . ' ' . $currency;
    }

    return $currency . ' ' . $amount;
}

function hanan_clinic_meta_description(): void
{
    if (!is_front_page()) {
        return;
    }

    printf(
        '<meta name="description" content="%s">' . "\n",
        esc_attr(hanan_clinic_text('hero_text'))
    );
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr(hanan_clinic_text('brand')));
    printf('<meta property="og:description" content="%s">' . "\n", esc_attr(hanan_clinic_text('hero_text')));
    echo '<meta property="og:type" content="website">' . "\n";
    printf('<link rel="alternate" hreflang="ar" href="%s">' . "\n", esc_url(hanan_clinic_language_url('ar')));
    printf('<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url(hanan_clinic_language_url('en')));
    printf('<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url(hanan_clinic_language_url('ar')));
}
add_action('wp_head', 'hanan_clinic_meta_description', 2);

function hanan_clinic_document_title(string $title): string
{
    if (!is_front_page()) {
        return $title;
    }

    return hanan_clinic_text('brand') . ' | ' . hanan_clinic_text('brand_tag');
}
add_filter('pre_get_document_title', 'hanan_clinic_document_title');
