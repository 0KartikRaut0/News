<?php
/**
 * Homepage helpers — category blocks, meta lines, YouTube.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output category badge for current post in loop.
 *
 * @param string $class Extra CSS class.
 */
function neonews_the_category_badge( $class = 'nn-post-category' ) {
    $categories = get_the_category();
    if ( empty( $categories ) ) {
        return;
    }
    $cat = $categories[0];
    printf(
        '<a href="%1$s" class="%2$s nn-cat-%3$s">%4$s</a>',
        esc_url( get_category_link( $cat->term_id ) ),
        esc_attr( $class ),
        esc_attr( $cat->slug ),
        esc_html( $cat->name )
    );
}

/**
 * Compact post meta line (author, date, read time, views, comments).
 *
 * @param int|null $post_id Post ID.
 * @param bool     $light   Light text for dark overlays.
 */
function neonews_the_post_meta_line( $post_id = null, $light = false ) {
    $post_id   = $post_id ? $post_id : get_the_ID();
    $author_id = (int) get_post_field( 'post_author', $post_id );
    $author    = get_the_author_meta( 'display_name', $author_id );
    $class     = $light ? 'nn-meta-line nn-meta-light' : 'nn-meta-line';

    echo '<div class="' . esc_attr( $class ) . '">';
    echo '<span class="nn-meta-author nn-meta-author-with-avatar">';
    echo neonews_get_user_avatar_html( $author_id, 22, 'nn-meta-author-avatar' );
    echo '<span>' . esc_html( sprintf( __( 'By %s', 'neonews' ), $author ) ) . '</span>';
    echo '</span>';
    echo '<span class="nn-meta-sep">·</span>';
    echo '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time>';
    echo '<span class="nn-meta-sep">·</span>';
    echo '<span>' . esc_html( neonews_get_reading_time( $post_id ) ) . ' ' . esc_html__( 'min read', 'neonews' ) . '</span>';
    if ( neonews_is_feature_enabled( 'show_post_views' ) ) {
        echo '<span class="nn-meta-sep">·</span>';
        echo '<span class="nn-meta-views">';
        echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        echo esc_html( neonews_format_views( neonews_get_post_views( $post_id ) ) ) . ' ' . esc_html__( 'views', 'neonews' );
        echo '</span>';
    }
    if ( get_comments_number( $post_id ) ) {
        echo '<span class="nn-meta-sep">·</span>';
        echo '<span class="nn-meta-comments">' . esc_html( number_format_i18n( get_comments_number( $post_id ) ) ) . ' ' . esc_html__( 'comments', 'neonews' ) . '</span>';
    }
    echo '</div>';
}

/**
 * Views badge with eye icon.
 *
 * @param int|null $post_id Post ID.
 */
function neonews_the_views_badge( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    echo '<span class="nn-views-badge">';
    echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    echo esc_html( neonews_format_views( neonews_get_post_views( $post_id ) ) );
    echo '</span>';
}

/**
 * Homepage category accent colors.
 *
 * @param string $slug Category slug.
 * @return string CSS color.
 */
function neonews_category_accent( $slug ) {
    $map = neonews_get_category_color_map();
    return isset( $map[ $slug ] ) ? $map[ $slug ] : '#ff7a18';
}

/**
 * Render a homepage category block (featured + list).
 *
 * @param string $slug Category slug.
 */
function neonews_render_home_category_block( $slug ) {
    $cat = get_category_by_slug( $slug );
    if ( ! $cat ) {
        return;
    }

    $query = new WP_Query( array(
        'cat'            => $cat->term_id,
        'posts_per_page' => absint( neonews_get_setting( 'home_category_posts', 3 ) ),
        'no_found_rows'  => true,
    ) );

    if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        return;
    }

    $accent = neonews_category_accent( $slug );
    ?>
    <section class="nn-home-cat-block nn-reveal" style="--nn-cat-accent: <?php echo esc_attr( $accent ); ?>">
        <header class="nn-home-cat-header">
            <h2 class="nn-home-cat-title"><span class="nn-cat-bar"></span><?php echo esc_html( $cat->name ); ?></h2>
            <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="nn-section-link"><?php esc_html_e( 'See all', 'neonews' ); ?> →</a>
        </header>
        <?php
        $i = 0;
        while ( $query->have_posts() ) :
            $query->the_post();
            if ( 0 === $i ) {
                get_template_part( 'template-parts/home', 'featured' );
            } else {
                get_template_part( 'template-parts/home', 'list-item' );
            }
            $i++;
        endwhile;
        wp_reset_postdata();
        ?>
    </section>
    <?php
}

/**
 * Fallback primary menu when demo not imported yet.
 */
function neonews_fallback_primary_menu() {
    echo '<ul class="nn-nav-menu">';
    echo '<li class="current-menu-item"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'neonews' ) . '</a></li>';

    foreach ( neonews_get_fallback_nav_categories() as $slug ) {
        $cat = get_category_by_slug( $slug );
        if ( $cat ) {
            echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
        }
    }

    foreach ( neonews_get_fallback_nav_pages() as $page_slug ) {
        $page = get_page_by_path( $page_slug );
        if ( $page ) {
            echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
        }
    }

    echo '</ul>';
}

/**
 * Parse YouTube video ID from URL.
 *
 * @param string $url YouTube URL.
 * @return string Video ID or empty.
 */
function neonews_parse_youtube_id( $url ) {
    if ( empty( $url ) ) {
        return '';
    }
    if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
        return $m[1];
    }
    return '';
}

/**
 * Get YouTube URL for post.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_get_youtube_url( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    return (string) get_post_meta( $post_id, '_neonews_youtube_url', true );
}

/**
 * Get YouTube video ID for post.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_get_youtube_id( $post_id = null ) {
    return neonews_parse_youtube_id( neonews_get_youtube_url( $post_id ) );
}

/**
 * YouTube thumbnail URL.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_get_youtube_thumb( $post_id = null ) {
    $id = neonews_get_youtube_id( $post_id );
    if ( ! $id ) {
        return '';
    }
    return 'https://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
}

/**
 * Video duration label.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_get_video_duration( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    return (string) get_post_meta( $post_id, '_neonews_video_duration', true );
}

/**
 * Responsive YouTube embed HTML.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_youtube_embed( $post_id = null ) {
    $id = neonews_get_youtube_id( $post_id );
    if ( ! $id ) {
        return '';
    }
    return sprintf(
        '<div class="nn-youtube-wrap"><iframe src="https://www.youtube.com/embed/%1$s" title="%2$s" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe></div>',
        esc_attr( $id ),
        esc_attr( get_the_title( $post_id ) )
    );
}

/**
 * Homepage newsletter box.
 */
function neonews_home_newsletter() {
    $settings = neonews_get_settings();
    $action   = ! empty( $settings['newsletter_form_action'] ) ? $settings['newsletter_form_action'] : '#';
    $onsubmit = empty( $settings['newsletter_form_action'] ) ? ' onsubmit="return false;"' : '';
    ?>
    <section class="nn-newsletter-box nn-reveal">
        <h3><?php esc_html_e( 'Daily briefing', 'neonews' ); ?></h3>
        <p><?php echo esc_html( $settings['footer_newsletter_text'] ); ?></p>
        <form class="nn-newsletter-form" action="<?php echo esc_url( $action ); ?>" method="post"<?php echo $onsubmit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <label class="nn-visually-hidden" for="nn-newsletter-email"><?php esc_html_e( 'Email', 'neonews' ); ?></label>
            <input type="email" id="nn-newsletter-email" name="email" placeholder="<?php esc_attr_e( 'Email', 'neonews' ); ?>" required />
            <button type="submit" class="nn-btn nn-btn-join"><?php esc_html_e( 'Join', 'neonews' ); ?></button>
        </form>
    </section>
    <?php
}

/**
 * Graphical sidebar — Fresh Reads.
 */
function neonews_render_sidebar_fresh_reads( $limit = null ) {
    if ( ! neonews_is_feature_enabled( 'sidebar_fresh_reads' ) ) {
        return;
    }
    if ( null === $limit ) {
        $limit = absint( neonews_get_setting( 'sidebar_fresh_reads_count', 5 ) );
    }
    $title = neonews_get_setting( 'sidebar_fresh_reads_title', __( 'Fresh Reads', 'neonews' ) );
    $query = neonews_get_trending_posts( $limit );
    ?>
    <div class="nn-widget nn-widget-graphical nn-widget-fresh">
        <h3 class="nn-widget-title"><span class="nn-widget-icon">📰</span> <?php echo esc_html( $title ); ?></h3>
        <?php if ( $query->have_posts() ) : ?>
            <div class="nn-sidebar-cards">
                <?php
                $n = 1;
                while ( $query->have_posts() ) :
                    $query->the_post();
                    $thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                    ?>
                    <a href="<?php the_permalink(); ?>" class="nn-sidebar-card">
                        <span class="nn-sidebar-card-rank"><?php echo esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ); ?></span>
                        <span class="nn-sidebar-card-thumb">
                            <?php if ( $thumb ) : ?>
                                <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
                            <?php else : ?>
                                <span class="nn-sidebar-thumb-fallback"></span>
                            <?php endif; ?>
                        </span>
                        <span class="nn-sidebar-card-body">
                            <span class="nn-sidebar-card-title"><?php the_title(); ?></span>
                            <span class="nn-sidebar-card-meta">
                                <?php echo esc_html( neonews_format_views( neonews_get_post_views() ) ); ?> ·
                                <?php echo esc_html( neonews_get_reading_time() ); ?> <?php esc_html_e( 'min', 'neonews' ); ?>
                            </span>
                        </span>
                    </a>
                    <?php
                    $n++;
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <p class="nn-widget-empty"><?php esc_html_e( 'No posts yet.', 'neonews' ); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Graphical sidebar — Explore Topics.
 */
function neonews_render_sidebar_topics( $limit = 8 ) {
    $categories = get_categories( array(
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => absint( $limit ),
        'hide_empty' => true,
    ) );
    $colors = array( '#ff7a18', '#2563eb', '#16a34a', '#9333ea', '#ca8a04', '#db2777', '#0891b2', '#ea580c' );
    ?>
    <div class="nn-widget nn-widget-graphical nn-widget-topics">
        <h3 class="nn-widget-title"><span class="nn-widget-icon">🏷</span> <?php esc_html_e( 'Explore Topics', 'neonews' ); ?></h3>
        <?php if ( $categories ) : ?>
            <div class="nn-topic-pills">
                <?php foreach ( $categories as $i => $cat ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="nn-topic-pill" style="--nn-pill-color: <?php echo esc_attr( $colors[ $i % count( $colors ) ] ); ?>">
                        <span class="nn-topic-name"><?php echo esc_html( $cat->name ); ?></span>
                        <span class="nn-topic-count"><?php echo esc_html( number_format_i18n( $cat->count ) ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Reader tipline pitch section.
 */
function neonews_home_tipline() {
    $contact = get_page_by_path( 'contact' );
    $url     = $contact ? get_permalink( $contact ) : home_url( '/contact/' );
    ?>
    <section class="nn-tipline nn-reveal">
        <div class="nn-tipline-info">
            <span class="nn-tipline-badge"><?php esc_html_e( 'Reader Tipline', 'neonews' ); ?></span>
            <h2><?php esc_html_e( 'Got a story we should cover?', 'neonews' ); ?></h2>
            <p><?php esc_html_e( 'Send tips, documents, or story ideas directly to our editors.', 'neonews' ); ?></p>
            <ul class="nn-tipline-list">
                <li><?php esc_html_e( 'Read by our editors within 48 hours', 'neonews' ); ?></li>
                <li><?php esc_html_e( 'Anonymity respected on request', 'neonews' ); ?></li>
                <li><?php esc_html_e( 'Get credit when your tip becomes a story', 'neonews' ); ?></li>
            </ul>
        </div>
        <div class="nn-tipline-form-wrap">
            <h3><?php esc_html_e( 'Pitch a story', 'neonews' ); ?></h3>
            <p class="nn-tipline-desc"><?php esc_html_e( 'Share your lead — our editors review every submission.', 'neonews' ); ?></p>
            <div class="nn-tipline-fields nn-tipline-fields-visual">
                <span class="nn-tipline-field"><?php esc_html_e( 'Your name', 'neonews' ); ?></span>
                <span class="nn-tipline-field"><?php esc_html_e( 'Email', 'neonews' ); ?></span>
                <span class="nn-tipline-field nn-tipline-field-lg"><?php esc_html_e( 'Tell us about the story…', 'neonews' ); ?></span>
            </div>
            <a href="<?php echo esc_url( $url ); ?>" class="nn-btn nn-tipline-cta"><?php esc_html_e( 'Send pitch', 'neonews' ); ?></a>
            <p class="nn-tipline-note"><?php esc_html_e( 'Opens our secure contact form.', 'neonews' ); ?></p>
        </div>
    </section>
    <?php
}
