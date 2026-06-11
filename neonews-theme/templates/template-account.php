<?php
/**
 * Template Name: My Account
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
        <header class="nn-page-header nn-reveal">
            <h1 class="nn-page-title"><?php the_title(); ?></h1>
        </header>
        <div class="nn-reveal">
            <?php echo do_shortcode( '[neonews_account]' ); ?>
        </div>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
