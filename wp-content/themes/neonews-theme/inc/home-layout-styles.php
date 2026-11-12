<?php
/**
 * Homepage section layout styles (cards, horizontal scroll, banner, carousel).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Layout option labels for admin UI.
 *
 * @param string $group banner|carousel|posts|video.
 * @return array<string, string>
 */
function neonews_home_layout_choices( $group ) {
    $choices = array(
        'banner'   => array(
            'classic'   => __( 'Classic — rounded, contained', 'neonews' ),
            'fullbleed' => __( 'Full bleed — edge-to-edge', 'neonews' ),
            'minimal'   => __( 'Minimal — slim strip', 'neonews' ),
            'cinematic' => __( 'Cinematic — tall + vignette', 'neonews' ),
            'framed'    => __( 'Framed — thick border & shadow', 'neonews' ),
        ),
        'carousel' => array(
            'overlay'   => __( 'Overlay — text on image (default)', 'neonews' ),
            'split'     => __( 'Split — image + text side by side', 'neonews' ),
            'filmstrip' => __( 'Filmstrip — peek next slide', 'neonews' ),
            'compact'   => __( 'Compact — shorter hero', 'neonews' ),
        ),
        'posts'    => array(
            'featured-list' => __( 'Featured + list (default)', 'neonews' ),
            'cards'         => __( 'Card grid', 'neonews' ),
            'horizontal'    => __( 'Horizontal scroll cards', 'neonews' ),
            'grid-compact'  => __( 'Compact 2-column grid', 'neonews' ),
            'list-only'     => __( 'List rows only', 'neonews' ),
        ),
        'video'    => array(
            'grid'       => __( 'Grid', 'neonews' ),
            'horizontal' => __( 'Horizontal scroll', 'neonews' ),
            'theater'    => __( 'Theater — large lead + row', 'neonews' ),
        ),
        'latest'   => array(
            'cards'        => __( 'Cards', 'neonews' ),
            'horizontal'   => __( 'Horizontal scroll', 'neonews' ),
            'grid-compact' => __( 'Compact grid', 'neonews' ),
            'list-only'    => __( 'List only', 'neonews' ),
        ),
    );

    return $choices[ $group ] ?? array();
}

/**
 * Sanitize a layout key for a group.
 *
 * @param string $group Group key.
 * @param string $value Raw value.
 * @param string $fallback Fallback.
 * @return string
 */
function neonews_sanitize_home_layout( $group, $value, $fallback = '' ) {
    $choices = neonews_home_layout_choices( $group );
    $value   = sanitize_key( (string) $value );
    if ( isset( $choices[ $value ] ) ) {
        return $value;
    }
    if ( $fallback && isset( $choices[ $fallback ] ) ) {
        return $fallback;
    }
    return (string) array_key_first( $choices );
}

/**
 * Resolved category block layout.
 *
 * @param array $block Block config.
 * @return string
 */
function neonews_get_category_block_layout( $block ) {
    $settings = neonews_get_settings();
    $default  = neonews_sanitize_home_layout( 'posts', $settings['home_category_layout'] ?? '', 'featured-list' );
    if ( ! empty( $block['layout'] ) ) {
        return neonews_sanitize_home_layout( 'posts', $block['layout'], $default );
    }
    return $default;
}

/**
 * Render posts loop for a homepage layout.
 *
 * @param WP_Query $query   Query with posts.
 * @param string   $layout  Layout slug.
 * @param array    $args    Extra args (featured_first for theater-style).
 */
function neonews_render_home_posts_layout( $query, $layout, $args = array() ) {
    if ( ! $query || ! $query->have_posts() ) {
        return;
    }

    $layout = neonews_sanitize_home_layout( 'posts', $layout, 'featured-list' );
    $wrapper_class = 'nn-home-posts nn-home-layout-' . esc_attr( $layout );

    if ( 'horizontal' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-hscroll-wrap"><div class="nn-hscroll-track">';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="nn-hscroll-item">';
            get_template_part( 'template-parts/content', 'card' );
            echo '</div>';
        }
        echo '</div></div>';
        wp_reset_postdata();
        return;
    }

    if ( 'cards' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-post-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'card' );
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    if ( 'grid-compact' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-post-grid nn-post-grid-compact nn-post-grid-dense">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'card' );
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    if ( 'list-only' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-home-list-stack">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/home', 'list-item' );
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    // featured-list (default).
    echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-home-featured-list">';
    $i = 0;
    while ( $query->have_posts() ) {
        $query->the_post();
        if ( 0 === $i ) {
            get_template_part( 'template-parts/home', 'featured' );
        } else {
            get_template_part( 'template-parts/home', 'list-item' );
        }
        $i++;
    }
    echo '</div>';
    wp_reset_postdata();
}

/**
 * Render video section posts.
 *
 * @param WP_Query $query  Video posts.
 * @param string   $layout Layout slug.
 */
function neonews_render_home_video_layout( $query, $layout ) {
    if ( ! $query || ! $query->have_posts() ) {
        return;
    }

    $layout = neonews_sanitize_home_layout( 'video', $layout, 'grid' );
    $class  = 'nn-watch-grid nn-watch-layout-' . esc_attr( $layout );

    if ( 'horizontal' === $layout ) {
        echo '<div class="' . esc_attr( $class ) . ' nn-hscroll-wrap"><div class="nn-hscroll-track">';
        $v = 0;
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="nn-hscroll-item nn-hscroll-item-video">';
            get_template_part( 'template-parts/home', 'video-card', array( 'featured' => false ) );
            echo '</div>';
            $v++;
        }
        echo '</div></div>';
        wp_reset_postdata();
        return;
    }

    if ( 'theater' === $layout ) {
        echo '<div class="' . esc_attr( $class ) . ' nn-watch-theater">';
        $v = 0;
        while ( $query->have_posts() ) {
            $query->the_post();
            $wrap = 0 === $v ? 'nn-watch-theater-lead' : 'nn-watch-theater-item';
            echo '<div class="' . esc_attr( $wrap ) . '">';
            get_template_part( 'template-parts/home', 'video-card', array( 'featured' => 0 === $v ) );
            echo '</div>';
            $v++;
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    echo '<div class="' . esc_attr( $class ) . '">';
    $v = 0;
    while ( $query->have_posts() ) {
        $query->the_post();
        get_template_part( 'template-parts/home', 'video-card', array( 'featured' => 0 === $v ) );
        $v++;
    }
    echo '</div>';
    wp_reset_postdata();
}

/**
 * CSS class for banner wrapper.
 *
 * @return string
 */
function neonews_get_home_banner_style_class() {
    $settings = neonews_get_settings();
    $style    = neonews_sanitize_home_layout( 'banner', $settings['home_banner_style'] ?? '', 'classic' );
    return 'nn-banner-style-' . $style;
}

/**
 * CSS class for carousel wrapper.
 *
 * @return string
 */
function neonews_get_home_carousel_style_class() {
    $settings = neonews_get_settings();
    $style    = neonews_sanitize_home_layout( 'carousel', $settings['home_carousel_style'] ?? '', 'overlay' );
    return 'nn-carousel-style-' . $style;
}
