<?php
/**
 * The template for displaying archive pages
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="nn-archive-header">
    <div class="nn-container">
        <?php
        the_archive_title( '<h1 class="nn-archive-title">', '</h1>' );
        the_archive_description( '<div class="nn-archive-description">', '</div>' );
        ?>
    </div>
</div>

<?php do_action( 'neonews_archive_after_header' ); ?>

<?php neonews_ad_zone( 'ad-archive', __( '728×90 — Archive listing placement', 'neonews' ) ); ?>

<div class="nn-container">
    <div class="nn-row">
        <div class="nn-main-content nn-col">
            <?php if ( have_posts() ) : ?>
                <div class="nn-post-grid">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        get_template_part( 'template-parts/content', 'card' );
                    endwhile;
                    ?>
                </div>

                <?php neonews_pagination(); ?>

            <?php else : ?>
                <div class="nn-no-posts">
                    <h2><?php esc_html_e( 'Nothing Found', 'neonews' ); ?></h2>
                    <p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for.', 'neonews' ); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <aside class="nn-sidebar nn-col">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>

<?php
get_footer();
