<?php
/**
 * Template Name: Advertise
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container nn-page-fancy">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="nn-page-hero-fancy nn-reveal">
            <h1><?php the_title(); ?></h1>
            <p><?php esc_html_e( 'Reach millions of engaged readers.', 'neonews' ); ?></p>
        </header>
        <div class="nn-ad-packages nn-reveal">
            <div class="nn-page-card">
                <h3><?php esc_html_e( 'Display Ads', 'neonews' ); ?></h3>
                <p><?php esc_html_e( 'Header banners, in-content slots, and mobile sticky placements.', 'neonews' ); ?></p>
            </div>
            <div class="nn-page-card">
                <h3><?php esc_html_e( 'Sponsored Stories', 'neonews' ); ?></h3>
                <p><?php esc_html_e( 'Native content crafted with our editorial team.', 'neonews' ); ?></p>
            </div>
            <div class="nn-page-card">
                <h3><?php esc_html_e( 'Newsletter', 'neonews' ); ?></h3>
                <p><?php esc_html_e( 'Premium placement in our daily digest.', 'neonews' ); ?></p>
            </div>
        </div>
        <article class="nn-page-card nn-reveal">
            <div class="nn-page-content nn-single-content">
                <?php the_content(); ?>
            </div>
            <?php neonews_render_advertise_whatsapp_block(); ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
