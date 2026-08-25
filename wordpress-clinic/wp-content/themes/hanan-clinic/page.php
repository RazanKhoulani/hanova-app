<?php get_header(); ?>

<main id="main-content" class="content-shell">
    <div class="site-container">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h1><?php the_title(); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
