<?php
/**
 * Reader blocks: story glance, key points, TOC.
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Reader {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'neonews_single_before_content', array( $this, 'render_blocks' ), 10 );
        add_filter( 'the_content', array( $this, 'add_heading_ids' ), 9 );
    }

    public function render_blocks() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        $post_id = get_the_ID();
        $content = get_post_field( 'post_content', $post_id );

        if ( NeoNews_Smart::is_enabled( 'story_glance' ) ) {
            $this->render_story_glance( $post_id );
        }

        if ( NeoNews_Smart::is_enabled( 'key_points' ) ) {
            $this->render_key_points( $content );
        }

        if ( NeoNews_Smart::is_enabled( 'toc' ) ) {
            $this->render_toc( $content );
        }
    }

    /**
     * @param int $post_id Post ID.
     */
    private function render_story_glance( $post_id ) {
        $text = NeoNews_Smart_Helpers::get_story_glance( $post_id );
        if ( ! $text ) {
            return;
        }

        $mins = function_exists( 'neonews_get_reading_time' ) ? neonews_get_reading_time( $post_id ) : 1;
        ?>
        <aside class="nn-smart-glance nn-reveal">
            <div class="nn-smart-glance-head">
                <span class="nn-smart-badge"><?php esc_html_e( 'Quick read', 'neonews-smart' ); ?></span>
                <span class="nn-smart-meta"><?php echo esc_html( sprintf( _n( '%d min read', '%d min read', $mins, 'neonews-smart' ), $mins ) ); ?></span>
            </div>
            <p class="nn-smart-glance-text"><?php echo esc_html( $text ); ?></p>
        </aside>
        <?php
    }

    /**
     * @param string $content Content.
     */
    private function render_key_points( $content ) {
        $headings = NeoNews_Smart_Helpers::parse_headings( $content );
        if ( count( $headings ) < 2 ) {
            return;
        }

        $points = array_slice( $headings, 0, 6 );
        ?>
        <aside class="nn-smart-keypoints nn-reveal">
            <h3 class="nn-smart-subtitle"><?php esc_html_e( 'Key points', 'neonews-smart' ); ?></h3>
            <ul class="nn-smart-keypoints-list">
                <?php foreach ( $points as $h ) : ?>
                    <li>
                        <?php if ( NeoNews_Smart::is_enabled( 'toc' ) ) : ?>
                            <a href="#<?php echo esc_attr( $h['id'] ); ?>"><?php echo esc_html( $h['text'] ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $h['text'] ); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php
    }

    /**
     * @param string $content Content.
     */
    private function render_toc( $content ) {
        $headings = NeoNews_Smart_Helpers::parse_headings( $content );
        if ( count( $headings ) < 3 ) {
            return;
        }
        ?>
        <nav class="nn-smart-toc nn-reveal" aria-label="<?php esc_attr_e( 'Table of contents', 'neonews-smart' ); ?>">
            <h3 class="nn-smart-subtitle"><?php esc_html_e( 'In this story', 'neonews-smart' ); ?></h3>
            <ol class="nn-smart-toc-list">
                <?php foreach ( $headings as $h ) : ?>
                    <li class="nn-smart-toc-l<?php echo esc_attr( $h['level'] ); ?>">
                        <a href="#<?php echo esc_attr( $h['id'] ); ?>"><?php echo esc_html( $h['text'] ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }

    /**
     * @param string $content Content.
     * @return string
     */
    public function add_heading_ids( $content ) {
        if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }
        if ( ! NeoNews_Smart::is_enabled( 'toc' ) && ! NeoNews_Smart::is_enabled( 'key_points' ) ) {
            return $content;
        }
        return NeoNews_Smart_Helpers::inject_heading_ids( $content );
    }
}
