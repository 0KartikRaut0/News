<?php
/**
 * Template Name: Legal Page
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
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-page-card nn-reveal' ); ?>>
            <header class="nn-page-header">
                <h1 class="nn-page-title"><?php the_title(); ?></h1>
                <p class="nn-legal-updated"><?php printf( esc_html__( 'Last updated: %s', 'neonews' ), esc_html( get_the_modified_date() ) ); ?></p>
            </header>
            <div class="nn-page-content nn-single-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
