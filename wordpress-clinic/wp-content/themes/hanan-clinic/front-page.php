<?php
get_header();

$doctorImageId = (int) get_theme_mod('hanan_doctor_image');
$clinicImageId = (int) get_theme_mod('hanan_clinic_image');
$bookingUrl = hanan_clinic_booking_url();
$appUrl = hanan_clinic_app_url();
$homeData = hanan_clinic_home_data();
$products = hanan_clinic_products();
$categories = is_array($homeData['categories'] ?? null) ? $homeData['categories'] : [];
$activeOffer = hanan_clinic_active_offer();
$offerTitle = !empty($activeOffer['title']) ? $activeOffer['title'] : hanan_clinic_text('offer_title');
$offerText = !empty($activeOffer['description']) ? $activeOffer['description'] : hanan_clinic_text('offer_text');
$offerImage = !empty($activeOffer['image']) ? $activeOffer['image'] : '';
$offerBadge = '';
if (!empty($activeOffer['discount_value'])) {
    $discountValue = rtrim(rtrim(number_format_i18n((float) $activeOffer['discount_value'], 2), '0'), '.');
    $offerBadge = ($activeOffer['discount_type'] ?? '') === 'percentage'
        ? $discountValue . '%'
        : hanan_clinic_format_price((float) $activeOffer['discount_value'], 'ل.س');
}
$services = [
    ['01', 'service_skin_title', 'service_skin_text', 'skin'],
    ['02', 'service_injection_title', 'service_injection_text', 'inject'],
    ['03', 'service_laser_title', 'service_laser_text', 'laser'],
    ['04', 'service_consult_title', 'service_consult_text', 'consult'],
];
$results = [
    ['result_1', 'tone'],
    ['result_2', 'texture'],
    ['result_3', 'glow'],
];
?>

<main id="main-content">
    <section class="hero-section" id="home">
        <div class="hero-orb hero-orb-one"></div>
        <div class="hero-orb hero-orb-two"></div>
        <div class="site-container hero-grid">
            <div class="hero-copy reveal">
                <span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('hero_kicker')); ?></span>
                <h1><?php echo esc_html(hanan_clinic_text('hero_title')); ?></h1>
                <p><?php echo esc_html(hanan_clinic_text('hero_text')); ?></p>
                <div class="hero-buttons">
                    <a class="button" href="<?php echo esc_url($bookingUrl); ?>"><?php echo esc_html(hanan_clinic_text('book_now')); ?><span aria-hidden="true">↗</span></a>
                    <a class="button button-ghost" href="#services"><?php echo esc_html(hanan_clinic_text('explore_services')); ?></a>
                </div>
                <div class="hero-trust">
                    <div class="trust-avatars"><span>H</span><span>A</span><span>+</span></div>
                    <div><strong><?php echo esc_html(hanan_clinic_text('medical_consultation')); ?></strong><small><?php echo esc_html(hanan_clinic_text('natural_results')); ?></small></div>
                </div>
            </div>

            <div class="hero-visual reveal reveal-delay">
                <div class="portrait-backdrop"></div>
                <div class="doctor-portrait">
                    <?php if ($doctorImageId) : ?>
                        <?php echo wp_get_attachment_image($doctorImageId, 'large', false, ['alt' => get_theme_mod('hanan_doctor_name', 'Hanova Care')]); ?>
                    <?php else : ?>
                        <div class="portrait-placeholder"><span>HA</span><small><?php echo esc_html(hanan_clinic_text('doctor_photo')); ?></small></div>
                    <?php endif; ?>
                </div>
                <div class="floating-note note-top"><span class="note-mark">+</span><div><strong><?php echo esc_html(hanan_clinic_text('medical_consultation')); ?></strong><small><?php echo esc_html(hanan_clinic_text('natural_results')); ?></small></div></div>
                <div class="floating-note note-bottom"><span class="pulse-dot"></span><strong><?php echo esc_html(hanan_clinic_text('book_now')); ?></strong></div>
                <div class="portrait-signature"><?php echo esc_html(get_theme_mod('hanan_doctor_name', 'Hanova Care')); ?></div>
            </div>
        </div>

        <div class="site-container hero-stats reveal">
            <div><strong>+8</strong><span><?php echo esc_html(hanan_clinic_text('years')); ?></span></div>
            <div><strong>+2K</strong><span><?php echo esc_html(hanan_clinic_text('patients')); ?></span></div>
            <div><strong>12</strong><span><?php echo esc_html(hanan_clinic_text('treatments')); ?></span></div>
        </div>
    </section>

    <section class="section about-section" id="about">
        <div class="site-container about-grid">
            <div class="about-visual reveal">
                <div class="about-photo">
                    <?php if ($clinicImageId) : ?>
                        <?php echo wp_get_attachment_image($clinicImageId, 'large', false, ['alt' => hanan_clinic_text('about')]); ?>
                    <?php else : ?>
                        <div class="clinic-placeholder"><span class="clinic-line-art"></span><small><?php echo esc_html(hanan_clinic_text('brand')); ?></small></div>
                    <?php endif; ?>
                </div>
                <div class="about-seal"><span>H</span><small>EST.<br>2018</small></div>
            </div>
            <div class="section-copy reveal reveal-delay">
                <span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('about_kicker')); ?></span>
                <h2><?php echo esc_html(hanan_clinic_text('about_title')); ?></h2>
                <p><?php echo esc_html(hanan_clinic_text('about_text')); ?></p>
                <ul class="care-list">
                    <li><span>01</span><?php echo esc_html(hanan_clinic_text('about_point_1')); ?></li>
                    <li><span>02</span><?php echo esc_html(hanan_clinic_text('about_point_2')); ?></li>
                    <li><span>03</span><?php echo esc_html(hanan_clinic_text('about_point_3')); ?></li>
                </ul>
                <a class="text-link arrow-link" href="#contact"><?php echo esc_html(hanan_clinic_text('book_now')); ?><span>↗</span></a>
            </div>
        </div>
    </section>

    <section class="section services-section" id="services">
        <div class="site-container">
            <div class="section-heading reveal">
                <div><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('services_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('services_title')); ?></h2></div>
                <p><?php echo esc_html(hanan_clinic_text('services_text')); ?></p>
            </div>
            <div class="services-grid">
                <?php foreach ($services as [$number, $title, $text, $kind]) : ?>
                    <article class="service-card reveal" data-service="<?php echo esc_attr($kind); ?>">
                        <div class="service-top"><span class="service-number"><?php echo esc_html($number); ?></span><span class="service-glyph" aria-hidden="true"></span></div>
                        <h3><?php echo esc_html(hanan_clinic_text($title)); ?></h3>
                        <p><?php echo esc_html(hanan_clinic_text($text)); ?></p>
                        <a href="<?php echo esc_url($bookingUrl); ?>"><?php echo esc_html(hanan_clinic_text('learn_more')); ?><span>↗</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if ($categories !== []) : ?>
        <section class="section concerns-section" id="concerns">
            <div class="site-container">
                <div class="section-heading compact reveal">
                    <div><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('concerns_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('concerns_title')); ?></h2></div>
                </div>
                <div class="concerns-list reveal" aria-label="<?php echo esc_attr(hanan_clinic_text('concerns_title')); ?>">
                    <?php foreach (array_slice($categories, 0, 12) as $category) : ?>
                        <?php if (!is_array($category) || empty($category['name'])) { continue; } ?>
                        <a class="concern-chip" href="<?php echo esc_url($appUrl); ?>">
                            <span aria-hidden="true">+</span><?php echo esc_html((string) $category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="section results-section" id="results">
        <div class="site-container">
            <div class="section-heading compact reveal">
                <div><span class="eyebrow eyebrow-light"><i></i><?php echo esc_html(hanan_clinic_text('results_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('results_title')); ?></h2></div>
                <p><?php echo esc_html(hanan_clinic_text('results_text')); ?></p>
            </div>
            <div class="results-grid">
                <?php foreach ($results as $index => [$title, $kind]) : ?>
                    <article class="result-card reveal">
                        <div class="result-image result-<?php echo esc_attr($kind); ?>">
                            <span class="result-divider"></span>
                            <span class="before-label"><?php echo esc_html(hanan_clinic_text('before')); ?></span>
                            <span class="after-label"><?php echo esc_html(hanan_clinic_text('after')); ?></span>
                        </div>
                        <div class="result-caption"><span>0<?php echo esc_html((string) ($index + 1)); ?></span><strong><?php echo esc_html(hanan_clinic_text($title)); ?></strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section products-section" id="products">
        <div class="site-container">
            <div class="section-heading reveal">
                <div><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('products_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('products_title')); ?></h2></div>
                <p><?php echo esc_html(hanan_clinic_text('products_text')); ?></p>
            </div>
            <div class="products-grid">
                <?php foreach ($products as $index => $product) : ?>
                    <article class="product-card reveal">
                        <div class="product-visual product-tone-<?php echo esc_attr((string) (($index % 4) + 1)); ?>">
                            <?php if (!empty($product['image'])) : ?>
                                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="product-bottle"><span>H</span><small>CLINIC<br>FORMULA</small></div>
                            <?php endif; ?>
                            <span class="product-index">0<?php echo esc_html((string) ($index + 1)); ?></span>
                        </div>
                        <div class="product-copy">
                            <div><h3><?php echo esc_html($product['name']); ?></h3><?php if (!empty($product['category'])) : ?><small><?php echo esc_html($product['category']); ?></small><?php endif; ?></div>
                            <?php if ($product['price'] !== null) : ?><strong><?php echo esc_html(hanan_clinic_format_price($product['price'], $product['currency'])); ?></strong><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="center-action reveal"><a class="button button-outline" href="<?php echo esc_url($appUrl); ?>"><?php echo esc_html(hanan_clinic_text('view_in_app')); ?><span>↗</span></a></div>
        </div>
    </section>

    <section class="offer-section">
        <div class="site-container">
            <div class="offer-card reveal<?php echo $offerImage !== '' ? ' offer-card-has-image' : ''; ?>">
                <?php if ($offerImage !== '') : ?><img class="offer-media" src="<?php echo esc_url($offerImage); ?>" alt="" loading="lazy"><?php endif; ?>
                <span class="offer-flower flower-one"></span><span class="offer-flower flower-two"></span>
                <div><span class="eyebrow eyebrow-light"><i></i><?php echo esc_html(hanan_clinic_text('featured_offer')); ?></span><?php if ($offerBadge !== '') : ?><span class="offer-badge"><?php echo esc_html($offerBadge); ?></span><?php endif; ?><h2><?php echo esc_html($offerTitle); ?></h2><p><?php echo esc_html($offerText); ?></p></div>
                <a class="button button-light" href="<?php echo esc_url($bookingUrl); ?>"><?php echo esc_html(hanan_clinic_text('book_now')); ?><span>↗</span></a>
            </div>
        </div>
    </section>

    <section class="section articles-section" id="articles">
        <div class="site-container">
            <div class="section-heading reveal">
                <div><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('articles_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('articles_title')); ?></h2></div>
                <p><?php echo esc_html(hanan_clinic_text('articles_text')); ?></p>
            </div>
            <div class="articles-grid">
                <?php
                $postsQuery = new WP_Query(['posts_per_page' => 3, 'post_status' => 'publish']);
                if ($postsQuery->have_posts()) :
                    while ($postsQuery->have_posts()) : $postsQuery->the_post(); ?>
                        <article class="article-card reveal">
                            <a class="article-image" href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } else { echo '<span class="article-placeholder"></span>'; } ?></a>
                            <div class="article-meta"><span><?php echo esc_html(get_the_date('d M Y')); ?></span><span>Journal</span></div>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
                            <a class="text-link arrow-link" href="<?php the_permalink(); ?>"><?php echo esc_html(hanan_clinic_text('read_article')); ?><span>↗</span></a>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else :
                    foreach (range(1, 3) as $index) : ?>
                        <article class="article-card reveal">
                            <div class="article-image"><span class="article-placeholder article-placeholder-<?php echo esc_attr((string) $index); ?>"></span></div>
                            <div class="article-meta"><span><?php echo esc_html(wp_date('d M Y')); ?></span><span>Journal</span></div>
                            <h3><?php echo esc_html(hanan_clinic_text('article_' . $index . '_title')); ?></h3>
                            <p><?php echo esc_html(hanan_clinic_text('article_' . $index . '_text')); ?></p>
                            <a class="text-link arrow-link" href="#contact"><?php echo esc_html(hanan_clinic_text('read_article')); ?><span>↗</span></a>
                        </article>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </section>

    <section class="section app-section">
        <div class="site-container app-card reveal">
            <div class="app-copy"><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('app_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('app_title')); ?></h2><p><?php echo esc_html(hanan_clinic_text('app_text')); ?></p><a class="button" href="<?php echo esc_url($appUrl); ?>"><?php echo esc_html(hanan_clinic_text('download_app')); ?><span>↓</span></a></div>
            <div class="phone-mockup" aria-hidden="true"><div class="phone-screen"><span class="phone-pill"></span><div class="phone-greeting">Hanova</div><div class="phone-banner"></div><div class="phone-products"><i></i><i></i><i></i></div><div class="phone-nav"><span></span><span></span><span></span><span></span></div></div></div>
        </div>
    </section>

    <section class="section contact-section" id="contact">
        <div class="site-container contact-grid">
            <div class="contact-copy reveal"><span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('contact_kicker')); ?></span><h2><?php echo esc_html(hanan_clinic_text('contact_title')); ?></h2><p><?php echo esc_html(hanan_clinic_text('contact_text')); ?></p><a class="button" href="<?php echo esc_url($bookingUrl); ?>"><?php echo esc_html(hanan_clinic_text('book_now')); ?><span>↗</span></a></div>
            <div class="contact-cards reveal reveal-delay">
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', get_theme_mod('hanan_phone', '+963900000000'))); ?>"><span class="contact-icon">01</span><span><small><?php echo esc_html(hanan_clinic_text('call_us')); ?></small><strong><?php echo esc_html(get_theme_mod('hanan_phone', '+963 900 000 000')); ?></strong></span><i>↗</i></a>
                <a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D+/', '', get_theme_mod('hanan_whatsapp', '963900000000'))); ?>" target="_blank" rel="noopener"><span class="contact-icon">02</span><span><small><?php echo esc_html(hanan_clinic_text('whatsapp')); ?></small><strong>WhatsApp</strong></span><i>↗</i></a>
                <div><span class="contact-icon">03</span><span><small><?php echo esc_html(hanan_clinic_text('address')); ?></small><strong><?php echo esc_html(hanan_clinic_text('address_value')); ?></strong></span></div>
                <div><span class="contact-icon">04</span><span><small><?php echo esc_html(hanan_clinic_text('hours')); ?></small><strong><?php echo esc_html(hanan_clinic_text('hours_value')); ?></strong></span></div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
