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
        add_action( 'save_post_page', array( $this, 'flush_submit_page_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_submit_page_cache' ) );
    }

    /**
     * Forget the auto-detected submit page when pages change.
     */
    public function flush_submit_page_cache() {
        delete_transient( 'neonews_submit_page_id' );
    }

    /**
     * Render submission form shortcode
     */
    public function render_submission_form( $atts ) {
        if ( ! is_user_logged_in() || ! NeoNews_User_Roles::can_submit_news() ) {
            ob_start();
            $this->render_locked_notice();
            return ob_get_clean();
        }

        ob_start();
        $this->render_form();
        return ob_get_clean();
    }

    /**
     * Where the "become premium" button points.
     *
     * @return string
     */
    public static function get_upgrade_url() {
        $url = (string) NeoNews_Core::get_settings( 'submission_upgrade_url' );
        if ( $url ) {
            return $url;
        }
        if ( class_exists( 'NeoNews_Membership_Frontend' ) ) {
            return NeoNews_Membership_Frontend::get_exclusive_page_url();
        }
        return home_url( '/' );
    }

    /**
     * URL of the page that holds the [neonews_submit_form] shortcode.
     *
     * @return string
     */
    public static function get_submit_page_url() {
        $page_id = absint( NeoNews_Core::get_settings( 'submission_page_id' ) );
        if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
            return get_permalink( $page_id );
        }

        $cached = get_transient( 'neonews_submit_page_id' );
        if ( false === $cached ) {
            $cached = 0;
            $page   = get_page_by_path( 'submit' );
            if ( $page && has_shortcode( $page->post_content, 'neonews_submit_form' ) ) {
                $cached = $page->ID;
            } else {
                $found = get_posts( array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    's'              => '[neonews_submit_form',
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                ) );
                $cached = $found ? (int) $found[0] : 0;
            }
            set_transient( 'neonews_submit_page_id', $cached, DAY_IN_SECONDS );
        }

        return $cached ? get_permalink( $cached ) : home_url( '/submit/' );
    }

    /**
     * Submission access for a visitor: 'open', 'locked' (logged in, not eligible) or 'guest'.
     *
     * @param int|null $user_id User ID.
     * @return string
     */
    public static function get_access_state( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return 'guest';
        }
        return NeoNews_User_Roles::can_submit_news( $user_id ) ? 'open' : 'locked';
    }

    /**
     * Explain how to become eligible instead of a bare permission error.
     */
    private function render_locked_notice() {
        $logged_in    = is_user_logged_in();
        $basic_open   = (bool) NeoNews_Core::get_settings( 'submission_allow_basic' );
        $premium_path = ! $basic_open && (bool) NeoNews_Core::get_settings( 'submission_allow_premium' );

        $title   = (string) NeoNews_Core::get_settings( 'submission_locked_title' );
        $message = (string) NeoNews_Core::get_settings( 'submission_locked_message' );

        if ( $basic_open ) {
            $title   = __( 'Sign in to share your story', 'neonews-core' );
            $message = __( 'Any member can submit a story. Sign in or create a free account — our editorial team reviews every submission, and approved stories go live on the site.', 'neonews-core' );
        }
        if ( ! $title ) {
            $title = $premium_path
                ? __( 'Story submissions are for Premium members', 'neonews-core' )
                : __( 'Story submissions are by invitation', 'neonews-core' );
        }
        if ( ! $message ) {
            $message = $premium_path
                ? __( 'Become a Premium member to share your own stories with our readers. Every story is reviewed by our editorial team, and once approved it goes live on the site with your name on it.', 'neonews-core' )
                : __( 'Story submissions are currently open to our reporters only. Contact our team if you would like to contribute.', 'neonews-core' );
        }

        $steps = array(
            array( __( 'Become Premium', 'neonews-core' ), __( 'Upgrade or redeem your activation key on your profile.', 'neonews-core' ) ),
            array( __( 'Submit your story', 'neonews-core' ), __( 'Write your article right here and send it in.', 'neonews-core' ) ),
            array( __( 'Editorial review', 'neonews-core' ), __( 'Our team checks it for accuracy and quality.', 'neonews-core' ) ),
            array( __( 'Goes live', 'neonews-core' ), __( 'Approved stories are published and you get an email.', 'neonews-core' ) ),
        );
        if ( $basic_open ) {
            $steps[0] = array( __( 'Sign in', 'neonews-core' ), __( 'Use your free account — no upgrade needed.', 'neonews-core' ) );
        }
        $show_steps = $premium_path || $basic_open;
        ?>
        <div class="nn-submit-locked">
            <div class="nn-submit-locked-head">
                <span class="nn-submit-locked-icon" aria-hidden="true">★</span>
                <div>
                    <h2 class="nn-submit-locked-title"><?php echo esc_html( $title ); ?></h2>
                    <p class="nn-submit-locked-text"><?php echo esc_html( $message ); ?></p>
                </div>
            </div>

            <?php if ( $show_steps ) : ?>
                <ol class="nn-submit-steps">
                    <?php foreach ( $steps as $i => $step ) : ?>
                        <li class="nn-submit-step">
                            <span class="nn-submit-step-num"><?php echo esc_html( $i + 1 ); ?></span>
                            <strong><?php echo esc_html( $step[0] ); ?></strong>
                            <span><?php echo esc_html( $step[1] ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <div class="nn-submit-locked-actions">
                <?php if ( ! $logged_in ) : ?>
                    <a class="nn-btn nn-open-login" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'neonews-core' ); ?></a>
                <?php endif; ?>
                <?php if ( $premium_path ) : ?>
                    <a class="nn-btn<?php echo $logged_in ? '' : ' nn-btn-outline'; ?>" href="<?php echo esc_url( self::get_upgrade_url() ); ?>"><?php esc_html_e( 'Become a Premium member', 'neonews-core' ); ?></a>
                    <?php if ( $logged_in && function_exists( 'neonews_get_profile_url' ) ) : ?>
                        <a class="nn-btn nn-btn-outline" href="<?php echo esc_url( neonews_get_profile_url() ); ?>"><?php esc_html_e( 'Redeem activation key', 'neonews-core' ); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
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
                           value="<?php echo esc_attr( isset( $_GET['pitch_title'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_GET['pitch_title'] ) ), 0, 200 ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>"
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
            return new WP_Error( 'no_permission', esc_html__( 'Story submissions are available to Premium members. Upgrade your plan to submit articles for review.', 'neonews-core' ) );
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
