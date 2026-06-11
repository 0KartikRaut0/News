<?php
/**
 * Shared helpers — excerpts, headings, scoring.
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Helpers {

    /**
     * Build excerpt from post content (first N sentences).
     *
     * @param string $content HTML content.
     * @param int    $max     Max sentences.
     * @return string
     */
    public static function build_excerpt_from_content( $content, $max = 2 ) {
        $text = wp_strip_all_tags( $content );
        $text = preg_replace( '/\s+/', ' ', trim( $text ) );

        if ( '' === $text ) {
            return '';
        }

        $sentences = preg_split( '/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
        if ( empty( $sentences ) ) {
            return wp_trim_words( $text, 35, '…' );
        }

        $max = max( 1, absint( $max ) );
        return implode( ' ', array_slice( $sentences, 0, $max ) );
    }

    /**
     * Get story glance text for a post.
     *
     * @param int $post_id Post ID.
     * @return string
     */
    public static function get_story_glance( $post_id ) {
        $manual = get_post_field( 'post_excerpt', $post_id );
        if ( $manual ) {
            return $manual;
        }

        $cached = get_post_meta( $post_id, '_neonews_smart_glance', true );
        if ( $cached ) {
            return $cached;
        }

        $max = absint( NeoNews_Smart::get_settings( 'excerpt_max_sentences' ) );
        return self::build_excerpt_from_content( get_post_field( 'post_content', $post_id ), $max );
    }

    /**
     * Parse h2/h3 headings from HTML content.
     *
     * @param string $content HTML.
     * @return array[] Each: text, level, id.
     */
    public static function parse_headings( $content ) {
        if ( ! preg_match_all( '/<h([2-3])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER ) ) {
            return array();
        }

        $headings = array();
        $used_ids = array();

        foreach ( $matches as $m ) {
            $text = wp_strip_all_tags( $m[2] );
            $text = trim( $text );
            if ( '' === $text ) {
                continue;
            }

            $id = sanitize_title( $text );
            if ( isset( $used_ids[ $id ] ) ) {
                $used_ids[ $id ]++;
                $id .= '-' . $used_ids[ $id ];
            } else {
                $used_ids[ $id ] = 1;
            }

            $headings[] = array(
                'text'  => $text,
                'level' => (int) $m[1],
                'id'    => $id,
            );
        }

        return $headings;
    }

    /**
     * Inject id anchors into heading tags for TOC jumps.
     *
     * @param string $content HTML.
     * @return string
     */
    public static function inject_heading_ids( $content ) {
        $headings = self::parse_headings( $content );
        if ( empty( $headings ) ) {
            return $content;
        }

        $i = 0;
        return preg_replace_callback(
            '/<h([2-3])([^>]*)>(.*?)<\/h\1>/is',
            function ( $m ) use ( $headings, &$i ) {
                if ( ! isset( $headings[ $i ] ) ) {
                    return $m[0];
                }
                $id   = esc_attr( $headings[ $i ]['id'] );
                $i++;
                if ( false !== stripos( $m[2], 'id=' ) ) {
                    return $m[0];
                }
                return '<h' . $m[1] . $m[2] . ' id="' . $id . '">' . $m[3] . '</h' . $m[1] . '>';
            },
            $content,
            count( $headings )
        );
    }

    /**
     * Score related post by tag/title overlap.
     *
     * @param int $source_id Source post.
     * @param int $candidate_id Candidate post.
     * @return int
     */
    public static function score_related_post( $source_id, $candidate_id ) {
        $score = 0;

        $source_tags = wp_get_post_tags( $source_id, array( 'fields' => 'ids' ) );
        $cand_tags   = wp_get_post_tags( $candidate_id, array( 'fields' => 'ids' ) );
        $score      += count( array_intersect( (array) $source_tags, (array) $cand_tags ) ) * 10;

        $source_cats = wp_get_post_categories( $source_id );
        $cand_cats   = wp_get_post_categories( $candidate_id );
        $score      += count( array_intersect( (array) $source_cats, (array) $cand_cats ) ) * 5;

        $source_words = self::title_keywords( get_the_title( $source_id ) );
        $cand_words   = self::title_keywords( get_the_title( $candidate_id ) );
        $score       += count( array_intersect( $source_words, $cand_words ) ) * 3;

        return $score;
    }

    /**
     * @param string $title Title.
     * @return string[]
     */
    public static function title_keywords( $title ) {
        $words = preg_split( '/\s+/', strtolower( wp_strip_all_tags( $title ) ) );
        $stop  = array( 'the', 'a', 'an', 'and', 'or', 'in', 'on', 'at', 'to', 'for', 'of', 'is', 'are', 'with' );
        $words = array_filter(
            (array) $words,
            function ( $w ) use ( $stop ) {
                return strlen( $w ) > 3 && ! in_array( $w, $stop, true );
            }
        );
        return array_values( array_unique( $words ) );
    }

    /**
     * Top category IDs from user activity log.
     *
     * @param int $user_id User ID.
     * @param int $limit   Limit.
     * @return int[]
     */
    public static function get_user_top_categories( $user_id, $limit = 3 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'neonews_user_activity';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return array();
        }

        $reads = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT object_id FROM {$table} WHERE user_id = %d AND event_type = 'post_read' ORDER BY created_at DESC LIMIT 30",
                $user_id
            )
        );

        if ( empty( $reads ) ) {
            return array();
        }

        $counts = array();
        foreach ( $reads as $post_id ) {
            foreach ( wp_get_post_categories( (int) $post_id ) as $cat_id ) {
                $counts[ $cat_id ] = ( $counts[ $cat_id ] ?? 0 ) + 1;
            }
        }

        arsort( $counts );
        return array_slice( array_map( 'intval', array_keys( $counts ) ), 0, $limit );
    }

    /**
     * @param int $user_id User ID.
     * @param int $limit   Limit.
     * @return int[]
     */
    public static function get_user_read_post_ids( $user_id, $limit = 50 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'neonews_user_activity';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return array();
        }

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT object_id FROM {$table} WHERE user_id = %d AND event_type = 'post_read' ORDER BY created_at DESC LIMIT %d",
                $user_id,
                $limit
            )
        );

        return array_map( 'intval', $ids );
    }
}
