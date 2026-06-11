<article class="nn-home-list-item">
    <a href="<?php the_permalink(); ?>" class="nn-home-list-thumb">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy' ) ); ?>
        <?php else : ?>
            <span class="nn-home-thumb-placeholder nn-thumb-sm"></span>
        <?php endif; ?>
    </a>
    <div class="nn-home-list-body">
        <h4 class="nn-home-list-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <div class="nn-home-list-meta">
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
            <?php neonews_the_views_badge(); ?>
        </div>
    </div>
</article>
