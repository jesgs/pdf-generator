<?php
/* Start the Loop */
while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="entry-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header><!-- .entry-header -->

        <?php if ( '' !== get_the_post_thumbnail() && ! is_single() ) : ?>
            <div class="post-thumbnail">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'twentyseventeen-featured-image' ); ?>
                </a>
            </div><!-- .post-thumbnail -->
        <?php endif; ?>

        <div class="entry-content">
            <?php the_content(); ?>
        </div><!-- .entry-content -->
    </article><!-- #post-## -->
<?php endwhile; // End of the loop.

