<?php
/**
 * Membership badge SVG icons.
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Crown icon for premium members.
 *
 * @param int $size Size in px.
 * @return string
 */
function neonews_membership_crown_svg( $size = 14 ) {
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 16l-1-9 5 3 3-6 3 6 5-3-1 9H5zm2.2-2h9.6l.5-4.5-3.2 1.9L12 8.5 9.9 11.4 6.7 9.5l.5 4.5z"/></svg>',
        absint( $size )
    );
}

/**
 * Basic member shield icon.
 *
 * @param int $size Size in px.
 * @return string
 */
function neonews_membership_basic_svg( $size = 14 ) {
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l8 3v6c0 5.25-3.5 9.74-8 11-4.5-1.26-8-5.75-8-11V5l8-3zm0 2.18L6 6.3V11c0 4.12 2.73 7.79 6 8.94 3.27-1.15 6-4.82 6-8.94V6.3l-6-2.12z"/></svg>',
        absint( $size )
    );
}
