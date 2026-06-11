<?php
/**
 * User Roles Handler
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * User Roles Class
 */
class NeoNews_User_Roles {

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
        add_action( 'admin_init', array( $this, 'maybe_update_roles' ) );
    }

    /**
     * Activation - Add custom roles
     */
    public static function activate() {
        $roles_added = get_option( 'neonews_roles_added' );
        
        if ( $roles_added ) {
            return;
        }

        add_role(
            'nn_reporter',
            __( 'Reporter', 'neonews-core' ),
            array(
                'read'                   => true,
                'edit_posts'             => true,
                'delete_posts'           => false,
                'publish_posts'          => false,
                'upload_files'           => true,
                'edit_published_posts'   => false,
                'delete_published_posts' => false,
            )
        );

        $editor_role = get_role( 'editor' );
        if ( $editor_role ) {
            $editor_role->add_cap( 'manage_neonews_submissions' );
            $editor_role->add_cap( 'approve_neonews_posts' );
        }

        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'manage_neonews_submissions' );
            $admin_role->add_cap( 'approve_neonews_posts' );
            $admin_role->add_cap( 'manage_neonews_settings' );
        }

        update_option( 'neonews_roles_added', true );
    }

    /**
     * Check and update roles if needed
     */
    public function maybe_update_roles() {
        $current_version = get_option( 'neonews_roles_version', '0' );
        
        if ( version_compare( $current_version, NEONEWS_CORE_VERSION, '<' ) ) {
            $this->update_roles();
            update_option( 'neonews_roles_version', NEONEWS_CORE_VERSION );
        }
    }

    /**
     * Update existing roles
     */
    private function update_roles() {
        $reporter = get_role( 'nn_reporter' );
        if ( $reporter ) {
            $reporter->add_cap( 'read' );
            $reporter->add_cap( 'edit_posts' );
            $reporter->add_cap( 'upload_files' );
        }
    }

    /**
     * Check if user is a reporter
     */
    public static function is_reporter( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );
        
        if ( ! $user ) {
            return false;
        }

        return in_array( 'nn_reporter', (array) $user->roles, true );
    }

    /**
     * Check if user can submit news
     */
    public static function can_submit_news( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        $user = get_userdata( $user_id );
        
        if ( ! $user ) {
            return false;
        }

        $allowed_roles = array( 'nn_reporter', 'editor', 'administrator', 'author' );
        
        foreach ( $allowed_roles as $role ) {
            if ( in_array( $role, (array) $user->roles, true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can approve submissions
     */
    public static function can_approve_submissions( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        return user_can( $user_id, 'approve_neonews_posts' );
    }

    /**
     * Deactivation - Remove custom roles (optional)
     */
    public static function deactivate() {
    }

    /**
     * Uninstall - Remove all data
     */
    public static function uninstall() {
        remove_role( 'nn_reporter' );

        $editor_role = get_role( 'editor' );
        if ( $editor_role ) {
            $editor_role->remove_cap( 'manage_neonews_submissions' );
            $editor_role->remove_cap( 'approve_neonews_posts' );
        }

        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->remove_cap( 'manage_neonews_submissions' );
            $admin_role->remove_cap( 'approve_neonews_posts' );
            $admin_role->remove_cap( 'manage_neonews_settings' );
        }

        delete_option( 'neonews_roles_added' );
        delete_option( 'neonews_roles_version' );
    }
}
