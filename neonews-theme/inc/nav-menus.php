<?php
/**
 * Primary navigation helpers — Exclusive link & Company submenu.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Company page slugs for nav submenu.
 *
 * @return string[]
 */
function neonews_get_company_nav_slugs() {
    return neonews_get_footer_company_slugs();
}

/**
 * Exclusive page menu label with star icon markup.
 *
 * @return string
 */
function neonews_get_exclusive_nav_label() {
    return '<span class="nn-nav-premium-label"><span class="nn-nav-star" aria-hidden="true">★</span> ' . esc_html__( 'Exclusive', 'neonews' ) . '</span>';
}

/**
 * Sync Exclusive (after Home) and Company submenu on the primary menu.
 *
 * @return bool
 */
function neonews_sync_primary_nav_menus() {
    static $syncing = false;

    if ( $syncing ) {
        return false;
    }

    $syncing = true;

    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;

    if ( ! $menu_id ) {
        $menu_id = neonews_get_or_create_primary_menu();
        if ( ! $menu_id ) {
            $syncing = false;
            return false;
        }
        $locations['primary'] = $menu_id;
        $locations['mobile']  = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    neonews_sync_exclusive_nav_item( $menu_id );
    neonews_sync_company_nav_items( $menu_id );

    $syncing = false;

    return true;
}

/**
 * @return int Menu term ID or 0.
 */
function neonews_get_or_create_primary_menu() {
    $name = __( 'Main Menu', 'neonews' );
    $menu = wp_get_nav_menu_object( $name );
    if ( $menu ) {
        return (int) $menu->term_id;
    }

    $created = wp_create_nav_menu( $name );
    return is_wp_error( $created ) ? 0 : (int) $created;
}

/**
 * @param int $menu_id Menu ID.
 */
function neonews_sync_exclusive_nav_item( $menu_id ) {
    $page_id = 0;
    if ( function_exists( 'neonews_membership_get_exclusive_page_id' ) ) {
        $page_id = neonews_membership_get_exclusive_page_id();
    }
    if ( ! $page_id && function_exists( 'neonews_membership_ensure_exclusive_page' ) ) {
        $page_id = neonews_membership_ensure_exclusive_page();
    }
    if ( ! $page_id ) {
        return;
    }

    $existing_id = neonews_find_nav_item_by_page( $menu_id, $page_id );
    $position    = neonews_get_nav_insert_position_after_home( $menu_id );

    $args = array(
        'menu-item-title'     => neonews_get_exclusive_nav_label(),
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
        'menu-item-position'  => $position,
        'menu-item-classes'   => 'nn-nav-exclusive nn-nav-premium-item',
    );

    wp_update_nav_menu_item( $menu_id, $existing_id ? $existing_id : 0, $args );
}

/**
 * @param int $menu_id Menu ID.
 */
function neonews_sync_company_nav_items( $menu_id ) {
    $parent_id = neonews_find_nav_item_by_class( $menu_id, 'nn-nav-company-parent' );

    if ( ! $parent_id ) {
        $parent_id = wp_update_nav_menu_item(
            $menu_id,
            0,
            array(
                'menu-item-title'   => __( 'Company', 'neonews' ),
                'menu-item-url'     => '#',
                'menu-item-type'    => 'custom',
                'menu-item-status'  => 'publish',
                'menu-item-classes' => 'nn-nav-company-parent menu-item-has-children',
            )
        );
    }

    if ( is_wp_error( $parent_id ) || ! $parent_id ) {
        return;
    }

    $child_order = 1;
    foreach ( neonews_get_company_nav_slugs() as $slug ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            continue;
        }

        $existing = neonews_find_nav_item_by_page( $menu_id, $page->ID, $parent_id );
        wp_update_nav_menu_item(
            $menu_id,
            $existing ? $existing : 0,
            array(
                'menu-item-title'     => get_the_title( $page ),
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $parent_id,
                'menu-item-position'  => $child_order,
            )
        );
        $child_order++;
    }
}

/**
 * @param int $menu_id Menu ID.
 * @return int
 */
function neonews_get_nav_insert_position_after_home( $menu_id ) {
    $items = wp_get_nav_menu_items( $menu_id );
    if ( empty( $items ) ) {
        return 2;
    }

    foreach ( $items as $item ) {
        if ( 'post_type' === $item->type && 'page' === $item->object ) {
            $front_id = (int) get_option( 'page_on_front' );
            if ( $front_id && (int) $item->object_id === $front_id ) {
                return (int) $item->menu_order + 1;
            }
            if ( 0 === (int) $item->menu_order || 'home' === get_post_field( 'post_name', $item->object_id ) ) {
                return (int) $item->menu_order + 1;
            }
        }
        if ( 'custom' === $item->type && trailingslashit( $item->url ) === trailingslashit( home_url( '/' ) ) ) {
            return (int) $item->menu_order + 1;
        }
    }

    return 2;
}

/**
 * @param int $menu_id   Menu ID.
 * @param int $page_id   Page ID.
 * @param int $parent_id Parent item ID.
 * @return int Menu item post ID.
 */
function neonews_find_nav_item_by_page( $menu_id, $page_id, $parent_id = 0 ) {
    $items = wp_get_nav_menu_items( $menu_id );
    if ( empty( $items ) ) {
        return 0;
    }

    foreach ( $items as $item ) {
        if ( (int) $item->object_id === (int) $page_id && 'page' === $item->object ) {
            if ( $parent_id && (int) $item->menu_item_parent !== (int) $parent_id ) {
                continue;
            }
            if ( ! $parent_id && (int) $item->menu_item_parent > 0 ) {
                continue;
            }
            return (int) $item->ID;
        }
    }

    return 0;
}

/**
 * @param int    $menu_id Menu ID.
 * @param string $class   CSS class fragment.
 * @return int
 */
function neonews_find_nav_item_by_class( $menu_id, $class ) {
    $items = wp_get_nav_menu_items( $menu_id );
    if ( empty( $items ) ) {
        return 0;
    }

    foreach ( $items as $item ) {
        if ( in_array( $class, (array) $item->classes, true ) ) {
            return (int) $item->ID;
        }
    }

    return 0;
}

/**
 * Allow HTML in exclusive nav menu title.
 *
 * @param string $title Menu title.
 * @param object $item  Menu item.
 * @return string
 */
function neonews_nav_menu_allow_exclusive_html( $title, $item ) {
    if ( ! empty( $item->classes ) && in_array( 'nn-nav-exclusive', (array) $item->classes, true ) ) {
        return neonews_get_exclusive_nav_label();
    }
    return $title;
}
add_filter( 'nav_menu_item_title', 'neonews_nav_menu_allow_exclusive_html', 10, 2 );

/**
 * Fallback primary menu with Exclusive + Company dropdown.
 */
function neonews_fallback_primary_menu_enhanced() {
    echo '<ul class="nn-nav-menu">';

    $home_class = is_front_page() ? ' class="current-menu-item"' : '';
    echo '<li' . $home_class . '><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'neonews' ) . '</a></li>';

    if ( function_exists( 'neonews_membership_get_exclusive_page_id' ) ) {
        $exclusive_id = neonews_membership_get_exclusive_page_id();
        if ( ! $exclusive_id && function_exists( 'neonews_membership_ensure_exclusive_page' ) ) {
            $exclusive_id = neonews_membership_ensure_exclusive_page();
        }
        if ( $exclusive_id ) {
            $active = is_page( $exclusive_id ) ? ' class="current-menu-item nn-nav-exclusive nn-nav-premium-item"' : ' class="nn-nav-exclusive nn-nav-premium-item"';
            echo '<li' . $active . '><a href="' . esc_url( get_permalink( $exclusive_id ) ) . '">' . neonews_get_exclusive_nav_label() . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    foreach ( neonews_get_fallback_nav_categories() as $slug ) {
        $cat = get_category_by_slug( $slug );
        if ( $cat ) {
            echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
        }
    }

    $company_pages = array();
    foreach ( neonews_get_company_nav_slugs() as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            $company_pages[] = $page;
        }
    }

    if ( ! empty( $company_pages ) ) {
        echo '<li class="menu-item-has-children nn-nav-company-parent"><a href="#">' . esc_html__( 'Company', 'neonews' ) . '</a><ul class="sub-menu">';
        foreach ( $company_pages as $page ) {
            $active = is_page( $page->ID ) ? ' class="current-menu-item"' : '';
            echo '<li' . $active . '><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
        }
        echo '</ul></li>';
    }

    foreach ( neonews_get_fallback_nav_pages() as $page_slug ) {
        if ( in_array( $page_slug, neonews_get_company_nav_slugs(), true ) ) {
            continue;
        }
        $page = get_page_by_path( $page_slug );
        if ( $page ) {
            echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
        }
    }

    echo '</ul>';
}
