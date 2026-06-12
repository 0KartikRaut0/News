<?php
/**
 * The template for displaying 404 pages
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container">
    <div class="nn-404-content" style="text-align: center; padding: 4rem 0;">
        <h1 style="font-size: 6rem; margin-bottom: 1rem; color: var(--nn-primary);">404</h1>
        <h2><?php esc_html_e( 'Page Not Found', 'neonews' ); ?></h2>
        <p style="color: var(--nn-text-secondary); margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">
            <?php esc_html_e( 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'neonews' ); ?>
        </p>

        <form role="search" method="get" class="nn-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="max-width: 400px; margin: 0 auto 2rem;">
            <div class="nn-form-group">
                <input type="search" class="nn-form-input" placeholder="<?php esc_attr_e( 'Search...', 'neonews' ); ?>" value="" name="s" />
            </div>
            <button type="submit" class="nn-btn" style="width: 100%; margin-top: 1rem;"><?php esc_html_e( 'Search', 'neonews' ); ?></button>
        </form>

        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nn-btn nn-btn-secondary">
            <?php esc_html_e( 'Back to Homepage', 'neonews' ); ?>
        </a>
    </div>

    <div class="nn-404-suggestions">
        <h3 class="nn-section-title nn-text-center"><?php esc_html_e( 'Popular Articles', 'neonews' ); ?></h3>
        
        <?php
        $popular_args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'meta_key'       => '_neonews_post_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        );
        $popular_posts = new WP_Query( $popular_args );

        if ( $popular_posts->have_posts() ) :
        ?>
            <div class="nn-post-grid">
                <?php
                while ( $popular_posts->have_posts() ) :
                    $popular_posts->the_post();
                    get_template_part( 'template-parts/content', 'card' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <?php
            $recent_args = array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 4,
                'no_found_rows'  => true,
            );
            $recent_posts = new WP_Query( $recent_args );

            if ( $recent_posts->have_posts() ) :
            ?>
                <div class="nn-post-grid">
                    <?php
                    while ( $recent_posts->have_posts() ) :
                        $recent_posts->the_post();
                        get_template_part( 'template-parts/content', 'card' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
