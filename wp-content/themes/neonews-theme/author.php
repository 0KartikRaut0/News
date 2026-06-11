<?php
/**
 * The template for displaying author archive pages
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$author_id = get_queried_object_id();
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_bio = get_the_author_meta( 'description', $author_id );
$author_posts_count = count_user_posts( $author_id, 'post', true );
?>

<div class="nn-author-header">
    <div class="nn-container">
        <div class="nn-author-header-inner">
            <div class="nn-author-header-avatar">
                <?php echo neonews_get_user_avatar_html( $author_id, 120, 'nn-author-header-avatar-img' ); ?>
            </div>
            <div class="nn-author-header-content">
                <h1 class="nn-author-header-name"><?php echo esc_html( $author_name ); ?></h1>
                <?php if ( $author_bio ) : ?>
                    <p class="nn-author-header-bio"><?php echo esc_html( $author_bio ); ?></p>
                <?php endif; ?>
                <div class="nn-author-stats">
                    <div class="nn-author-stat">
                        <span class="nn-author-stat-number"><?php echo esc_html( number_format_i18n( $author_posts_count ) ); ?></span>
                        <span class="nn-author-stat-label"><?php esc_html_e( 'Articles', 'neonews' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'neonews_archive_after_header' ); ?>

<div class="nn-container">
    <div class="nn-row">
        <div class="nn-main-content nn-col">
            <header class="nn-section-header">
                <h2 class="nn-section-title">
                    <?php
                    printf(
                        /* translators: %s: author name */
                        esc_html__( 'Articles by %s', 'neonews' ),
                        esc_html( $author_name )
                    );
                    ?>
                </h2>
            </header>

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
                    <p><?php esc_html_e( 'This author has not published any articles yet.', 'neonews' ); ?></p>
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
