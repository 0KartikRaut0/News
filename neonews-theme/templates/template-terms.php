<?php
/**
 * Template Name: Terms of Service
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container nn-page-fancy nn-legal-page nn-legal-terms">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="nn-legal-hero nn-reveal">
            <span class="nn-legal-icon">📜</span>
            <h1><?php the_title(); ?></h1>
            <p class="nn-legal-updated"><?php printf( esc_html__( 'Last updated: %s', 'neonews' ), esc_html( get_the_modified_date() ) ); ?></p>
        </header>
        <article class="nn-page-card nn-reveal">
            <div class="nn-page-content nn-single-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
