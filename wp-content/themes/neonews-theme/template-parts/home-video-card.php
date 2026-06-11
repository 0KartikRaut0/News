<?php
/**
 * Video card for homepage Watch section.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$yt_id    = neonews_get_youtube_id();
$yt_thumb = neonews_get_youtube_thumb();
$duration = neonews_get_video_duration();
$is_feat  = ! empty( $args['featured'] );
$classes  = 'nn-watch-card' . ( $is_feat ? ' nn-watch-featured' : '' );
$gradients = array(
    'linear-gradient(135deg, #6366f1, #3b82f6)',
    'linear-gradient(135deg, #14b8a6, #22c55e)',
    'linear-gradient(135deg, #f472b6, #fb923c)',
    'linear-gradient(135deg, #a855f7, #6366f1)',
    'linear-gradient(135deg, #0ea5e9, #6366f1)',
);
$grad = $gradients[ get_the_ID() % count( $gradients ) ];
?>
<a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( $classes ); ?>" style="--nn-watch-grad: <?php echo esc_attr( $grad ); ?>">
    <span class="nn-watch-media">
        <?php if ( $yt_thumb ) : ?>
            <img src="<?php echo esc_url( $yt_thumb ); ?>" alt="" loading="lazy" />
        <?php elseif ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'neonews-card', array( 'loading' => 'lazy' ) ); ?>
        <?php else : ?>
            <span class="nn-watch-gradient"></span>
        <?php endif; ?>
        <span class="nn-watch-play" aria-hidden="true"><span>▶</span></span>
        <?php if ( $duration ) : ?>
            <span class="nn-watch-duration"><?php echo esc_html( $duration ); ?></span>
        <?php endif; ?>
    </span>
    <span class="nn-watch-body">
        <?php
        $cats = get_the_category();
        if ( ! empty( $cats ) ) :
        ?>
            <span class="nn-watch-cat">• <?php echo esc_html( strtoupper( $cats[0]->name ) ); ?></span>
        <?php endif; ?>
        <span class="nn-watch-title"><?php the_title(); ?></span>
        <span class="nn-watch-views">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <?php echo esc_html( neonews_format_views( neonews_get_post_views() ) ); ?> <?php esc_html_e( 'views', 'neonews' ); ?>
        </span>
    </span>
</a>
