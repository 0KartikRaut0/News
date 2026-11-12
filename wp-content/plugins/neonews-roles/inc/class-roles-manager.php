<?php
/**
 * Roles Manager — defines available pages/caps and stores permissions.
 *
 * @package NeoNews_Roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Roles_Manager {

    /** Option key where permissions are stored. */
    const OPTION = 'neonews_role_permissions';

    /**
     * Plugin activation — seed defaults so nothing breaks.
     */
    public static function activate() {
        if ( ! get_option( self::OPTION ) ) {
            update_option( self::OPTION, self::get_defaults() );
        }
    }

    /**
     * All NeoNews admin pages that can be gated.
     * slug  => human label
     *
     * @return array
     */
    public static function get_pages() {
        return array(
            // ── NewsPulse core ────────────────────────────────────────────
            'neonews-settings'       => array( 'label' => 'Platform Settings',    'group' => 'NeoNews' ),
            'neonews-guest-activity' => array( 'label' => 'Guest Activity',        'group' => 'NeoNews' ),
            // ── Plugins ───────────────────────────────────────────────────
            'neonews-membership'     => array( 'label' => 'Membership',            'group' => 'NeoNews' ),
            'neonews-core-settings'  => array( 'label' => 'Core Features',         'group' => 'NeoNews' ),
            'neonews-user-activity'  => array( 'label' => 'User Activity Log',     'group' => 'NeoNews' ),
            'neonews-seo'            => array( 'label' => 'SEO',                   'group' => 'NeoNews' ),
            'neonews-security'       => array( 'label' => 'Security Center',       'group' => 'NeoNews' ),
            'neonews-smart'          => array( 'label' => 'Smart Tools',           'group' => 'NeoNews' ),
            'neonews-pwa'            => array( 'label' => 'PWA Settings',          'group' => 'NeoNews' ),
            'neonews-subscribers'    => array( 'label' => 'Newsletter Subscribers','group' => 'NeoNews' ),
            'neonews-roles'          => array( 'label' => 'Role Manager',          'group' => 'NeoNews' ),
            // ── WordPress core areas ──────────────────────────────────────
            'edit-posts'             => array( 'label' => 'Posts',                 'group' => 'WordPress' ),
            'edit-pages'             => array( 'label' => 'Pages',                 'group' => 'WordPress' ),
            'upload'                 => array( 'label' => 'Media Library',         'group' => 'WordPress' ),
            'edit-comments'          => array( 'label' => 'Comments',              'group' => 'WordPress' ),
            'themes'                 => array( 'label' => 'Appearance',            'group' => 'WordPress' ),
            'plugins'                => array( 'label' => 'Plugins',               'group' => 'WordPress' ),
            'users'                  => array( 'label' => 'Users',                 'group' => 'WordPress' ),
            'tools'                  => array( 'label' => 'Tools',                 'group' => 'WordPress' ),
            'options-general'        => array( 'label' => 'Settings (WP Core)',    'group' => 'WordPress' ),
        );
    }

    /**
     * All editable WordPress roles (excluding administrator — always has full access).
     *
     * @return array slug => label
     */
    public static function get_editable_roles() {
        $all    = wp_roles()->roles;
        $output = array();
        foreach ( $all as $slug => $role ) {
            if ( 'administrator' === $slug ) {
                continue; // Admin always has everything.
            }
            $output[ $slug ] = translate_user_role( $role['name'] );
        }
        return $output;
    }

    /**
     * Default permissions — mirrors current WordPress defaults so nothing
     * breaks when the plugin is first activated.
     *
     * @return array  role_slug => [ page_slug => bool ]
     */
    public static function get_defaults() {
        $defaults = array(
            'editor' => array(
                'edit-posts'    => true,
                'edit-pages'    => true,
                'upload'        => true,
                'edit-comments' => true,
            ),
            'author' => array(
                'edit-posts' => true,
                'upload'     => true,
            ),
            'contributor' => array(
                'edit-posts' => true,
            ),
            'subscriber' => array(),
        );

        // Any custom roles get empty defaults.
        foreach ( self::get_editable_roles() as $slug => $label ) {
            if ( ! isset( $defaults[ $slug ] ) ) {
                $defaults[ $slug ] = array();
            }
        }

        return $defaults;
    }

    /**
     * Load saved permissions (merged with defaults so new pages are never missing).
     *
     * @return array
     */
    public static function get_permissions() {
        $saved    = get_option( self::OPTION, array() );
        $defaults = self::get_defaults();

        foreach ( $defaults as $role => $pages ) {
            if ( ! isset( $saved[ $role ] ) ) {
                $saved[ $role ] = $pages;
            }
        }

        return $saved;
    }

    /**
     * Check if a role has access to a page slug.
     *
     * @param string $role Role slug.
     * @param string $page Page slug.
     * @return bool
     */
    public static function role_can( $role, $page ) {
        if ( 'administrator' === $role ) {
            return true;
        }
        $perms = self::get_permissions();
        return ! empty( $perms[ $role ][ $page ] );
    }

    /**
     * Save permissions from admin form.
     *
     * @param array $input Raw POST input.
     * @return bool
     */
    public static function save( $input ) {
        $pages = array_keys( self::get_pages() );
        $roles = array_keys( self::get_editable_roles() );
        $clean = array();

        foreach ( $roles as $role ) {
            $clean[ $role ] = array();
            foreach ( $pages as $page ) {
                $clean[ $role ][ $page ] = ! empty( $input[ $role ][ $page ] );
            }
        }

        return update_option( self::OPTION, $clean );
    }

    /**
     * Get current user's roles.
     *
     * @return array
     */
    public static function current_user_roles() {
        $user = wp_get_current_user();
        return (array) $user->roles;
    }

    /**
     * Does the current user (non-admin) have access to a page slug?
     *
     * @param string $page Page slug.
     * @return bool
     */
    public static function current_user_can_page( $page ) {
        if ( current_user_can( 'administrator' ) ) {
            return true;
        }
        foreach ( self::current_user_roles() as $role ) {
            if ( self::role_can( $role, $page ) ) {
                return true;
            }
        }
        return false;
    }
}
