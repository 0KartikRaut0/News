<?php
/**
 * Frontend breadcrumb navigation.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Breadcrumbs {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        neonews_seo_load_context();

        add_action( 'neonews_single_before_content', array( $this, 'render' ), 3 );
        add_action( 'neonews_archive_after_header', array( $this, 'render' ), 3 );
    }

    public function render() {
        if ( ! NeoNews_SEO::is_enabled() || ! NeoNews_SEO::get_settings( 'enable_breadcrumbs' ) ) {
            return;
        }

        $context = NeoNews_SEO_Context::get();
        if ( empty( $context['breadcrumbs'] ) || count( $context['breadcrumbs'] ) < 2 ) {
            return;
        }

        $html = self::build_html( $context['breadcrumbs'] );
        $html = apply_filters( 'neonews_seo_breadcrumbs_html', $html, $context['breadcrumbs'] );

        if ( $html ) {
            if ( is_archive() || is_search() || is_author() ) {
                echo '<div class="nn-container nn-seo-breadcrumbs-wrap">';
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $html;
                echo '</div>';
            } else {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $html;
            }
        }
    }

    /**
     * @param array<int,array{name:string,url:string}> $crumbs Crumbs.
     * @return string
     */
    public static function build_html( $crumbs ) {
        $items = array();
        $last  = count( $crumbs ) - 1;

        foreach ( $crumbs as $index => $crumb ) {
            $name = esc_html( $crumb['name'] );
            if ( $index === $last ) {
                $items[] = '<span class="nn-seo-crumb-current" aria-current="page">' . $name . '</span>';
            } else {
                $items[] = '<a class="nn-seo-crumb-link" href="' . esc_url( $crumb['url'] ) . '">' . $name . '</a>';
            }
        }

        return '<nav class="nn-seo-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'neonews-seo' ) . '">' .
            implode( '<span class="nn-seo-crumb-sep" aria-hidden="true">›</span>', $items ) .
            '</nav>';
    }
}
