<?php
/**
 * The template for displaying pages
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-container">
    <div class="nn-row">
        <div class="nn-main-content nn-col">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-page' ); ?>>
                    <header class="nn-page-header">
                        <h1 class="nn-page-title"><?php the_title(); ?></h1>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure class="nn-page-featured">
                            <?php the_post_thumbnail( 'neonews-featured' ); ?>
                        </figure>
                    <?php endif; ?>

                    <div class="nn-page-content nn-single-content">
                        <?php
                        the_content();

                        wp_link_pages( array(
                            'before' => '<div class="nn-page-links">' . esc_html__( 'Pages:', 'neonews' ),
                            'after'  => '</div>',
                        ) );
                        ?>
                    </div>

                    <?php
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
                    ?>
                </article>
                <?php
            endwhile;
            ?>
        </div>

        <?php if ( ! is_page_template( array(
            'templates/template-fullwidth.php',
            'templates/template-about.php',
            'templates/template-contact.php',
            'templates/template-account.php',
            'templates/template-profile.php',
            'templates/template-terms.php',
            'templates/template-cookies.php',
            'templates/template-cache.php',
            'templates/template-legal.php',
            'templates/template-careers.php',
            'templates/template-advertise.php',
            'templates/template-exclusive.php',
        ) ) ) : ?>
            <aside class="nn-sidebar nn-col">
                <?php get_sidebar(); ?>
            </aside>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
