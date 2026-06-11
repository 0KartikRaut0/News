<?php
/**
 * The template for displaying search results
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
        <h1 class="nn-archive-title">
            <?php
            printf(
                /* translators: %s: search query */
                esc_html__( 'Search Results for: %s', 'neonews' ),
                '<span>' . esc_html( get_search_query() ) . '</span>'
            );
            ?>
        </h1>
        <div class="nn-archive-description">
            <?php
            global $wp_query;
            printf(
                /* translators: %d: number of results */
                esc_html( _n( '%d result found', '%d results found', $wp_query->found_posts, 'neonews' ) ),
                absint( $wp_query->found_posts )
            );
            ?>
        </div>
    </div>
</div>

<?php do_action( 'neonews_archive_after_header' ); ?>

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
                    <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'neonews' ); ?></p>
                    
                    <form role="search" method="get" class="nn-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="nn-form-group">
                            <input type="search" class="nn-form-input" placeholder="<?php esc_attr_e( 'Search...', 'neonews' ); ?>" value="" name="s" />
                        </div>
                        <button type="submit" class="nn-btn"><?php esc_html_e( 'Search', 'neonews' ); ?></button>
                    </form>
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
