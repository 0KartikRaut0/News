<?php
/**
 * Front page — NewsPulse landing (desktop + mobile)
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$settings       = neonews_get_settings();
$carousel_count = absint( $settings['home_carousel_count'] );
$video_count    = absint( $settings['home_video_count'] );
$latest_count   = absint( $settings['home_latest_count'] );
$mid_ad_after   = absint( $settings['home_mid_ad_after_cat'] );
$home_cats      = neonews_get_home_categories();

$featured_query = neonews_show_featured_carousel() ? neonews_get_featured_posts( $carousel_count ) : null;
$video_query    = neonews_is_feature_enabled( 'show_home_videos' ) ? neonews_get_video_posts( $video_count ) : null;
?>

<div class="nn-home nn-home-pulse">
    <?php neonews_render_home_banner(); ?>

    <?php if ( neonews_is_feature_enabled( 'show_home_hero' ) ) : ?>
    <section class="nn-hero-pulse nn-container nn-reveal">
        <h1 class="nn-hero-headline">
            <?php echo esc_html( $settings['home_hero_title'] ); ?>
            <?php if ( ! empty( $settings['home_hero_title_accent'] ) ) : ?>
                <span class="accent"><?php echo esc_html( $settings['home_hero_title_accent'] ); ?></span>
            <?php endif; ?>
        </h1>
        <?php if ( ! empty( $settings['home_hero_subtitle'] ) ) : ?>
            <p class="nn-hero-sub"><?php echo esc_html( $settings['home_hero_subtitle'] ); ?></p>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ( $featured_query && $featured_query->have_posts() ) : ?>
    <section class="nn-container nn-hero-carousel-wrap nn-reveal">
        <div class="nn-featured-carousel nn-carousel-modern nn-carousel-hero">
            <div class="nn-carousel-track">
                <?php
                $slide_i = 0;
                while ( $featured_query->have_posts() ) :
                    $featured_query->the_post();
                    ?>
                    <article class="nn-carousel-slide">
                        <?php
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'neonews-featured', array( 'class' => 'nn-carousel-image' ) );
                        } else {
                            echo '<div class="nn-carousel-image nn-hero-placeholder"></div>';
                        }
                        ?>
                        <div class="nn-carousel-overlay nn-carousel-overlay-rich">
                            <?php neonews_the_category_badge( 'nn-carousel-category' ); ?>
                            <h2 class="nn-carousel-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <?php neonews_the_post_meta_line( get_the_ID(), true ); ?>
                        </div>
                    </article>
                    <?php
                    $slide_i++;
                endwhile;
                ?>
            </div>
            <?php if ( $slide_i > 1 ) : ?>
                <button type="button" class="nn-carousel-nav nn-carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'neonews' ); ?>">‹</button>
                <button type="button" class="nn-carousel-nav nn-carousel-next" aria-label="<?php esc_attr_e( 'Next', 'neonews' ); ?>">›</button>
                <div class="nn-carousel-dots">
                    <?php for ( $d = 0; $d < $slide_i; $d++ ) : ?>
                        <button type="button" class="nn-carousel-dot<?php echo 0 === $d ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $d ); ?>"></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php wp_reset_postdata(); endif; ?>

    <?php do_action( 'neonews_home_after_hero' ); ?>

    <div class="nn-container nn-ad-row nn-reveal">
        <?php neonews_ad_zone( 'ad-home-hero', __( '728×90 — Premium homepage placement', 'neonews' ) ); ?>
    </div>

    <div class="nn-container nn-home-layout nn-reveal">
        <div class="nn-home-main">
            <?php if ( neonews_is_feature_enabled( 'show_home_categories' ) && ! empty( $home_cats ) ) : ?>
            <div class="nn-home-cat-grid">
                <?php
                $cat_i = 0;
                foreach ( $home_cats as $cat_slug ) :
                    neonews_render_home_category_block( $cat_slug );
                    $cat_i++;
                    if ( $mid_ad_after > 0 && $cat_i === $mid_ad_after ) :
                        ?>
                        <div class="nn-ad-row nn-ad-row-full">
                            <?php neonews_ad_zone( 'ad-home-mid', __( 'Mid-page banner — high visibility', 'neonews' ) ); ?>
                        </div>
                        <?php
                    endif;
                endforeach;
                ?>
            </div>
            <?php endif; ?>

            <div class="nn-ad-row">
                <?php neonews_ad_zone( 'ad-content', __( 'Sponsored content — Advertise with us', 'neonews' ) ); ?>
            </div>

            <?php if ( $video_query && $video_query->have_posts() ) : ?>
            <div class="nn-ad-row">
                <?php neonews_ad_zone( 'ad-home-videos', __( 'Video section sponsor', 'neonews' ) ); ?>
            </div>
            <section class="nn-watch-section nn-reveal">
                <header class="nn-section-header">
                    <h2 class="nn-section-title"><?php echo esc_html( $settings['home_video_title'] ); ?></h2>
                    <?php if ( ! empty( $settings['home_video_subtitle'] ) ) : ?>
                        <span class="nn-section-sub"><?php echo esc_html( $settings['home_video_subtitle'] ); ?></span>
                    <?php endif; ?>
                </header>
                <div class="nn-watch-grid">
                    <?php
                    $v = 0;
                    while ( $video_query->have_posts() ) :
                        $video_query->the_post();
                        get_template_part( 'template-parts/home', 'video-card', array( 'featured' => 0 === $v ) );
                        $v++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ( neonews_is_feature_enabled( 'show_home_tipline' ) ) : ?>
                <?php neonews_home_tipline(); ?>
            <?php endif; ?>

            <?php if ( neonews_is_feature_enabled( 'show_home_latest' ) ) : ?>
            <section class="nn-latest-compact nn-reveal">
                <header class="nn-section-header">
                    <h2 class="nn-section-title"><?php esc_html_e( 'Latest', 'neonews' ); ?></h2>
                    <?php $blog_id = get_option( 'page_for_posts' ); ?>
                    <?php if ( $blog_id ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $blog_id ) ); ?>" class="nn-section-link"><?php esc_html_e( 'All news', 'neonews' ); ?> →</a>
                    <?php endif; ?>
                </header>
                <div class="nn-post-grid nn-post-grid-compact">
                    <?php
                    $latest = new WP_Query(
                        array(
                            'post_type'      => 'post',
                            'posts_per_page' => $latest_count,
                            'no_found_rows'  => true,
                        )
                    );
                    while ( $latest->have_posts() ) :
                        $latest->the_post();
                        get_template_part( 'template-parts/content', 'card' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
            <?php endif; ?>

            <div class="nn-ad-row">
                <?php neonews_ad_zone( 'ad-home-footer', __( 'Footer banner — Homepage exclusive', 'neonews' ) ); ?>
            </div>
        </div>

        <aside class="nn-home-rail nn-sidebar-rich nn-reveal">
            <?php neonews_ad_zone( 'ad-home-sidebar', __( '300×600 — Sidebar placement', 'neonews' ) ); ?>
            <?php get_sidebar(); ?>
            <?php if ( neonews_is_feature_enabled( 'show_home_newsletter' ) ) : ?>
                <?php neonews_home_newsletter(); ?>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
