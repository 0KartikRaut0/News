<?php
/**
 * Submission spam / quality checks (local rules).
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Submission {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_filter( 'neonews_submission_validate', array( $this, 'validate' ), 10, 2 );
    }

    /**
     * @param null|true|WP_Error $result Result.
     * @param array              $data   Submission data.
     * @return null|true|WP_Error
     */
    public function validate( $result, $data ) {
        if ( $result instanceof WP_Error ) {
            return $result;
        }

        if ( ! NeoNews_Smart::is_enabled( 'submission' ) ) {
            return $result;
        }

        $title   = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $plain   = strtolower( wp_strip_all_tags( $title . ' ' . $content ) );

        $banned = NeoNews_Smart::get_settings( 'submission_banned_words' );
        if ( $banned ) {
            foreach ( array_filter( array_map( 'trim', explode( ',', $banned ) ) ) as $word ) {
                if ( '' !== $word && false !== strpos( $plain, strtolower( $word ) ) ) {
                    return new WP_Error(
                        'smart_banned_word',
                        sprintf(
                            /* translators: %s: blocked term */
                            __( 'Submission blocked: disallowed term detected (%s).', 'neonews-smart' ),
                            $word
                        )
                    );
                }
            }
        }

        $max_links = absint( NeoNews_Smart::get_settings( 'submission_max_links' ) );
        if ( $max_links > 0 ) {
            preg_match_all( '/<a\s/i', $content, $links );
            $link_count = count( $links[0] );
            preg_match_all( '#https?://#i', wp_strip_all_tags( $content ), $urls );
            $link_count = max( $link_count, count( $urls[0] ) );

            if ( $link_count > $max_links ) {
                return new WP_Error(
                    'smart_too_many_links',
                    sprintf(
                        /* translators: %d: max links */
                        __( 'Submission blocked: too many links (maximum %d allowed).', 'neonews-smart' ),
                        $max_links
                    )
                );
            }
        }

        return $result;
    }
}
