<?php

/**

 * NewsPulse-style header

 *

 * @package NeoNews

 */



if ( ! defined( 'ABSPATH' ) ) {

    exit;

}



$theme_mode = neonews_get_theme_mode();

$settings   = neonews_get_settings();

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?> data-theme="<?php echo esc_attr( $theme_mode ); ?>">

<head>

    <meta charset="<?php bloginfo( 'charset' ); ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>

</head>



<body <?php body_class( 'nn-newspulse' ); ?>>

<?php wp_body_open(); ?>



<a class="skip-link nn-visually-hidden" href="#main-content"><?php esc_html_e( 'Skip to content', 'neonews' ); ?></a>



<?php
$nn_show_datetime_bar = ! empty( $settings['show_header_datetime'] );
$nn_show_weather_bar  = neonews_weather_enabled();
$nn_show_social_bar   = function_exists( 'neonews_show_header_social' ) && neonews_show_header_social();

if ( $nn_show_datetime_bar || $nn_show_weather_bar || $nn_show_social_bar ) :
    ?>
<div class="nn-datetime-bar<?php echo ! $nn_show_datetime_bar ? ' nn-datetime-bar-compact' : ''; ?>">

    <div class="nn-container nn-datetime-inner">

        <?php if ( $nn_show_datetime_bar ) : ?>
        <time id="nn-live-datetime" datetime="<?php echo esc_attr( current_time( 'c' ) ); ?>">

            <?php echo esc_html( date_i18n( 'l, F j, Y · g:i:s A' ) ); ?>

        </time>
        <?php else : ?>
        <span class="nn-datetime-spacer" aria-hidden="true"></span>
        <?php endif; ?>

        <div class="nn-datetime-end">
            <?php if ( $nn_show_weather_bar ) : ?>
                <?php neonews_render_weather_header(); ?>
            <?php endif; ?>
            <?php if ( $nn_show_social_bar ) : ?>
                <?php neonews_render_social_icons( 'header' ); ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php endif; ?>



<header id="masthead" class="nn-header nn-header-pulse" role="banner">

    <div class="nn-container">

        <div class="nn-header-top">

            <div class="nn-logo nn-logo-pulse">

                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nn-logo-link" rel="home">

                    <span class="nn-logo-mark" aria-hidden="true"><?php echo esc_html( neonews_get_setting( 'logo_mark', '✦' ) ); ?></span>

                    <span class="nn-logo-text"><?php bloginfo( 'name' ); ?></span>

                </a>
                <?php endif; ?>

            </div>



            <div class="nn-header-search-wrap nn-desktop-only">
                <?php if ( neonews_is_feature_enabled( 'show_header_search' ) ) : ?>
                <form role="search" method="get" class="nn-header-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">

                    <label class="nn-visually-hidden" for="nn-header-search-input"><?php esc_html_e( 'Search', 'neonews' ); ?></label>

                    <input type="search" id="nn-header-search-input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( neonews_get_setting( 'search_placeholder_desktop', __( 'Search stories, topics, authors…', 'neonews' ) ) ); ?>" />

                    <button type="submit" class="nn-search-submit"><?php esc_html_e( 'Search', 'neonews' ); ?></button>

                </form>
                <?php endif; ?>
            </div>



            <div class="nn-header-actions">

                <?php if ( neonews_is_feature_enabled( 'show_header_auth' ) ) : ?>
                    <?php neonews_header_user_menu(); ?>
                <?php endif; ?>

                <button type="button" class="nn-theme-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'neonews' ); ?>" data-current="<?php echo esc_attr( $theme_mode ); ?>">

                    <svg class="nn-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line></svg>

                    <svg class="nn-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>

                </button>

                <button type="button" class="nn-mobile-toggle" aria-label="<?php esc_attr_e( 'Menu', 'neonews' ); ?>" aria-expanded="false" aria-controls="mobile-navigation">

                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>

                </button>

            </div>

        </div>

        <div class="nn-header-mobile-search nn-mobile-only">
            <?php if ( neonews_is_feature_enabled( 'show_header_search' ) ) : ?>
            <form role="search" method="get" class="nn-header-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label class="nn-visually-hidden" for="nn-mobile-header-search"><?php esc_html_e( 'Search', 'neonews' ); ?></label>
                <input type="search" id="nn-mobile-header-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( neonews_get_setting( 'search_placeholder_mobile', __( 'Search stories…', 'neonews' ) ) ); ?>" />
                <button type="submit" class="nn-search-submit" aria-label="<?php esc_attr_e( 'Search', 'neonews' ); ?>">⌕</button>
            </form>
            <?php endif; ?>
        </div>



        <nav id="site-navigation" class="nn-nav nn-nav-pulse nn-desktop-only" role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'neonews' ); ?>">

            <?php

            wp_nav_menu( array(

                'theme_location' => 'primary',

                'menu_class'     => 'nn-nav-menu',

                'container'      => false,

                'fallback_cb'    => 'neonews_fallback_primary_menu_enhanced',

                'depth'          => 2,

            ) );

            ?>

        </nav>

    </div>

</header>



    <?php
    if ( neonews_show_breaking_ticker() ) :
        $breaking_news = neonews_get_breaking_news( absint( neonews_get_setting( 'breaking_count', 5 ) ) );
        if ( $breaking_news->have_posts() ) :
    ?>

    <div class="nn-breaking-news nn-breaking-pulse nn-breaking-live">
        <div class="nn-container">
            <div class="nn-breaking-inner">
                <span class="nn-breaking-label">
                    <span class="nn-live-dot" aria-hidden="true"></span>
                    <?php echo esc_html( neonews_get_setting( 'breaking_label_live', __( 'LIVE', 'neonews' ) ) ); ?>
                </span>
                <span class="nn-breaking-tag"><?php echo esc_html( neonews_get_setting( 'breaking_label_tag', __( 'Breaking', 'neonews' ) ) ); ?></span>
                <div class="nn-breaking-ticker">
                    <div class="nn-breaking-track">
                        <div class="nn-breaking-list">
                        <?php

                        for ( $loop = 0; $loop < 2; $loop++ ) :

                            while ( $breaking_news->have_posts() ) :

                                $breaking_news->the_post();

                                ?>

                                <span class="nn-breaking-item"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>

                                <span class="nn-breaking-separator">•</span>

                                <?php

                            endwhile;

                            $breaking_news->rewind_posts();

                        endfor;

                        wp_reset_postdata();

                        ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php endif; endif; ?>



<div id="nn-mobile-backdrop" class="nn-mobile-backdrop" aria-hidden="true"></div>

<nav id="mobile-navigation" class="nn-mobile-nav nn-mobile-nav-pulse" aria-label="<?php esc_attr_e( 'Mobile menu', 'neonews' ); ?>" aria-hidden="true">

    <div class="nn-mobile-nav-head">
        <span class="nn-mobile-nav-title"><?php esc_html_e( 'Menu', 'neonews' ); ?></span>
        <button type="button" class="nn-mobile-nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'neonews' ); ?>">×</button>
    </div>

    <?php if ( neonews_is_feature_enabled( 'show_header_auth' ) ) : ?>
        <?php neonews_render_mobile_account_section(); ?>
    <?php endif; ?>

    <?php if ( neonews_is_feature_enabled( 'show_header_search' ) ) : ?>
    <form role="search" method="get" class="nn-mobile-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">

        <input type="search" name="s" placeholder="<?php echo esc_attr( neonews_get_setting( 'search_placeholder_mobile', __( 'Search stories…', 'neonews' ) ) ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />

        <button type="submit"><?php esc_html_e( 'Search', 'neonews' ); ?></button>

    </form>
    <?php endif; ?>

    <?php

    wp_nav_menu( array(

        'theme_location' => 'mobile',

        'menu_class'     => 'nn-mobile-menu',

        'container'      => false,

        'depth'          => 2,

        'fallback_cb'    => function() {

            wp_nav_menu( array(

                'theme_location' => 'primary',

                'menu_class'     => 'nn-mobile-menu',

                'container'      => false,

                'depth'          => 2,

                'fallback_cb'    => 'neonews_fallback_primary_menu_enhanced',

            ) );

        },

    ) );

    ?>

</nav>



<?php if ( neonews_is_ad_zone_enabled( 'ad-header' ) ) : ?>

<div class="nn-ad-header-wrap nn-container">

    <?php neonews_ad_zone( 'ad-header', __( '728×90 leaderboard — Header placement', 'neonews' ) ); ?>

</div>

<?php endif; ?>



<main id="main-content" class="nn-site-main" role="main">


