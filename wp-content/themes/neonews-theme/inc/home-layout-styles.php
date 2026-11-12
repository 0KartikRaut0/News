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
            'glass'     => __( 'Glass — frosted frame & depth', 'neonews' ),
            'kenburns'  => __( 'Ken Burns — slow zoom on image', 'neonews' ),
            'spotlight' => __( 'Spotlight — soft radial vignette', 'neonews' ),
            'elevated'  => __( 'Elevated — shadow & hover lift', 'neonews' ),
            'shine'     => __( 'Shine — light sweep on hover', 'neonews' ),
            'interactive' => __( 'Interactive — cursor glow on hover', 'neonews' ),
        ),
        'carousel' => array(
            'overlay'   => __( 'Overlay — text on image (default)', 'neonews' ),
            'split'     => __( 'Split — image + text side by side', 'neonews' ),
            'filmstrip' => __( 'Filmstrip — peek next slide', 'neonews' ),
            'compact'   => __( 'Compact — shorter hero', 'neonews' ),
            'magazine'  => __( 'Magazine — tall hero, bold titles', 'neonews' ),
            'cards'     => __( 'Cards — spaced slides with shadow', 'neonews' ),
            'edge'      => __( 'Edge — wide cinematic slides', 'neonews' ),
            'hover-lift' => __( 'Hover lift — slides rise on mouse-over', 'neonews' ),
        ),
        'posts'    => array(
            'featured-list' => __( 'Featured + list (default)', 'neonews' ),
            'cards'         => __( 'Card grid', 'neonews' ),
            'horizontal'    => __( 'Horizontal scroll cards', 'neonews' ),
            'grid-compact'  => __( 'Compact grid', 'neonews' ),
            'list-only'     => __( 'List rows only', 'neonews' ),
            'magazine'      => __( 'Magazine — lead story + grid', 'neonews' ),
            'bento'         => __( 'Bento — mixed-size tiles', 'neonews' ),
            'editorial'     => __( 'Editorial — dense headline stack', 'neonews' ),
            'overlay-cards' => __( 'Overlay — title on image', 'neonews' ),
            'polaroid'      => __( 'Polaroid — framed print style', 'neonews' ),
        ),
        'video'    => array(
            'grid'       => __( 'Grid', 'neonews' ),
            'horizontal' => __( 'Horizontal scroll', 'neonews' ),
            'theater'    => __( 'Theater — large lead + row', 'neonews' ),
        ),
        'latest'   => array(
            'featured-list' => __( 'Featured + list', 'neonews' ),
            'cards'         => __( 'Card grid', 'neonews' ),
            'horizontal'    => __( 'Horizontal scroll cards', 'neonews' ),
            'grid-compact'  => __( 'Compact grid', 'neonews' ),
            'list-only'     => __( 'List rows only', 'neonews' ),
            'magazine'      => __( 'Magazine — lead story + grid', 'neonews' ),
            'bento'         => __( 'Bento — mixed-size tiles', 'neonews' ),
            'editorial'     => __( 'Editorial — dense headline stack', 'neonews' ),
            'overlay-cards' => __( 'Overlay — title on image', 'neonews' ),
            'polaroid'      => __( 'Polaroid — framed print style', 'neonews' ),
        ),
    );

    return $choices[ $group ] ?? array();
}

/**
 * Homepage hover / mouse effect choices.
 *
 * @return array<string, string>
 */
function neonews_home_hover_choices() {
    return array(
        'lift'       => __( 'Lift — cards & slides rise (default)', 'neonews' ),
        'glow'       => __( 'Glow — brand-colored shadow', 'neonews' ),
        'zoom'       => __( 'Zoom — image zoom on hover', 'neonews' ),
        'tilt'       => __( '3D tilt — follows mouse on cards', 'neonews' ),
        'spotlight'  => __( 'Spotlight — cursor light on banner & cards', 'neonews' ),
        'none'       => __( 'None — static (no extra motion)', 'neonews' ),
    );
}

/**
 * @param string $value Raw.
 * @return string
 */
function neonews_sanitize_home_hover( $value ) {
    $choices = neonews_home_hover_choices();
    $value   = sanitize_key( (string) $value );
    if ( isset( $choices[ $value ] ) ) {
        return $value;
    }
    return 'lift';
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
 * Open enhanced horizontal scroll wrapper (nav buttons + track).
 *
 * @param string $wrapper_class Outer classes.
 */
function neonews_home_hscroll_open( $wrapper_class ) {
    echo '<div class="' . esc_attr( $wrapper_class . ' nn-hscroll-wrap nn-hscroll-enhanced' ) . '">';
    echo '<button type="button" class="nn-hscroll-nav nn-hscroll-prev" disabled aria-label="' . esc_attr__( 'Scroll left', 'neonews' ) . '">';
    echo '<span aria-hidden="true">‹</span></button>';
    echo '<div class="nn-hscroll-track" tabindex="0" role="region" aria-label="' . esc_attr__( 'Scrollable content', 'neonews' ) . '">';
}

/**
 * Close horizontal scroll wrapper.
 */
function neonews_home_hscroll_close() {
    echo '</div>';
    echo '<button type="button" class="nn-hscroll-nav nn-hscroll-next" aria-label="' . esc_attr__( 'Scroll right', 'neonews' ) . '">';
    echo '<span aria-hidden="true">›</span></button>';
    echo '</div>';
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

    $layout        = neonews_sanitize_home_layout( 'posts', $layout, 'featured-list' );
    $wrapper_class = 'nn-home-posts nn-home-layout-' . esc_attr( $layout );

    if ( 'horizontal' === $layout ) {
        neonews_home_hscroll_open( $wrapper_class );
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="nn-hscroll-item">';
            get_template_part( 'template-parts/content', 'card' );
            echo '</div>';
        }
        neonews_home_hscroll_close();
        wp_reset_postdata();
        return;
    }

    if ( 'magazine' === $layout ) {
        $posts = array();
        while ( $query->have_posts() ) {
            $query->the_post();
            $posts[] = get_post();
        }
        wp_reset_postdata();

        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-magazine-layout">';
        if ( ! empty( $posts ) ) {
            $lead = array_shift( $posts );
            $GLOBALS['post'] = $lead; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            setup_postdata( $lead );
            echo '<div class="nn-magazine-lead">';
            get_template_part( 'template-parts/home', 'featured' );
            echo '</div>';
            if ( ! empty( $posts ) ) {
                echo '<div class="nn-magazine-grid">';
                foreach ( $posts as $post_obj ) {
                    $GLOBALS['post'] = $post_obj; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    setup_postdata( $post_obj );
                    get_template_part( 'template-parts/content', 'card' );
                }
                echo '</div>';
            }
            wp_reset_postdata();
        }
        echo '</div>';
        return;
    }

    if ( 'bento' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-bento-layout">';
        $i = 0;
        while ( $query->have_posts() ) {
            $query->the_post();
            $i++;
            $mod = $i <= 5 ? ' nn-bento-item-' . $i : ' nn-bento-item-rest';
            echo '<div class="nn-bento-item' . esc_attr( $mod ) . '">';
            get_template_part( 'template-parts/content', 'card' );
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    if ( 'editorial' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-editorial-stack">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/home', 'list-item' );
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    if ( 'overlay-cards' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-post-grid nn-overlay-cards-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'card' );
        }
        echo '</div>';
        wp_reset_postdata();
        return;
    }

    if ( 'polaroid' === $layout ) {
        echo '<div class="' . esc_attr( $wrapper_class ) . ' nn-post-grid nn-polaroid-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'card' );
        }
        echo '</div>';
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
        neonews_home_hscroll_open( $class . ' nn-watch-hscroll' );
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="nn-hscroll-item nn-hscroll-item-video">';
            get_template_part( 'template-parts/home', 'video-card', array( 'featured' => false ) );
            echo '</div>';
        }
        neonews_home_hscroll_close();
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
