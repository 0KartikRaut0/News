<article class="nn-home-featured">
    <a href="<?php the_permalink(); ?>" class="nn-home-featured-link">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'neonews-card', array( 'loading' => 'lazy' ) ); ?>
        <?php else : ?>
            <div class="nn-home-thumb-placeholder"></div>
        <?php endif; ?>
    </a>
    <div class="nn-home-featured-body">
        <?php neonews_the_category_badge( 'nn-post-category nn-cat-sm' ); ?>
        <h3 class="nn-home-featured-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="nn-home-featured-meta">
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
            <?php neonews_the_views_badge(); ?>
        </div>
    </div>
</article>
