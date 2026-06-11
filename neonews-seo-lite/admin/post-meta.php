<?php
/**
 * Per-post SEO fields (post editor only).
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Post meta box.
 */
final class NeoNews_SEO_Lite_Post_Meta {

    /** @var self|null */
    private static $instance = null;

    /**
     * @return self
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register' ) );
        add_action( 'save_post', array( $this, 'save' ), 10, 2 );
    }

    /**
     * @return void
     */
    public function register() {
        add_meta_box(
            'neonews_seo_lite',
            __( 'SEO Lite', 'neonews-seo-lite' ),
            array( $this, 'render' ),
            'post',
            'normal',
            'default'
        );
    }

    /**
     * @param WP_Post $post Post.
     * @return void
     */
    public function render( $post ) {
        wp_nonce_field( 'neonews_seo_lite_save', 'neonews_seo_lite_nonce' );

        $title  = get_post_meta( $post->ID, '_nn_seo_lite_title', true );
        $desc   = get_post_meta( $post->ID, '_nn_seo_lite_description', true );
        $robots = get_post_meta( $post->ID, '_nn_seo_lite_robots', true );
        ?>
        <p>
            <label for="nn-seo-lite-title"><strong><?php esc_html_e( 'SEO title', 'neonews-seo-lite' ); ?></strong></label><br />
            <input type="text" class="large-text" id="nn-seo-lite-title" name="nn_seo_lite_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
        </p>
        <p>
            <label for="nn-seo-lite-description"><strong><?php esc_html_e( 'Meta description', 'neonews-seo-lite' ); ?></strong></label><br />
            <textarea id="nn-seo-lite-description" name="nn_seo_lite_description" rows="3" class="large-text" maxlength="160"><?php echo esc_textarea( $desc ); ?></textarea>
        </p>
        <p>
            <label for="nn-seo-lite-robots"><strong><?php esc_html_e( 'Robots', 'neonews-seo-lite' ); ?></strong></label><br />
            <select id="nn-seo-lite-robots" name="nn_seo_lite_robots">
                <option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default (index)', 'neonews-seo-lite' ); ?></option>
                <option value="noindex" <?php selected( $robots, 'noindex' ); ?>><?php esc_html_e( 'Noindex', 'neonews-seo-lite' ); ?></option>
                <option value="noindex,nofollow" <?php selected( $robots, 'noindex,nofollow' ); ?>><?php esc_html_e( 'Noindex, nofollow', 'neonews-seo-lite' ); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post.
     * @return void
     */
    public function save( $post_id, $post ) {
        if ( ! isset( $_POST['neonews_seo_lite_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_seo_lite_nonce'] ) ), 'neonews_seo_lite_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) || 'post' !== $post->post_type ) {
            return;
        }

        $map = array(
            '_nn_seo_lite_title'       => sanitize_text_field( wp_unslash( $_POST['nn_seo_lite_title'] ?? '' ) ),
            '_nn_seo_lite_description' => sanitize_textarea_field( wp_unslash( $_POST['nn_seo_lite_description'] ?? '' ) ),
            '_nn_seo_lite_robots'      => sanitize_text_field( wp_unslash( $_POST['nn_seo_lite_robots'] ?? '' ) ),
        );

        foreach ( $map as $key => $value ) {
            if ( '' === $value ) {
                delete_post_meta( $post_id, $key );
            } else {
                update_post_meta( $post_id, $key, $value );
            }
        }
    }
}
