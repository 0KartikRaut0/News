<?php
/**
 * News Submission Handler
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * News Submission Class
 */
class NeoNews_News_Submission {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_shortcode( 'neonews_submit_form', array( $this, 'render_submission_form' ) );
        add_action( 'wp_ajax_neonews_submit_article', array( $this, 'handle_submission' ) );
        add_action( 'init', array( $this, 'handle_form_submission' ) );
    }

    /**
     * Render submission form shortcode
     */
    public function render_submission_form( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="nn-notice nn-notice-warning">' . 
                   sprintf(
                       /* translators: %s: login URL */
                       esc_html__( 'Please %s to submit an article.', 'neonews-core' ),
                       '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'log in', 'neonews-core' ) . '</a>'
                   ) . 
                   '</div>';
        }

        if ( ! NeoNews_User_Roles::can_submit_news() ) {
            return '<div class="nn-notice nn-notice-error">' . 
                   esc_html__( 'You do not have permission to submit articles.', 'neonews-core' ) . 
                   '</div>';
        }

        ob_start();
        $this->render_form();
        return ob_get_clean();
    }

    /**
     * Render the form HTML
     */
    private function render_form() {
        $categories = get_categories( array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );
        ?>
        <div class="nn-submission-form-wrapper">
            <form id="nn-submission-form" class="nn-submission-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'neonews_submit_article', 'neonews_submission_nonce' ); ?>
                
                <div class="nn-form-group">
                    <label for="nn-article-title" class="nn-form-label">
                        <?php esc_html_e( 'Article Title', 'neonews-core' ); ?> <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="nn-article-title" 
                           name="article_title" 
                           class="nn-form-input" 
                           required 
                           maxlength="200"
                           placeholder="<?php esc_attr_e( 'Enter your article title...', 'neonews-core' ); ?>">
                </div>

                <div class="nn-form-group">
                    <label for="nn-article-category" class="nn-form-label">
                        <?php esc_html_e( 'Category', 'neonews-core' ); ?> <span class="required">*</span>
                    </label>
                    <select id="nn-article-category" name="article_category" class="nn-form-select" required>
                        <option value=""><?php esc_html_e( 'Select a category', 'neonews-core' ); ?></option>
                        <?php foreach ( $categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="nn-form-group">
                    <label for="nn-article-content" class="nn-form-label">
                        <?php esc_html_e( 'Article Content', 'neonews-core' ); ?> <span class="required">*</span>
                    </label>
                    <?php
                    wp_editor( '', 'nn_article_content', array(
                        'textarea_name' => 'article_content',
                        'textarea_rows' => 15,
                        'media_buttons' => false,
                        'teeny'         => false,
                        'quicktags'     => true,
                        'tinymce'       => array(
                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,link,unlink,undo,redo',
                            'toolbar2' => '',
                        ),
                    ) );
                    ?>
                </div>

                <div class="nn-form-group">
                    <label for="nn-featured-image" class="nn-form-label">
                        <?php esc_html_e( 'Featured Image', 'neonews-core' ); ?>
                    </label>
                    <input type="file" 
                           id="nn-featured-image" 
                           name="featured_image" 
                           class="nn-form-file"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <p class="nn-form-help">
                        <?php esc_html_e( 'Accepted formats: JPG, PNG, GIF, WebP. Max size: 2MB.', 'neonews-core' ); ?>
                    </p>
                </div>

                <div class="nn-form-group">
                    <label for="nn-article-excerpt" class="nn-form-label">
                        <?php esc_html_e( 'Excerpt (Optional)', 'neonews-core' ); ?>
                    </label>
                    <textarea id="nn-article-excerpt" 
                              name="article_excerpt" 
                              class="nn-form-textarea" 
                              rows="3"
                              maxlength="500"
                              placeholder="<?php esc_attr_e( 'Brief summary of your article...', 'neonews-core' ); ?>"></textarea>
                </div>

                <div class="nn-form-notice">
                    <p>
                        <strong><?php esc_html_e( 'Note:', 'neonews-core' ); ?></strong>
                        <?php esc_html_e( 'Your article will be reviewed by our editorial team before publishing.', 'neonews-core' ); ?>
                    </p>
                </div>

                <div class="nn-form-submit">
                    <button type="submit" class="nn-btn nn-btn-primary" id="nn-submit-article">
                        <?php esc_html_e( 'Submit Article', 'neonews-core' ); ?>
                    </button>
                </div>

                <div id="nn-submission-message" class="nn-submission-message" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle form submission (non-AJAX fallback)
     */
    public function handle_form_submission() {
        if ( ! isset( $_POST['neonews_submission_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_submission_nonce'] ) ), 'neonews_submit_article' ) ) {
            return;
        }

        $this->process_submission();
    }

    /**
     * Handle AJAX submission
     */
    public function handle_submission() {
        check_ajax_referer( 'neonews_submit_article', 'neonews_submission_nonce' );

        $result = $this->process_submission();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ) );
        }

        wp_send_json_success( array(
            'message' => esc_html__( 'Your article has been submitted and is pending review.', 'neonews-core' ),
            'post_id' => $result,
        ) );
    }

    /**
     * Process the submission
     */
    private function process_submission() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'not_logged_in', esc_html__( 'You must be logged in to submit articles.', 'neonews-core' ) );
        }

        if ( ! NeoNews_User_Roles::can_submit_news() ) {
            return new WP_Error( 'no_permission', esc_html__( 'You do not have permission to submit articles.', 'neonews-core' ) );
        }

        $title = isset( $_POST['article_title'] ) ? sanitize_text_field( wp_unslash( $_POST['article_title'] ) ) : '';
        $content = isset( $_POST['article_content'] ) ? wp_kses_post( wp_unslash( $_POST['article_content'] ) ) : '';
        $category = isset( $_POST['article_category'] ) ? absint( $_POST['article_category'] ) : 0;
        $excerpt = isset( $_POST['article_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['article_excerpt'] ) ) : '';

        if ( empty( $title ) ) {
            return new WP_Error( 'empty_title', esc_html__( 'Please enter an article title.', 'neonews-core' ) );
        }

        if ( strlen( $title ) > 200 ) {
            return new WP_Error( 'title_too_long', esc_html__( 'Title must be less than 200 characters.', 'neonews-core' ) );
        }

        if ( empty( $content ) ) {
            return new WP_Error( 'empty_content', esc_html__( 'Please enter article content.', 'neonews-core' ) );
        }

        $content_length = strlen( wp_strip_all_tags( $content ) );
        if ( $content_length < 100 ) {
            return new WP_Error( 'content_too_short', esc_html__( 'Article content must be at least 100 characters.', 'neonews-core' ) );
        }

        if ( empty( $category ) ) {
            return new WP_Error( 'empty_category', esc_html__( 'Please select a category.', 'neonews-core' ) );
        }

        $category_exists = term_exists( $category, 'category' );
        if ( ! $category_exists ) {
            return new WP_Error( 'invalid_category', esc_html__( 'Invalid category selected.', 'neonews-core' ) );
        }

        $validated = apply_filters(
            'neonews_submission_validate',
            null,
            array(
                'title'    => $title,
                'content'  => $content,
                'category' => $category,
                'excerpt'  => $excerpt,
            )
        );
        if ( is_wp_error( $validated ) ) {
            return $validated;
        }

        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status'  => 'pending',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id(),
        );

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        wp_set_post_categories( $post_id, array( $category ) );

        if ( ! empty( $_FILES['featured_image']['name'] ) ) {
            $upload_result = $this->handle_featured_image_upload( $post_id );
            
            if ( is_wp_error( $upload_result ) ) {
                wp_delete_post( $post_id, true );
                return $upload_result;
            }
        }

        update_post_meta( $post_id, '_neonews_submitted_via_frontend', true );
        update_post_meta( $post_id, '_neonews_submission_date', current_time( 'mysql' ) );

        do_action( 'neonews_article_submitted', $post_id, get_current_user_id() );

        if ( function_exists( 'neonews_log_user_activity' ) ) {
            neonews_log_user_activity(
                'article_submit',
                sprintf(
                    /* translators: %s: article title */
                    __( 'Submitted article: %s', 'neonews-core' ),
                    $title
                ),
                $post_id,
                array( 'status' => 'pending' ),
                get_current_user_id()
            );
        }

        return $post_id;
    }

    /**
     * Handle featured image upload
     */
    private function handle_featured_image_upload( $post_id ) {
        if ( empty( $_FILES['featured_image']['name'] ) ) {
            return true;
        }

        $file = $_FILES['featured_image'];

        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return new WP_Error( 'upload_error', esc_html__( 'Error uploading featured image.', 'neonews-core' ) );
        }

        $allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
        $file_type = wp_check_filetype( $file['name'] );
        
        if ( ! in_array( $file['type'], $allowed_types, true ) && ! in_array( 'image/' . $file_type['ext'], $allowed_types, true ) ) {
            return new WP_Error( 'invalid_type', esc_html__( 'Invalid image format. Allowed: JPG, PNG, GIF, WebP.', 'neonews-core' ) );
        }

        $max_size = 2 * 1024 * 1024;
        if ( $file['size'] > $max_size ) {
            return new WP_Error( 'file_too_large', esc_html__( 'Image size must be less than 2MB.', 'neonews-core' ) );
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload( 'featured_image', $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        set_post_thumbnail( $post_id, $attachment_id );

        return true;
    }
}
