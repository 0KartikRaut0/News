<?php
/**
 * The main template file
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container">
    <?php if ( is_home() && ! is_paged() ) : ?>
        <?php
        $featured_posts = neonews_show_featured_carousel() ? neonews_get_featured_posts( 5 ) : null;
        if ( $featured_posts && $featured_posts->have_posts() ) :
        ?>
        <section class="nn-featured-carousel" aria-label="<?php esc_attr_e( 'Featured Posts', 'neonews' ); ?>">
            <div class="nn-carousel-track">
                <?php
                while ( $featured_posts->have_posts() ) :
                    $featured_posts->the_post();
                    ?>
                    <article class="nn-carousel-slide">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'neonews-featured', array( 'class' => 'nn-carousel-image', 'loading' => 'eager' ) ); ?>
                        <?php else : ?>
                            <div class="nn-carousel-image nn-post-thumbnail-placeholder">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="nn-carousel-overlay">
                            <?php
                            $categories = get_the_category();
                            if ( ! empty( $categories ) ) :
                            ?>
                                <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="nn-carousel-category">
                                    <?php echo esc_html( $categories[0]->name ); ?>
                                </a>
                            <?php endif; ?>
                            <h2 class="nn-carousel-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="nn-carousel-meta">
                                <span><?php echo esc_html( get_the_date() ); ?></span>
                                <span> • </span>
                                <span><?php echo esc_html( neonews_get_reading_time() ); ?> <?php esc_html_e( 'min read', 'neonews' ); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
            <button type="button" class="nn-carousel-nav nn-carousel-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'neonews' ); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button type="button" class="nn-carousel-nav nn-carousel-next" aria-label="<?php esc_attr_e( 'Next slide', 'neonews' ); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            <div class="nn-carousel-dots" role="tablist">
                <?php for ( $i = 0; $i < $featured_posts->post_count; $i++ ) : ?>
                    <button type="button" class="nn-carousel-dot<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'neonews' ), $i + 1 ); ?>" role="tab" <?php echo 0 === $i ? 'aria-selected="true"' : ''; ?>></button>
                <?php endfor; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'homepage-1' ) ) : ?>
            <section class="nn-homepage-section">
                <?php dynamic_sidebar( 'homepage-1' ); ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <div class="nn-row">
        <div class="nn-main-content nn-col">
            <?php if ( ! is_home() || is_paged() ) : ?>
                <header class="nn-section-header">
                    <h1 class="nn-section-title"><?php esc_html_e( 'Latest News', 'neonews' ); ?></h1>
                </header>
            <?php else : ?>
                <header class="nn-section-header">
                    <h2 class="nn-section-title"><?php esc_html_e( 'Latest News', 'neonews' ); ?></h2>
                    <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="nn-section-link">
                        <?php esc_html_e( 'View All', 'neonews' ); ?> →
                    </a>
                </header>
            <?php endif; ?>

            <?php if ( have_posts() ) : ?>
                <div class="nn-post-grid">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        get_template_part( 'template-parts/content', 'card' );
                    endwhile;
                    ?>
                </div>

                <?php neonews_pagination(); ?>

            <?php else : ?>
                <div class="nn-no-posts">
                    <p><?php esc_html_e( 'No posts found.', 'neonews' ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'homepage-2' ) ) : ?>
                <section class="nn-homepage-section nn-mt-3">
                    <?php dynamic_sidebar( 'homepage-2' ); ?>
                </section>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'homepage-3' ) ) : ?>
                <section class="nn-homepage-section nn-mt-3">
                    <?php dynamic_sidebar( 'homepage-3' ); ?>
                </section>
            <?php endif; ?>
        </div>

        <aside class="nn-sidebar nn-col">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>

<?php
get_footer();
