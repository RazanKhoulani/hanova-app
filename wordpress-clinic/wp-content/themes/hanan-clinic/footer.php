<footer class="site-footer">
    <div class="site-container footer-grid">
        <div class="footer-brand-wrap">
            <a class="clinic-brand footer-brand" href="<?php echo hanan_clinic_language_url(hanan_clinic_language()); ?>">
                <span class="brand-symbol" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="brand-words">
                    <strong><?php echo esc_html(hanan_clinic_text('brand')); ?></strong>
                    <small><?php echo esc_html(hanan_clinic_text('brand_tag')); ?></small>
                </span>
            </a>
            <p><?php echo esc_html(hanan_clinic_text('footer_text')); ?></p>
        </div>

        <div class="footer-links">
            <strong><?php echo esc_html(hanan_clinic_text('services')); ?></strong>
            <a href="<?php echo hanan_clinic_section_url('services'); ?>"><?php echo esc_html(hanan_clinic_text('service_skin_title')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('services'); ?>"><?php echo esc_html(hanan_clinic_text('service_injection_title')); ?></a>
            <a href="<?php echo hanan_clinic_section_url('services'); ?>"><?php echo esc_html(hanan_clinic_text('service_consult_title')); ?></a>
        </div>

        <div class="footer-links">
            <strong><?php echo esc_html(hanan_clinic_text('contact')); ?></strong>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', get_theme_mod('hanan_phone', '+963900000000'))); ?>"><?php echo esc_html(get_theme_mod('hanan_phone', '+963 900 000 000')); ?></a>
            <a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D+/', '', get_theme_mod('hanan_whatsapp', '963900000000'))); ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a href="<?php echo esc_url(get_theme_mod('hanan_instagram_url', '#')); ?>" target="_blank" rel="noopener">Instagram</a>
        </div>

        <div class="footer-hours">
            <strong><?php echo esc_html(hanan_clinic_text('hours')); ?></strong>
            <p><?php echo esc_html(hanan_clinic_text('hours_value')); ?></p>
            <a class="text-link" href="<?php echo hanan_clinic_section_url('contact'); ?>"><?php echo esc_html(hanan_clinic_text('address_value')); ?></a>
        </div>
    </div>

    <div class="site-container footer-bottom">
        <span>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(hanan_clinic_text('brand')); ?>. <?php echo esc_html(hanan_clinic_text('rights')); ?></span>
        <a href="<?php echo esc_url(get_privacy_policy_url() ?: '#'); ?>"><?php echo esc_html(hanan_clinic_text('privacy')); ?></a>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
