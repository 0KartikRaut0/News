<?php
/**
 * Post and page SEO meta box.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Post_Meta {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register' ) );
        add_action( 'save_post', array( $this, 'save' ), 10, 2 );
        add_filter( 'manage_post_posts_columns', array( $this, 'column' ) );
        add_filter( 'manage_pages_columns', array( $this, 'column' ) );
        add_action( 'manage_post_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
        add_action( 'manage_pages_custom_column', array( $this, 'render_column' ), 10, 2 );
    }

    public function register() {
        foreach ( array( 'post', 'page' ) as $type ) {
            add_meta_box(
                'neonews_seo_meta',
                __( 'SEO', 'neonews-seo' ),
                array( $this, 'render' ),
                $type,
                'normal',
                'high'
            );
        }
    }

    /**
     * @param WP_Post $post Post.
     */
    public function render( $post ) {
        neonews_seo_load_context();

        wp_nonce_field( 'neonews_seo_save_post', 'neonews_seo_post_nonce' );

        $title    = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_TITLE );
        $desc     = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_DESCRIPTION );
        $canonical = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_CANONICAL );
        $robots   = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_ROBOTS );
        $focus    = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_FOCUS );
        $og_image = absint( NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_OG_IMAGE ) );
        $preview_desc = $desc ? $desc : NeoNews_SEO_Context::description_for_post( $post );
        ?>
        <div class="nn-seo-meta-box">
            <p class="description"><?php esc_html_e( 'Leave fields blank to use smart defaults (title, excerpt, featured image).', 'neonews-seo' ); ?></p>

            <label for="neonews-seo-title"><?php esc_html_e( 'SEO title', 'neonews-seo' ); ?></label>
            <input type="text" id="neonews-seo-title" name="neonews_seo_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>" />

            <label for="neonews-seo-description"><?php esc_html_e( 'Meta description', 'neonews-seo' ); ?></label>
            <textarea id="neonews-seo-description" name="neonews_seo_description" rows="3" maxlength="160" placeholder="<?php echo esc_attr( wp_trim_words( $preview_desc, 20, '' ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Recommended max 160 characters.', 'neonews-seo' ); ?></p>

            <label for="neonews-seo-focus"><?php esc_html_e( 'Focus keyword (editorial note)', 'neonews-seo' ); ?></label>
            <input type="text" id="neonews-seo-focus" name="neonews_seo_focus" value="<?php echo esc_attr( $focus ); ?>" />

            <label for="neonews-seo-canonical"><?php esc_html_e( 'Canonical URL', 'neonews-seo' ); ?></label>
            <input type="url" id="neonews-seo-canonical" name="neonews_seo_canonical" value="<?php echo esc_attr( $canonical ); ?>" placeholder="<?php echo esc_attr( get_permalink( $post ) ); ?>" />

            <label for="neonews-seo-robots"><?php esc_html_e( 'Robots', 'neonews-seo' ); ?></label>
            <select id="neonews-seo-robots" name="neonews_seo_robots">
                <option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default (index, follow)', 'neonews-seo' ); ?></option>
                <option value="noindex" <?php selected( $robots, 'noindex' ); ?>><?php esc_html_e( 'Noindex', 'neonews-seo' ); ?></option>
                <option value="nofollow" <?php selected( $robots, 'nofollow' ); ?>><?php esc_html_e( 'Nofollow', 'neonews-seo' ); ?></option>
                <option value="noindex,nofollow" <?php selected( $robots, 'noindex,nofollow' ); ?>><?php esc_html_e( 'Noindex, nofollow', 'neonews-seo' ); ?></option>
            </select>

            <label><?php esc_html_e( 'Social image override', 'neonews-seo' ); ?></label>
            <?php $this->render_og_field( $og_image ); ?>
        </div>
        <?php
    }

    /**
     * @param int $attachment_id Attachment ID.
     */
    private function render_og_field( $attachment_id ) {
        $url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
        ?>
        <div class="nn-seo-media-field">
            <input type="hidden" name="neonews_seo_og_image" id="neonews-seo-og-image" value="<?php echo esc_attr( $attachment_id ); ?>" />
            <div class="nn-seo-media-preview"><?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" alt="" /><?php endif; ?></div>
            <button type="button" class="button nn-seo-media-select" data-target="neonews-seo-og-image"><?php esc_html_e( 'Select image', 'neonews-seo' ); ?></button>
            <button type="button" class="button nn-seo-media-clear" data-target="neonews-seo-og-image"><?php esc_html_e( 'Remove', 'neonews-seo' ); ?></button>
        </div>
        <?php
    }

    /**
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post.
     */
    public function save( $post_id, $post ) {
        if ( ! isset( $_POST['neonews_seo_post_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_seo_post_nonce'] ) ), 'neonews_seo_save_post' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array(
            NeoNews_SEO_Meta::POST_TITLE       => sanitize_text_field( wp_unslash( $_POST['neonews_seo_title'] ?? '' ) ),
            NeoNews_SEO_Meta::POST_DESCRIPTION => sanitize_textarea_field( wp_unslash( $_POST['neonews_seo_description'] ?? '' ) ),
            NeoNews_SEO_Meta::POST_FOCUS       => sanitize_text_field( wp_unslash( $_POST['neonews_seo_focus'] ?? '' ) ),
            NeoNews_SEO_Meta::POST_CANONICAL   => esc_url_raw( wp_unslash( $_POST['neonews_seo_canonical'] ?? '' ) ),
            NeoNews_SEO_Meta::POST_ROBOTS      => sanitize_text_field( wp_unslash( $_POST['neonews_seo_robots'] ?? '' ) ),
            NeoNews_SEO_Meta::POST_OG_IMAGE    => absint( $_POST['neonews_seo_og_image'] ?? 0 ),
        );

        foreach ( $fields as $key => $value ) {
            if ( '' === $value || 0 === $value ) {
                delete_post_meta( $post_id, $key );
            } else {
                update_post_meta( $post_id, $key, $value );
            }
        }
    }

    /**
     * @param array $columns Columns.
     * @return array
     */
    public function column( $columns ) {
        $columns['neonews_seo'] = __( 'SEO', 'neonews-seo' );
        return $columns;
    }

    /**
     * @param string $column Column.
     * @param int    $post_id Post ID.
     */
    public function render_column( $column, $post_id ) {
        if ( 'neonews_seo' !== $column ) {
            return;
        }

        $score = NeoNews_SEO_Meta::get_post_score( $post_id );
        $labels = array(
            'good'    => __( 'Good', 'neonews-seo' ),
            'warn'    => __( 'Needs work', 'neonews-seo' ),
            'noindex' => __( 'Noindex', 'neonews-seo' ),
        );

        printf(
            '<span class="nn-seo-score nn-seo-score-%1$s">%2$s</span>',
            esc_attr( $score ),
            esc_html( $labels[ $score ] ?? $score )
        );
    }
}
