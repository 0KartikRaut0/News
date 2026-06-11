<?php
/**
 * Meta tags, Open Graph, Twitter Cards, canonical URLs.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Head {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        neonews_seo_load_context();

        add_filter( 'pre_get_document_title', array( $this, 'filter_document_title' ), 20 );
        add_filter( 'wp_robots', array( $this, 'filter_wp_robots' ), 20 );
        add_action( 'wp_head', array( $this, 'output_meta' ), 1 );
        add_action( 'wp_head', array( $this, 'output_social' ), 5 );
    }

    /**
     * @param string $title Title.
     * @return string
     */
    public function filter_document_title( $title ) {
        if ( ! NeoNews_SEO::should_output() ) {
            return $title;
        }

        $context = NeoNews_SEO_Context::get();
        return ! empty( $context['title'] ) ? $context['title'] : $title;
    }

    /**
     * @param array $robots Robots directives.
     * @return array
     */
    public function filter_wp_robots( $robots ) {
        if ( ! NeoNews_SEO::should_output() ) {
            return $robots;
        }

        $context = NeoNews_SEO_Context::get();
        if ( ! empty( $context['robots']['noindex'] ) ) {
            $robots['noindex'] = true;
        }
        if ( ! empty( $context['robots']['nofollow'] ) ) {
            $robots['nofollow'] = true;
        }

        return $robots;
    }

    public function output_meta() {
        if ( ! NeoNews_SEO::should_output() ) {
            return;
        }

        $settings = NeoNews_SEO::get_settings();
        $context  = NeoNews_SEO_Context::get();

        if ( ! empty( $settings['enable_meta_description'] ) && ! empty( $context['description'] ) ) {
            $description = NeoNews_SEO_Context::trim_description( $context['description'] );
            $description = apply_filters( 'neonews_seo_meta_description', $description, $context );
            printf(
                '<meta name="description" content="%s">' . "\n",
                esc_attr( $description )
            );
        }

        if ( ! empty( $settings['enable_canonical'] ) && ! empty( $context['canonical'] ) ) {
            $canonical = apply_filters( 'neonews_seo_canonical_url', $context['canonical'], $context );
            printf(
                '<link rel="canonical" href="%s">' . "\n",
                esc_url( $canonical )
            );
        }
    }

    public function output_social() {
        if ( ! NeoNews_SEO::should_output() ) {
            return;
        }

        $settings = NeoNews_SEO::get_settings();
        $context  = NeoNews_SEO_Context::get();
        $title    = wp_strip_all_tags( $context['title'] );
        $desc     = NeoNews_SEO_Context::trim_description( $context['description'] );
        $url      = ! empty( $context['canonical'] ) ? $context['canonical'] : NeoNews_SEO_Context::current_url();

        if ( ! empty( $settings['enable_open_graph'] ) ) {
            printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_locale() ) ) );
            printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
            printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
            printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
            printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
            printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $context['og_type'] ) );

            if ( 'article' === $context['og_type'] && ! empty( $context['object_id'] ) ) {
                $post_id = (int) $context['object_id'];
                printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( 'c', $post_id ) ) );
                printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( 'c', $post_id ) ) );

                if ( ! empty( $settings['article_section_category'] ) ) {
                    $cats = get_the_category( $post_id );
                    if ( ! empty( $cats ) ) {
                        printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $cats[0]->name ) );
                    }
                }

                $tags = get_the_tags( $post_id );
                if ( $tags ) {
                    foreach ( array_slice( $tags, 0, 5 ) as $tag ) {
                        printf( '<meta property="article:tag" content="%s">' . "\n", esc_attr( $tag->name ) );
                    }
                }
            }

            if ( ! empty( $context['og_image'] ) ) {
                printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $context['og_image'] ) );
                if ( ! empty( $context['og_image_w'] ) ) {
                    printf( '<meta property="og:image:width" content="%d">' . "\n", absint( $context['og_image_w'] ) );
                }
                if ( ! empty( $context['og_image_h'] ) ) {
                    printf( '<meta property="og:image:height" content="%d">' . "\n", absint( $context['og_image_h'] ) );
                }
            }
        }

        if ( ! empty( $settings['enable_twitter_cards'] ) ) {
            $card = ! empty( $context['twitter_card'] ) ? $context['twitter_card'] : 'summary_large_image';
            printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $card ) );
            printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
            printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );

            $twitter_site = trim( (string) ( $settings['twitter_site'] ?? '' ) );
            if ( $twitter_site ) {
                if ( '@' !== $twitter_site[0] ) {
                    $twitter_site = '@' . $twitter_site;
                }
                printf( '<meta name="twitter:site" content="%s">' . "\n", esc_attr( $twitter_site ) );
            }

            if ( ! empty( $context['og_image'] ) ) {
                printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $context['og_image'] ) );
            }
        }
    }
}
