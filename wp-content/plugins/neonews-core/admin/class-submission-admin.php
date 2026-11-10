<?php
/**
 * Story submissions review screen, per-user access, and author emails.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Submission admin class.
 */
class NeoNews_Submission_Admin {

    const STATUS_META = '_neonews_submission_status';
    const REASON_META = '_neonews_submission_reject_reason';

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
        add_action( 'admin_menu', array( $this, 'add_menu' ), 21 );
        add_action( 'admin_post_neonews_submission_review', array( $this, 'handle_review' ) );
        add_action( 'show_user_profile', array( $this, 'render_user_access_field' ) );
        add_action( 'edit_user_profile', array( $this, 'render_user_access_field' ) );
        add_action( 'personal_options_update', array( $this, 'save_user_access_field' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_access_field' ) );
    }

    /**
     * Frontend hooks that must run outside wp-admin too.
     */
    public static function register_global_hooks() {
        add_action( 'neonews_article_submitted', array( __CLASS__, 'on_submitted' ), 10, 2 );
        add_action( 'transition_post_status', array( __CLASS__, 'on_status_change' ), 10, 3 );
    }

    /**
     * Count pending frontend submissions.
     *
     * @return int
     */
    public static function pending_count() {
        $query = new WP_Query(
            array(
                'post_type'      => 'post',
                'post_status'    => 'pending',
                'meta_key'       => '_neonews_submitted_via_frontend',
                'fields'         => 'ids',
                'posts_per_page' => 1,
                'no_found_rows'  => false,
            )
        );
        return (int) $query->found_posts;
    }

    /**
     * Replace the old "Pending Submissions" link with a real review screen.
     */
    public function add_menu() {
        remove_submenu_page( 'neonews-settings', 'edit.php?post_status=pending&post_type=post' );

        $count = self::pending_count();
        $label = __( 'Story Submissions', 'neonews-core' );
        if ( $count ) {
            $label .= ' <span class="awaiting-mod">' . absint( $count ) . '</span>';
        }

        add_submenu_page(
            'neonews-settings',
            __( 'Story Submissions', 'neonews-core' ),
            $label,
            'edit_others_posts',
            'neonews-submissions',
            array( $this, 'render_page' )
        );
    }

    /**
     * Render the review screen.
     */
    public function render_page() {
        if ( ! current_user_can( 'edit_others_posts' ) ) {
            return;
        }

        $tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'pending'; // phpcs:ignore WordPress.Security.NonceVerification
        $tabs = array(
            'pending'  => __( 'Pending review', 'neonews-core' ),
            'approved' => __( 'Approved', 'neonews-core' ),
            'rejected' => __( 'Rejected', 'neonews-core' ),
        );
        if ( ! isset( $tabs[ $tab ] ) ) {
            $tab = 'pending';
        }

        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 50,
            'meta_key'       => '_neonews_submitted_via_frontend',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        if ( 'pending' === $tab ) {
            $args['post_status'] = 'pending';
        } elseif ( 'approved' === $tab ) {
            $args['post_status'] = array( 'publish', 'future' );
        } else {
            $args['post_status'] = array( 'draft', 'trash' );
            $args['meta_query']  = array(
                array(
                    'key'   => self::STATUS_META,
                    'value' => 'rejected',
                ),
            );
            unset( $args['meta_key'] );
        }
        $query = new WP_Query( $args );

        $this->render_notice();
        ?>
        <div class="wrap nn-submissions-admin">
            <h1><?php esc_html_e( 'Story Submissions', 'neonews-core' ); ?></h1>
            <p class="description">
                <?php esc_html_e( 'Stories sent from the Submit Story page. Approving publishes the story and emails the author; rejecting moves it to drafts and emails the reason.', 'neonews-core' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-core-settings#nn-submission-settings' ) ); ?>"><?php esc_html_e( 'Submission settings', 'neonews-core' ); ?></a>
            </p>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $slug => $label ) : ?>
                    <a class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-submissions&tab=' . $slug ) ); ?>"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </nav>

            <table class="widefat striped" style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Story', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Author', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Submitted', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'neonews-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( ! $query->have_posts() ) : ?>
                    <tr><td colspan="5"><?php esc_html_e( 'No submissions here yet.', 'neonews-core' ); ?></td></tr>
                <?php else : ?>
                    <?php
                    foreach ( $query->posts as $post ) :
                        $author     = get_userdata( $post->post_author );
                        $is_premium = $author && NeoNews_User_Roles::is_premium_member( $author->ID );
                        $cats       = get_the_category( $post->ID );
                        $reason     = get_post_meta( $post->ID, self::REASON_META, true );
                        ?>
                        <tr>
                            <td>
                                <strong><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></strong>
                                <div class="description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $post->post_content ), 22 ) ); ?></div>
                                <?php if ( 'rejected' === $tab && $reason ) : ?>
                                    <div class="description"><em><?php echo esc_html( sprintf( /* translators: %s: reason */ __( 'Reason: %s', 'neonews-core' ), $reason ) ); ?></em></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ( $author ) : ?>
                                    <a href="<?php echo esc_url( get_edit_user_link( $author->ID ) ); ?>"><?php echo esc_html( $author->display_name ); ?></a><br>
                                    <span class="nn-sub-plan nn-sub-plan-<?php echo $is_premium ? 'premium' : 'basic'; ?>"><?php echo $is_premium ? esc_html__( 'Premium', 'neonews-core' ) : esc_html__( 'Basic', 'neonews-core' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $cats ? esc_html( $cats[0]->name ) : '—'; ?></td>
                            <td><?php echo esc_html( get_the_date( '', $post ) ); ?></td>
                            <td>
                                <?php if ( 'pending' === $tab ) : ?>
                                    <a class="button" href="<?php echo esc_url( get_preview_post_link( $post ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'neonews-core' ); ?></a>
                                    <?php $this->render_action_form( $post->ID, 'approve', __( 'Approve & publish', 'neonews-core' ), 'button-primary' ); ?>
                                    <?php $this->render_action_form( $post->ID, 'reject', __( 'Reject', 'neonews-core' ), '', true ); ?>
                                <?php elseif ( 'approved' === $tab ) : ?>
                                    <a class="button" href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View live', 'neonews-core' ); ?></a>
                                <?php else : ?>
                                    <?php $this->render_action_form( $post->ID, 'restore', __( 'Move back to pending', 'neonews-core' ) ); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <style>
            .nn-submissions-admin form.nn-sub-action { display:inline-block; margin:0 4px 4px 0; vertical-align:top; }
            .nn-submissions-admin .nn-sub-reason { display:block; width:220px; margin-bottom:4px; }
            .nn-sub-plan { display:inline-block; padding:1px 8px; border-radius:999px; font-size:11px; font-weight:600; }
            .nn-sub-plan-premium { background:#fef3c7; color:#92400e; }
            .nn-sub-plan-basic { background:#e2e8f0; color:#334155; }
        </style>
        <?php
    }

    /**
     * Render a single review action button.
     *
     * @param int    $post_id    Post ID.
     * @param string $decision   approve|reject|restore.
     * @param string $label      Button label.
     * @param string $class      Extra button class.
     * @param bool   $with_reason Show reason input.
     */
    private function render_action_form( $post_id, $decision, $label, $class = '', $with_reason = false ) {
        ?>
        <form class="nn-sub-action" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo 'reject' === $decision ? ' onsubmit="return confirm(\'' . esc_js( __( 'Reject this story?', 'neonews-core' ) ) . '\');"' : ''; ?>>
            <?php wp_nonce_field( 'neonews_submission_review_' . $post_id ); ?>
            <input type="hidden" name="action" value="neonews_submission_review" />
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
            <input type="hidden" name="decision" value="<?php echo esc_attr( $decision ); ?>" />
            <?php if ( $with_reason ) : ?>
                <input type="text" class="nn-sub-reason" name="reason" placeholder="<?php esc_attr_e( 'Reason (optional, emailed to author)', 'neonews-core' ); ?>" />
            <?php endif; ?>
            <button type="submit" class="button <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></button>
        </form>
        <?php
    }

    /**
     * Approve / reject / restore handler.
     */
    public function handle_review() {
        $post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';

        if ( ! $post_id || ! current_user_can( 'edit_others_posts' ) || ! current_user_can( 'publish_posts' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'neonews-core' ) );
        }
        check_admin_referer( 'neonews_submission_review_' . $post_id );

        if ( ! get_post_meta( $post_id, '_neonews_submitted_via_frontend', true ) ) {
            wp_die( esc_html__( 'This post was not submitted from the frontend.', 'neonews-core' ) );
        }

        $tab = 'pending';
        if ( 'approve' === $decision ) {
            wp_update_post(
                array(
                    'ID'          => $post_id,
                    'post_status' => 'publish',
                )
            );
            $done = 'approved';
        } elseif ( 'reject' === $decision ) {
            $reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
            update_post_meta( $post_id, self::STATUS_META, 'rejected' );
            update_post_meta( $post_id, self::REASON_META, $reason );
            wp_update_post(
                array(
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                )
            );
            self::email_author( $post_id, 'rejected', $reason );
            $done = 'rejected';
        } elseif ( 'restore' === $decision ) {
            delete_post_meta( $post_id, self::REASON_META );
            update_post_meta( $post_id, self::STATUS_META, 'pending' );
            wp_update_post(
                array(
                    'ID'          => $post_id,
                    'post_status' => 'pending',
                )
            );
            $tab  = 'rejected';
            $done = 'restored';
        } else {
            wp_die( esc_html__( 'Unknown action.', 'neonews-core' ) );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'        => 'neonews-submissions',
                    'tab'         => $tab,
                    'nn_reviewed' => $done,
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Result notice after a review action.
     */
    private function render_notice() {
        $done = isset( $_GET['nn_reviewed'] ) ? sanitize_key( wp_unslash( $_GET['nn_reviewed'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
        $messages = array(
            'approved' => __( 'Story approved and published. The author has been notified.', 'neonews-core' ),
            'rejected' => __( 'Story rejected and moved to drafts. The author has been notified.', 'neonews-core' ),
            'restored' => __( 'Story moved back to pending review.', 'neonews-core' ),
        );
        if ( isset( $messages[ $done ] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages[ $done ] ) . '</p></div>';
        }
    }

    /**
     * Mark new submissions and alert the editorial team.
     *
     * @param int $post_id Post ID.
     * @param int $user_id Author ID.
     */
    public static function on_submitted( $post_id, $user_id ) {
        update_post_meta( $post_id, self::STATUS_META, 'pending' );

        if ( ! NeoNews_Core::get_settings( 'submission_notify_admin' ) ) {
            return;
        }

        $author = get_userdata( $user_id );
        $subject = sprintf(
            /* translators: %s: site name */
            __( '[%s] New story submitted for review', 'neonews-core' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
        );
        $body = sprintf(
            /* translators: 1: story title, 2: author name, 3: review URL */
            __( "A new story is waiting for review.\n\nTitle: %1\$s\nAuthor: %2\$s\n\nReview it here: %3\$s", 'neonews-core' ),
            get_the_title( $post_id ),
            $author ? $author->display_name : '',
            admin_url( 'admin.php?page=neonews-submissions' )
        );
        wp_mail( get_option( 'admin_email' ), $subject, $body );
    }

    /**
     * Email the author whenever a frontend submission is published (from any screen).
     *
     * @param string  $new_status New status.
     * @param string  $old_status Old status.
     * @param WP_Post $post       Post.
     */
    public static function on_status_change( $new_status, $old_status, $post ) {
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }
        if ( ! get_post_meta( $post->ID, '_neonews_submitted_via_frontend', true ) ) {
            return;
        }
        if ( 'approved' === get_post_meta( $post->ID, self::STATUS_META, true ) ) {
            return;
        }

        update_post_meta( $post->ID, self::STATUS_META, 'approved' );
        delete_post_meta( $post->ID, self::REASON_META );
        self::email_author( $post->ID, 'approved' );
    }

    /**
     * Notify the author of the review outcome.
     *
     * @param int    $post_id Post ID.
     * @param string $outcome approved|rejected.
     * @param string $reason  Optional rejection reason.
     */
    public static function email_author( $post_id, $outcome, $reason = '' ) {
        if ( ! NeoNews_Core::get_settings( 'submission_notify_author' ) ) {
            return;
        }

        $post   = get_post( $post_id );
        $author = $post ? get_userdata( $post->post_author ) : null;
        if ( ! $author || ! $author->user_email ) {
            return;
        }

        $site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        if ( 'approved' === $outcome ) {
            $subject = sprintf( /* translators: %s: site name */ __( '[%s] Your story is live!', 'neonews-core' ), $site );
            $body    = sprintf(
                /* translators: 1: author name, 2: story title, 3: story URL */
                __( "Hi %1\$s,\n\nGreat news — our editorial team approved your story \"%2\$s\" and it is now live:\n%3\$s\n\nThank you for contributing!", 'neonews-core' ),
                $author->display_name,
                get_the_title( $post ),
                get_permalink( $post )
            );
        } else {
            $subject = sprintf( /* translators: %s: site name */ __( '[%s] Update on your story submission', 'neonews-core' ), $site );
            $body    = sprintf(
                /* translators: 1: author name, 2: story title */
                __( "Hi %1\$s,\n\nThank you for submitting \"%2\$s\". After review, our editorial team was not able to publish it this time.", 'neonews-core' ),
                $author->display_name,
                get_the_title( $post )
            );
            if ( $reason ) {
                $body .= "\n\n" . sprintf( /* translators: %s: reason */ __( 'Reason: %s', 'neonews-core' ), $reason );
            }
            $body .= "\n\n" . __( 'You are welcome to revise it and submit again.', 'neonews-core' );
        }

        wp_mail( $author->user_email, $subject, $body );
    }

    /**
     * Per-user submission override on the user profile screen.
     *
     * @param WP_User $user User.
     */
    public function render_user_access_field( $user ) {
        if ( ! current_user_can( 'edit_users' ) ) {
            return;
        }
        $value = get_user_meta( $user->ID, '_neonews_submission_access', true );
        ?>
        <h2><?php esc_html_e( 'Story submissions', 'neonews-core' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="neonews_submission_access"><?php esc_html_e( 'Can submit stories', 'neonews-core' ); ?></label></th>
                <td>
                    <select name="neonews_submission_access" id="neonews_submission_access">
                        <option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Default (follow Core Features → Story Submissions settings)', 'neonews-core' ); ?></option>
                        <option value="allow" <?php selected( $value, 'allow' ); ?>><?php esc_html_e( 'Always allow', 'neonews-core' ); ?></option>
                        <option value="deny" <?php selected( $value, 'deny' ); ?>><?php esc_html_e( 'Block submissions', 'neonews-core' ); ?></option>
                    </select>
                    <p class="description">
                        <?php
                        echo NeoNews_User_Roles::can_submit_news( $user->ID )
                            ? esc_html__( 'Currently: this user can submit stories.', 'neonews-core' )
                            : esc_html__( 'Currently: this user sees the "become Premium" message instead of the form.', 'neonews-core' );
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save per-user override.
     *
     * @param int $user_id User ID.
     */
    public function save_user_access_field( $user_id ) {
        if ( ! current_user_can( 'edit_users' ) || ! isset( $_POST['neonews_submission_access'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core profile form nonce.
            return;
        }
        $value = sanitize_key( wp_unslash( $_POST['neonews_submission_access'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        if ( in_array( $value, array( 'allow', 'deny' ), true ) ) {
            update_user_meta( $user_id, '_neonews_submission_access', $value );
        } else {
            delete_user_meta( $user_id, '_neonews_submission_access' );
        }
    }
}
