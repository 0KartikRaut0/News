<?php
/**
 * Template Name: Cookies Policy
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container nn-page-fancy nn-legal-page nn-legal-cookies">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="nn-legal-hero nn-reveal">
            <span class="nn-legal-icon">🍪</span>
            <h1><?php the_title(); ?></h1>
            <p class="nn-legal-updated"><?php printf( esc_html__( 'Last updated: %s', 'neonews' ), esc_html( get_the_modified_date() ) ); ?></p>
        </header>
        <article class="nn-page-card nn-reveal">
            <div class="nn-page-content nn-single-content"><?php the_content(); ?></div>
            <?php if ( neonews_cookie_banner_enabled() ) : ?>
                <div class="nn-legal-cookie-cta">
                    <p><?php esc_html_e( 'Manage your cookie preferences:', 'neonews' ); ?></p>
                    <button type="button" class="nn-btn nn-cookie-reopen"><?php esc_html_e( 'Open cookie banner', 'neonews' ); ?></button>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
