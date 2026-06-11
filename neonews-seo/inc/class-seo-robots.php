<?php
/**
 * robots.txt enhancements.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Robots {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_filter( 'robots_txt', array( $this, 'append_rules' ), 20, 2 );
    }

    /**
     * @param string $output Output.
     * @param bool   $public Public blog.
     * @return string
     */
    public function append_rules( $output, $public ) {
        if ( ! NeoNews_SEO::is_enabled() || ! $public ) {
            return $output;
        }

        $settings = NeoNews_SEO::get_settings();
        $extra    = trim( (string) ( $settings['robots_txt_extra'] ?? '' ) );

        if ( false === strpos( $output, 'Sitemap:' ) ) {
            $output .= "\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
        }

        if ( $extra ) {
            $output .= "\n# NeoNews SEO\n" . $extra . "\n";
        }

        return $output;
    }
}
