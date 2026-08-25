<?php get_header(); ?>

<main id="main-content" class="content-shell">
    <div class="site-container">
        <article>
            <span class="eyebrow"><i></i><?php echo esc_html(hanan_clinic_text('articles_kicker')); ?></span>
            <h1><?php echo esc_html(hanan_clinic_text('articles')); ?></h1>

            <?php if (have_posts()) : ?>
                <div class="articles-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="article-card">
                            <a class="article-image" href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } else { echo '<span class="article-placeholder"></span>'; } ?>
                            </a>
                            <div class="article-meta"><span><?php echo esc_html(get_the_date('d M Y')); ?></span><span>Journal</span></div>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?></p>
                            <a class="text-link arrow-link" href="<?php the_permalink(); ?>"><?php echo esc_html(hanan_clinic_text('read_article')); ?><span>↗</span></a>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php the_posts_pagination(); ?>
            <?php endif; ?>
        </article>
    </div>
</main>

<?php get_footer(); ?>
