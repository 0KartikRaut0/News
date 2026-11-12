<?php
/**
 * Article navigation — back/home bar, new-tab story links, external content links.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Should story links from listings open in a new tab?
 *
 * @return bool
 */
function neonews_open_articles_in_new_tab() {
    return (bool) apply_filters( 'neonews_open_articles_in_new_tab', false );
}

/**
 * Target/rel attributes for post permalinks on listing pages.
 *
 * @return string HTML attributes (leading space) or empty.
 */
function neonews_post_link_target_attrs() {
    if ( ! neonews_open_articles_in_new_tab() ) {
        return '';
    }
    return ' target="_blank" rel="noopener noreferrer"';
}

/**
 * Body classes for listing vs article view.
 *
 * @param array $classes Classes.
 * @return array
 */
function neonews_nav_body_classes( $classes ) {
    if ( is_singular( 'post' ) ) {
        $classes[] = 'nn-article-view';
        $classes[] = 'nn-has-article-nav';
    } elseif ( ! is_admin() && ! is_customize_preview() ) {
        $classes[] = 'nn-listing-context';
    }
    return $classes;
}
add_filter( 'body_class', 'neonews_nav_body_classes' );

/**
 * iOS PWA: allow safe-area insets.
 *
 * @param string $markup Viewport meta content.
 * @return string
 */
function neonews_viewport_fit_cover( $markup ) {
    if ( false === strpos( $markup, 'viewport-fit' ) ) {
        $markup = preg_replace(
            '/content=(["\'])([^"\']+)\1/',
            'content=$1$2, viewport-fit=cover$1',
            $markup,
            1
        );
    }
    return $markup;
}
add_filter( 'wp_viewport_meta', 'neonews_viewport_fit_cover' );

/**
 * Open external links inside post content in a new tab.
 *
 * @param string $content Content HTML.
 * @return string
 */
function neonews_content_external_links_new_tab( $content ) {
    if ( empty( $content ) || is_admin() ) {
        return $content;
    }

    $home_host = wp_parse_url( home_url(), PHP_URL_HOST );

    return preg_replace_callback(
        '/<a\s([^>]*\s)?href=(["\'])([^"\']+)\2([^>]*)>/i',
        function ( $matches ) use ( $home_host ) {
            $href = $matches[3];
            if ( 0 === strpos( $href, '#' ) || 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
                return $matches[0];
            }

            $link_host = wp_parse_url( $href, PHP_URL_HOST );
            if ( ! $link_host ) {
                return $matches[0];
            }

            if ( $link_host === $home_host ) {
                return $matches[0];
            }

            $tag = $matches[0];
            if ( false !== stripos( $tag, 'target=' ) ) {
                return $tag;
            }

            return preg_replace( '/<a\s/i', '<a target="_blank" rel="noopener noreferrer" ', $tag, 1 );
        },
        $content
    );
}
add_filter( 'the_content', 'neonews_content_external_links_new_tab', 15 );

/**
 * Should the sticky article nav render?
 *
 * @return bool
 */
function neonews_show_article_nav() {
    return is_singular( 'post' );
}

/**
 * Sticky back / home bar for articles (PWA + mobile + desktop).
 */
function neonews_render_article_nav() {
    if ( ! neonews_show_article_nav() ) {
        return;
    }

    $home = home_url( '/' );
    ?>
    <nav id="nn-article-nav" class="nn-article-nav" aria-label="<?php esc_attr_e( 'Article navigation', 'neonews' ); ?>">
        <button type="button" class="nn-article-nav-btn" data-nn-nav="back">
            <span class="nn-article-nav-icon" aria-hidden="true">←</span>
            <span><?php esc_html_e( 'Back', 'neonews' ); ?></span>
        </button>
        <a href="<?php echo esc_url( $home ); ?>" class="nn-article-nav-btn nn-article-nav-home">
            <span class="nn-article-nav-icon" aria-hidden="true">⌂</span>
            <span><?php esc_html_e( 'Home', 'neonews' ); ?></span>
        </a>
        <button type="button" class="nn-article-nav-btn nn-article-nav-exit" data-nn-nav="exit" hidden>
            <span class="nn-article-nav-icon" aria-hidden="true">✕</span>
            <span><?php esc_html_e( 'Close', 'neonews' ); ?></span>
        </button>
    </nav>
    <?php
}
add_action( 'wp_body_open', 'neonews_render_article_nav', 20 );
