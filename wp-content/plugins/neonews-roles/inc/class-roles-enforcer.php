<?php
/**
 * Roles Enforcer — blocks access to admin pages based on saved permissions.
 *
 * @package NeoNews_Roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Roles_Enforcer {

    public static function init() {
        // Remove menu items the role can't access.
        add_action( 'admin_menu', array( __CLASS__, 'filter_admin_menu' ), 999 );

        // Block direct URL access to restricted pages.
        add_action( 'admin_init', array( __CLASS__, 'block_direct_access' ) );
    }

    /**
     * Remove menu/submenu items for pages the current user can't access.
     * Administrators are never affected.
     */
    public static function filter_admin_menu() {
        if ( NeoNews_Roles_Manager::current_user_is_administrator() ) {
            return;
        }

        $pages = NeoNews_Roles_Manager::get_pages();

        foreach ( $pages as $slug => $info ) {
            if ( NeoNews_Roles_Manager::current_user_can_page( $slug ) ) {
                continue;
            }

            // Map our page slugs to WP menu slugs.
            $menu_slug = self::slug_to_menu( $slug );

            // Try removing as submenu under neonews-settings first.
            remove_submenu_page( 'neonews-settings', $menu_slug );

            // Also remove top-level WP core menus.
            remove_menu_page( $menu_slug );
        }
    }

    /**
     * Block direct URL access even if someone types the URL manually.
     */
    public static function block_direct_access() {
        if ( NeoNews_Roles_Manager::current_user_is_administrator() ) {
            return;
        }

        if ( ! is_admin() ) {
            return;
        }

        $current_page = sanitize_key( $_GET['page'] ?? '' );
        $current_file = basename( $_SERVER['PHP_SELF'] ?? '' );

        $pages = NeoNews_Roles_Manager::get_pages();

        foreach ( $pages as $slug => $info ) {
            if ( NeoNews_Roles_Manager::current_user_can_page( $slug ) ) {
                continue;
            }

            $menu_slug = self::slug_to_menu( $slug );

            // NeoNews pages use ?page=slug
            if ( $current_page && $current_page === $menu_slug ) {
                wp_die(
                    esc_html__( 'You do not have permission to access this page.', 'neonews-roles' ),
                    esc_html__( 'Access Denied', 'neonews-roles' ),
                    array( 'response' => 403, 'back_link' => true )
                );
            }

            // WP core pages use specific PHP files.
            $file_map = self::get_file_map();
            if ( isset( $file_map[ $slug ] ) && $current_file === $file_map[ $slug ] ) {
                wp_die(
                    esc_html__( 'You do not have permission to access this page.', 'neonews-roles' ),
                    esc_html__( 'Access Denied', 'neonews-roles' ),
                    array( 'response' => 403, 'back_link' => true )
                );
            }
        }
    }

    /**
     * Map our internal slug to the actual WP menu slug.
     *
     * @param string $slug Internal slug.
     * @return string
     */
    private static function slug_to_menu( $slug ) {
        $map = array(
            'edit-posts'    => 'edit.php',
            'edit-pages'    => 'edit.php?post_type=page',
            'upload'        => 'upload.php',
            'edit-comments' => 'edit-comments.php',
            'themes'        => 'themes.php',
            'plugins'       => 'plugins.php',
            'users'         => 'users.php',
            'tools'         => 'tools.php',
            'options-general' => 'options-general.php',
        );
        return $map[ $slug ] ?? $slug;
    }

    /**
     * Map our internal slug to the WP admin PHP filename for direct-access blocking.
     *
     * @return array
     */
    private static function get_file_map() {
        return array(
            'edit-posts'      => 'edit.php',
            'edit-pages'      => 'edit.php',
            'upload'          => 'upload.php',
            'edit-comments'   => 'edit-comments.php',
            'themes'          => 'themes.php',
            'plugins'         => 'plugins.php',
            'users'           => 'users.php',
            'tools'           => 'tools.php',
            'options-general' => 'options-general.php',
        );
    }
}
