<?php
/**
 * Template Name: About Us (Fancy)
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
            <p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
        </header>

        <div class="nn-about-stats nn-reveal">
            <div class="nn-stat-card">
                <span class="nn-stat-num">12M+</span>
                <span class="nn-stat-label"><?php esc_html_e( 'Monthly readers', 'neonews' ); ?></span>
            </div>
            <div class="nn-stat-card">
                <span class="nn-stat-num">40+</span>
                <span class="nn-stat-label"><?php esc_html_e( 'Reporters worldwide', 'neonews' ); ?></span>
            </div>
            <div class="nn-stat-card">
                <span class="nn-stat-num">7</span>
                <span class="nn-stat-label"><?php esc_html_e( 'Beats covered daily', 'neonews' ); ?></span>
            </div>
            <div class="nn-stat-card">
                <span class="nn-stat-num">2014</span>
                <span class="nn-stat-label"><?php esc_html_e( 'Founded', 'neonews' ); ?></span>
            </div>
        </div>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-page-card nn-reveal' ); ?>>
            <div class="nn-page-content nn-single-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
