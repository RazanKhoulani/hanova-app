<?php get_header(); ?>

<main id="main-content" class="content-shell">
    <div class="site-container">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?>>
                <span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('articles_kicker')); ?></span>
                <h1><?php the_title(); ?></h1>
                <div class="entry-meta"><?php echo esc_html(get_the_date('d M Y')); ?></div>
                <?php if (has_post_thumbnail()) : ?><div class="article-image mb-5"><?php the_post_thumbnail('large'); ?></div><?php endif; ?>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
