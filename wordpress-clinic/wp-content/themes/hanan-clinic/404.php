<?php get_header(); ?>

<main id="main-content" class="content-shell">
    <div class="site-container">
        <article>
            <span class="eyebrow"><i></i>404</span>
            <h1><?php echo hanan_clinic_is_rtl() ? 'الصفحة غير موجودة' : 'Page not found'; ?></h1>
            <p><?php echo hanan_clinic_is_rtl() ? 'يبدو أن الرابط الذي فتحته لم يعد متاحًا.' : 'The page you opened is no longer available.'; ?></p>
            <a class="button" href="<?php echo hanan_clinic_language_url(hanan_clinic_language()); ?>"><?php echo esc_html(hanan_clinic_text('home')); ?></a>
        </article>
    </div>
</main>

<?php get_footer(); ?>
