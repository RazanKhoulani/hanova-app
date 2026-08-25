<!doctype html>
<html lang="<?php echo esc_attr(hanan_clinic_language()); ?>" dir="<?php echo hanan_clinic_is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main-content"><?php echo esc_html(hanan_clinic_text('home')); ?></a>

<header class="site-header" data-site-header>
    <div class="site-container header-inner">
        <a class="clinic-brand" href="<?php echo hanan_clinic_language_url(hanan_clinic_language()); ?>" aria-label="<?php echo esc_attr(hanan_clinic_text('brand')); ?>">
            <?php if (has_custom_logo()) : ?>
                <span class="brand-logo"><?php echo wp_get_attachment_image((int) get_theme_mod('custom_logo'), 'thumbnail'); ?></span>
            <?php else : ?>
                <span class="brand-symbol" aria-hidden="true">
                    <span></span><span></span><span></span>
                </span>
            <?php endif; ?>
            <span class="brand-words">
                <strong><?php echo esc_html(hanan_clinic_text('brand')); ?></strong>
                <small><?php echo esc_html(hanan_clinic_text('brand_tag')); ?></small>
            </span>
        </a>

        <nav class="desktop-nav" aria-label="<?php echo esc_attr(hanan_clinic_text('menu')); ?>">
            <a href="<?php echo hanan_clinic_section_url('home'); ?>"><?php echo esc_html(hanan_clinic_text('home')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('about'); ?>"><?php echo esc_html(hanan_clinic_text('about')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('services'); ?>"><?php echo esc_html(hanan_clinic_text('services')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('results'); ?>"><?php echo esc_html(hanan_clinic_text('results')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('products'); ?>"><?php echo esc_html(hanan_clinic_text('products')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('articles'); ?>"><?php echo esc_html(hanan_clinic_text('articles')); ?></a>
        </nav>

        <div class="header-actions">
            <div class="language-switch" aria-label="Language">
                <a href="<?php echo hanan_clinic_language_url('ar'); ?>" class="<?php echo hanan_clinic_is_rtl() ? 'active' : ''; ?>">ع</a>
                <span></span>
                <a href="<?php echo hanan_clinic_language_url('en'); ?>" class="<?php echo !hanan_clinic_is_rtl() ? 'active' : ''; ?>">EN</a>
            </div>
            <a class="button button-small header-book" href="<?php echo esc_url(hanan_clinic_booking_url()); ?>">
                <?php echo esc_html(hanan_clinic_text('book_now')); ?>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>
                <span></span><span></span>
                <span class="screen-reader-text"><?php echo esc_html(hanan_clinic_text('menu')); ?></span>
            </button>
        </div>
    </div>

    <div class="mobile-menu" id="mobile-menu" data-mobile-menu hidden>
        <div class="site-container mobile-menu-inner">
            <a href="<?php echo hanan_clinic_section_url('home'); ?>"><?php echo esc_html(hanan_clinic_text('home')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('about'); ?>"><?php echo esc_html(hanan_clinic_text('about')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('services'); ?>"><?php echo esc_html(hanan_clinic_text('services')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('results'); ?>"><?php echo esc_html(hanan_clinic_text('results')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('products'); ?>"><?php echo esc_html(hanan_clinic_text('products')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('articles'); ?>"><?php echo esc_html(hanan_clinic_text('articles')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('contact'); ?>"><?php echo esc_html(hanan_clinic_text('contact')); ?></a>
        </div>
    </div>
</header>
