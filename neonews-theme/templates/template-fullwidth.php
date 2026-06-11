<?php
/**
 * Template Name: Full Width
 * Template Post Type: page
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-fullwidth-page">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-page' ); ?>>
            <?php if ( ! is_front_page() ) : ?>
                <header class="nn-page-header nn-container">
                    <h1 class="nn-page-title"><?php the_title(); ?></h1>
                </header>
            <?php endif; ?>

            <?php if ( has_post_thumbnail() && ! is_front_page() ) : ?>
                <figure class="nn-page-featured nn-container">
                    <?php the_post_thumbnail( 'neonews-featured' ); ?>
                </figure>
            <?php endif; ?>

            <div class="nn-page-content nn-single-content">
                <?php
                the_content();

                wp_link_pages( array(
                    'before' => '<div class="nn-page-links nn-container">' . esc_html__( 'Pages:', 'neonews' ),
                    'after'  => '</div>',
                ) );
                ?>
            </div>

            <?php
            if ( comments_open() || get_comments_number() ) :
                ?>
                <div class="nn-container">
                    <?php comments_template(); ?>
                </div>
                <?php
            endif;
            ?>
        </article>
        <?php
    endwhile;
    ?>
</div>

<?php
get_footer();
