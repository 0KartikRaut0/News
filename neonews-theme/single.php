<?php
/**
 * The template for displaying single posts
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-single-post' ); ?>>
    <div class="nn-container">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <header class="nn-single-header">
                <?php
                $categories = get_the_category();
                if ( ! empty( $categories ) ) :
                ?>
                    <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="nn-single-category">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                <?php endif; ?>

                <h1 class="nn-single-title"><?php the_title(); ?></h1>

                <div class="nn-single-meta">
                    <div class="nn-single-author">
                        <?php echo neonews_get_user_avatar_html( get_the_author_meta( 'ID' ), 40 ); ?>
                        <span>
                            <?php esc_html_e( 'By', 'neonews' ); ?>
                            <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                                <?php the_author(); ?>
                            </a>
                        </span>
                    </div>
                    <span class="nn-post-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            <?php echo esc_html( get_the_date() ); ?>
                        </time>
                    </span>
                    <span class="nn-post-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?php echo esc_html( neonews_get_reading_time() ); ?> <?php esc_html_e( 'min read', 'neonews' ); ?>
                    </span>
                    <span class="nn-post-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <?php echo esc_html( neonews_format_views( neonews_get_post_views() ) ); ?> <?php esc_html_e( 'views', 'neonews' ); ?>
                    </span>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="nn-single-featured">
                    <?php the_post_thumbnail( 'neonews-featured', array( 'loading' => 'eager' ) ); ?>
                    <?php
                    $caption = get_the_post_thumbnail_caption();
                    if ( $caption ) :
                    ?>
                        <figcaption><?php echo esc_html( $caption ); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <?php
            $youtube_embed = neonews_youtube_embed( get_the_ID() );
            if ( $youtube_embed ) {
                echo $youtube_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
            }
            ?>

            <div class="nn-single-engage nn-reveal">
                <?php neonews_post_engagement_bar( get_the_ID() ); ?>
            </div>

            <?php do_action( 'neonews_single_before_content' ); ?>

            <div class="nn-single-content">
                <?php
                the_content();

                wp_link_pages( array(
                    'before' => '<div class="nn-page-links">' . esc_html__( 'Pages:', 'neonews' ),
                    'after'  => '</div>',
                ) );
                ?>
            </div>

            <?php
            $tags = get_the_tags();
            if ( $tags ) :
            ?>
                <div class="nn-single-tags">
                    <?php foreach ( $tags as $tag ) : ?>
                        <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="nn-tag">
                            #<?php echo esc_html( $tag->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php neonews_ad_zone( 'ad-single', __( 'In-article sponsor — Single post placement', 'neonews' ) ); ?>

            <div class="nn-author-box">
                <div class="nn-author-avatar">
                    <?php echo neonews_get_user_avatar_html( get_the_author_meta( 'ID' ), 80 ); ?>
                </div>
                <div class="nn-author-info">
                    <h4 class="nn-author-name">
                        <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                            <?php the_author(); ?>
                        </a>
                    </h4>
                    <?php if ( get_the_author_meta( 'description' ) ) : ?>
                        <p class="nn-author-bio"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="nn-btn nn-btn-outline">
                        <?php esc_html_e( 'View all posts', 'neonews' ); ?>
                    </a>
                </div>
            </div>

            <?php
            $related_args = array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'post__not_in'   => array( get_the_ID() ),
                'category__in'   => wp_get_post_categories( get_the_ID() ),
                'orderby'        => 'rand',
                'no_found_rows'  => true,
            );
            $related_args = apply_filters( 'neonews_related_posts_query_args', $related_args, get_the_ID() );
            $related_posts = new WP_Query( $related_args );
            
            if ( $related_posts->have_posts() ) :
            ?>
                <section class="nn-related-posts">
                    <h3 class="nn-section-title"><?php esc_html_e( 'Related Articles', 'neonews' ); ?></h3>
                    <div class="nn-post-grid">
                        <?php
                        while ( $related_posts->have_posts() ) :
                            $related_posts->the_post();
                            get_template_part( 'template-parts/content', 'card' );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>
    </div>
</article>

<?php
get_footer();
