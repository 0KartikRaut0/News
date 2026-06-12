<?php
/**
 * Template Name: Careers
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
            <p><?php esc_html_e( 'Join a newsroom built for the next generation.', 'neonews' ); ?></p>
        </header>
        <article class="nn-page-card nn-reveal">
            <div class="nn-page-content nn-single-content">
                <?php the_content(); ?>
            </div>
            <?php neonews_render_careers_whatsapp_block(); ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
