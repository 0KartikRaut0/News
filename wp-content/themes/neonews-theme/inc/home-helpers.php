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
    $comment_count = neonews_get_comment_count( $post_id );
    if ( $comment_count ) {
        echo '<span class="nn-meta-sep">·</span>';
        echo '<span class="nn-meta-comments">' . esc_html( number_format_i18n( $comment_count ) ) . ' ' . esc_html__( 'comments', 'neonews' ) . '</span>';
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
/**
 * @param string|array $slug_or_block Category slug string, or full block config array.
 */
function neonews_render_home_category_block( $slug_or_block ) {
    // Accept both the old string form and the new block config array.
    if ( is_array( $slug_or_block ) ) {
        $block = $slug_or_block;
        $slug  = $block['slug'] ?? '';
    } else {
        $slug  = (string) $slug_or_block;
        $block = array();
    }

    $cat = get_category_by_slug( $slug );
    if ( ! $cat ) {
        return;
    }

    $default_count = absint( neonews_get_setting( 'home_category_posts', 3 ) );
    $count         = ! empty( $block['count'] ) ? absint( $block['count'] ) : $default_count;
    $label         = ! empty( $block['label'] ) ? $block['label'] : $cat->name;
    $accent        = ! empty( $block['color'] ) ? $block['color'] : neonews_category_accent( $slug );
    $see_all       = ! empty( $block['see_all_url'] ) ? $block['see_all_url'] : get_category_link( $cat->term_id );

    $query = new WP_Query( array(
        'cat'            => $cat->term_id,
        'posts_per_page' => $count,
        'post_status'    => 'publish',  // always use only published posts — no private/draft for admins
        'no_found_rows'  => true,
    ) );

    if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        return;
    }
    $layout        = neonews_get_category_block_layout( $block );
    $section_style = neonews_get_category_block_section_style( $block );
    $style_class   = neonews_home_section_style_class( $section_style );
    ?>
    <section class="nn-home-cat-block nn-reveal nn-cat-layout-<?php echo esc_attr( $layout ); ?> <?php echo esc_attr( $style_class ); ?>" style="--nn-cat-accent: <?php echo esc_attr( $accent ); ?>">
        <header class="nn-home-cat-header">
            <h2 class="nn-home-cat-title"><span class="nn-cat-bar"></span><?php echo esc_html( $label ); ?></h2>
            <a href="<?php echo esc_url( $see_all ); ?>" class="nn-section-link"><?php esc_html_e( 'See all', 'neonews' ); ?> →</a>
        </header>
        <?php neonews_render_home_posts_layout( $query, $layout ); ?>
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
    // Use built-in subscriber plugin if active, else fall back to custom action.
    $use_builtin = function_exists( 'NeoNews_Newsletter_Ajax' ) || class_exists( 'NeoNews_Newsletter_Ajax' );
    ?>
    <section class="nn-newsletter-box nn-reveal">
        <h3><?php esc_html_e( 'Daily briefing', 'neonews' ); ?></h3>
        <p><?php echo esc_html( $settings['footer_newsletter_text'] ); ?></p>
        <form class="nn-newsletter-form nn-sub-form" data-nn-subscribe="1" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
            <?php if ( $use_builtin ) : ?>
                <input type="hidden" name="action" value="nn_subscribe">
                <input type="hidden" name="nonce" value=""><!-- filled by JS -->
            <?php endif; ?>
            <label class="nn-visually-hidden" for="nn-newsletter-email"><?php esc_html_e( 'Email', 'neonews' ); ?></label>
            <input type="email" id="nn-newsletter-email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'neonews' ); ?>" required autocomplete="email" />
            <button type="submit" class="nn-btn nn-btn-join"><?php esc_html_e( 'Join', 'neonews' ); ?></button>
        </form>
        <p class="nn-sub-feedback" aria-live="polite" style="display:none;margin-top:10px;font-size:14px;"></p>
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

    // If no trending posts found (e.g. no view data yet or all posts older than 7 days),
    // fall back to the most recent published posts so the widget never shows empty.
    if ( ! $query->have_posts() ) {
        $query = new WP_Query( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ) );
    }
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
    $core_active = class_exists( 'NeoNews_News_Submission' ) && class_exists( 'NeoNews_Core' );
    $submit_url  = $core_active ? NeoNews_News_Submission::get_submit_page_url() : home_url( '/submit/' );
    $state       = $core_active ? NeoNews_News_Submission::get_access_state() : ( is_user_logged_in() ? 'open' : 'guest' );
    $basic_open  = $core_active && NeoNews_Core::get_settings( 'submission_allow_basic' );
    $upgrade_url = $core_active ? NeoNews_News_Submission::get_upgrade_url() : $submit_url;
    $points      = array_filter( array_map( 'trim', explode( "\n", (string) neonews_get_setting( 'tipline_points', '' ) ) ) );
    $locked      = 'open' !== $state;
    $needs_plan  = $locked && ! $basic_open;

    wp_parse_str( (string) wp_parse_url( $submit_url, PHP_URL_QUERY ), $submit_query );
    ?>
    <section class="nn-tipline nn-reveal<?php echo $locked ? ' is-locked' : ''; ?>">
        <div class="nn-tipline-info">
            <span class="nn-tipline-badge"><?php echo esc_html( neonews_get_setting( 'tipline_badge', '' ) ); ?></span>
            <h2><?php echo esc_html( neonews_get_setting( 'tipline_title', '' ) ); ?></h2>
            <p><?php echo esc_html( neonews_get_setting( 'tipline_text', '' ) ); ?></p>
            <?php if ( $points ) : ?>
                <ul class="nn-tipline-list">
                    <?php foreach ( $points as $point ) : ?>
                        <li><?php echo esc_html( $point ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="nn-tipline-form-wrap">
            <div class="nn-tipline-form-head">
                <h3><?php echo esc_html( neonews_get_setting( 'tipline_form_title', '' ) ); ?></h3>
                <?php if ( $needs_plan ) : ?>
                    <span class="nn-tipline-lock-badge"><span aria-hidden="true">🔒</span> <?php esc_html_e( 'Premium', 'neonews' ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( ! $locked ) : ?>
                <p class="nn-tipline-desc"><?php echo esc_html( neonews_get_setting( 'tipline_form_desc', '' ) ); ?></p>
                <form class="nn-tipline-form" method="get" action="<?php echo esc_url( strtok( $submit_url, '?' ) ); ?>">
                    <?php foreach ( $submit_query as $q_key => $q_value ) : ?>
                        <input type="hidden" name="<?php echo esc_attr( $q_key ); ?>" value="<?php echo esc_attr( $q_value ); ?>" />
                    <?php endforeach; ?>
                    <label class="screen-reader-text" for="nn-pitch-title"><?php esc_html_e( 'Story headline', 'neonews' ); ?></label>
                    <input type="text" id="nn-pitch-title" name="pitch_title" class="nn-tipline-input" maxlength="200" required placeholder="<?php esc_attr_e( 'Your story headline…', 'neonews' ); ?>" />
                    <button type="submit" class="nn-btn nn-tipline-cta"><?php echo esc_html( neonews_get_setting( 'tipline_cta_open', '' ) ); ?> →</button>
                </form>
                <p class="nn-tipline-note"><?php esc_html_e( 'Our editors review every story before it goes live.', 'neonews' ); ?></p>
            <?php else : ?>
                <div class="nn-tipline-locked-box">
                    <div class="nn-tipline-fields nn-tipline-fields-visual" aria-hidden="true">
                        <span class="nn-tipline-field"><?php esc_html_e( 'Your story headline…', 'neonews' ); ?></span>
                        <span class="nn-tipline-field nn-tipline-field-lg"><?php esc_html_e( 'Tell us about the story…', 'neonews' ); ?></span>
                    </div>
                    <div class="nn-tipline-lock-overlay">
                        <span class="nn-tipline-lock-icon" aria-hidden="true">🔒</span>
                        <p>
                            <?php
                            if ( $needs_plan ) {
                                echo esc_html( neonews_get_setting( 'tipline_locked_text', '' ) );
                            } else {
                                esc_html_e( 'Sign in to pitch your story.', 'neonews' );
                            }
                            ?>
                        </p>
                    </div>
                </div>
                <div class="nn-tipline-actions">
                    <?php if ( 'guest' === $state ) : ?>
                        <a href="<?php echo esc_url( wp_login_url( $submit_url ) ); ?>" class="nn-btn nn-tipline-cta nn-open-login"><?php esc_html_e( 'Sign in to pitch', 'neonews' ); ?></a>
                    <?php endif; ?>
                    <?php if ( $needs_plan ) : ?>
                        <a href="<?php echo esc_url( $upgrade_url ); ?>" class="nn-btn nn-tipline-cta<?php echo 'guest' === $state ? ' nn-btn-outline' : ''; ?>"><?php echo esc_html( neonews_get_setting( 'tipline_cta_locked', '' ) ); ?></a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $submit_url ); ?>" class="nn-tipline-how"><?php esc_html_e( 'How story submissions work', 'neonews' ); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Render a custom homepage section (template, posts feed, page, or legacy tag/html).
 *
 * @param array $cs Custom section config.
 */
function neonews_render_custom_home_section( $cs ) {
    if ( empty( $cs['id'] ) || empty( $cs['type'] ) ) {
        return;
    }

    $type = $cs['type'];

    if ( 'template' === $type ) {
        neonews_render_home_custom_template( $cs );
        return;
    }

    if ( 'posts' === $type || 'tag' === $type ) {
        if ( 'tag' === $type && empty( $cs['source'] ) ) {
            $cs['source'] = 'tag';
        }
        neonews_render_custom_home_posts_feed( $cs );
        return;
    }

    $title = ! empty( $cs['title'] ) ? $cs['title'] : '';

    if ( 'html' === $type ) {
        if ( empty( $cs['html'] ) ) {
            return;
        }
        $style_class = ! empty( $cs['section_style'] ) ? neonews_home_section_style_class( $cs['section_style'] ) : 'nn-section-style-default';
        echo '<section class="nn-custom-section nn-custom-section-html nn-reveal ' . esc_attr( $style_class ) . '">';
        if ( $title ) {
            echo '<header class="nn-section-header"><h2 class="nn-section-title">' . esc_html( $title ) . '</h2></header>';
        }
        echo '<div class="nn-custom-html-body">';
        echo wp_kses_post( $cs['html'] );
        echo '</div></section>';
        return;
    }

    if ( 'page' === $type ) {
        $page_id = absint( $cs['page_id'] ?? 0 );
        if ( ! $page_id ) {
            return;
        }
        $page = get_post( $page_id );
        if ( ! $page || 'publish' !== $page->post_status ) {
            return;
        }
        $see_all = ! empty( $cs['see_all_url'] ) ? $cs['see_all_url'] : get_permalink( $page_id );
        $style_class = ! empty( $cs['section_style'] ) ? neonews_home_section_style_class( $cs['section_style'] ) : 'nn-section-style-default';
        echo '<section class="nn-custom-section nn-custom-section-page nn-reveal ' . esc_attr( $style_class ) . '">';
        echo '<header class="nn-section-header">';
        echo '<h2 class="nn-section-title">' . esc_html( $title ?: get_the_title( $page_id ) ) . '</h2>';
        echo '<a href="' . esc_url( $see_all ) . '" class="nn-section-link">' . esc_html__( 'Read more', 'neonews' ) . ' →</a>';
        echo '</header>';
        echo '<div class="nn-custom-page-body nn-content-body">';
        echo apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div></section>';
    }
}
