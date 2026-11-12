<?php
/**
 * NeoNews Analytics Dashboard — full-screen responsive backend dashboard.
 *
 * Shows: total views, visitors, top articles, user activity, comments,
 * subscribers, registrations, device breakdown, and date-range filtering.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Analytics_Dashboard {

    public static function init() {
        add_action( 'admin_menu',            array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
        add_action( 'wp_ajax_nn_dash_data',  array( __CLASS__, 'ajax_data' ) );
    }

    /* ── Menu ─────────────────────────────────────────────────────────────── */

    public static function add_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Analytics Dashboard', 'neonews-core' ),
            __( '📊 Analytics', 'neonews-core' ),
            'manage_options',
            'neonews-analytics',
            array( __CLASS__, 'render' )
        );
    }

    /* ── Assets ───────────────────────────────────────────────────────────── */

    public static function enqueue( $hook ) {
        if ( 'neonews_page_neonews-analytics' !== $hook ) {
            return;
        }

        // Chart.js from CDN (self-hosted alternative: copy to /assets/js/).
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            array(),
            '4.4.0',
            true
        );

        wp_add_inline_style( 'wp-admin', self::get_css() );
        wp_add_inline_script( 'chartjs', self::get_js(), 'after' );

        wp_localize_script( 'chartjs', 'nnDash', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'nn_dash_nonce' ),
        ) );
    }

    /* ── AJAX data endpoint ───────────────────────────────────────────────── */

    public static function ajax_data() {
        check_ajax_referer( 'nn_dash_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ) );
        }

        $range     = sanitize_key( $_POST['range'] ?? '7' );
        $date_from = sanitize_text_field( $_POST['date_from'] ?? '' );
        $date_to   = sanitize_text_field( $_POST['date_to'] ?? '' );

        // Resolve date range.
        if ( $date_from && $date_to ) {
            $from = $date_from;
            $to   = $date_to;
            $days = max( 1, (int) ( ( strtotime( $to ) - strtotime( $from ) ) / DAY_IN_SECONDS ) + 1 );
        } else {
            $days = in_array( $range, array( '7', '14', '30', '90' ), true ) ? (int) $range : 7;
            $from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
            $to   = gmdate( 'Y-m-d' );
        }

        wp_send_json_success( array(
            'stat_cards'    => self::get_stat_cards( $from, $to, $days ),
            'views_chart'   => self::get_views_chart( $from, $to, $days ),
            'top_posts'     => self::get_top_posts( $from, $to, $days ),
            'activity_feed' => self::get_activity_feed( $from, $to ),
            'user_growth'   => self::get_user_growth( $from, $to, $days ),
            'category_split'=> self::get_category_split(),
            'device_split'  => self::get_device_split( $from, $to ),
        ) );
    }

    /* ── Data helpers ─────────────────────────────────────────────────────── */

    /**
     * Stat cards — headline numbers.
     */
    private static function get_stat_cards( $from, $to, $days ) {
        global $wpdb;

        // Total all-time views.
        $total_views = (int) $wpdb->get_var( // phpcs:ignore
            "SELECT SUM( CAST( meta_value AS UNSIGNED ) )
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_neonews_post_views'"
        );

        // Views in range (sum daily_views meta within date window).
        $range_views = self::sum_views_in_range( $from, $to );

        // Total published posts.
        $post_counts = wp_count_posts( 'post' );
        $total_posts = (int) ( $post_counts->publish ?? 0 );

        // Total registered users.
        $user_counts  = count_users();
        $total_users  = (int) ( $user_counts['total_users'] ?? 0 );

        // New users in range.
        $new_users = (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore
            "SELECT COUNT(*) FROM {$wpdb->users}
             WHERE DATE(user_registered) BETWEEN %s AND %s",
            $from, $to
        ) );

        // Comments in range.
        $comments_in_range = (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore
            "SELECT COUNT(*) FROM {$wpdb->comments}
             WHERE comment_approved = '1'
             AND DATE(comment_date) BETWEEN %s AND %s",
            $from, $to
        ) );

        // Total approved comments.
        $total_comments = (int) get_comments( array( 'count' => true, 'status' => 'approve' ) );

        // Newsletter subscribers (confirmed).
        $sub_count = 0;
        if ( class_exists( 'NeoNews_Newsletter_DB' ) ) {
            $counts    = NeoNews_Newsletter_DB::get_counts();
            $sub_count = (int) $counts['confirmed'];
        }

        // Avg views per post.
        $avg_views = $total_posts > 0 ? round( $total_views / $total_posts ) : 0;

        return array(
            array( 'label' => 'Total Views (All-time)', 'value' => number_format_i18n( $total_views ),    'icon' => '👁',  'color' => '#1d3557' ),
            array( 'label' => 'Views (This Period)',    'value' => number_format_i18n( $range_views ),    'icon' => '📈', 'color' => '#457b9d' ),
            array( 'label' => 'Published Articles',    'value' => number_format_i18n( $total_posts ),    'icon' => '📰', 'color' => '#e63946' ),
            array( 'label' => 'Registered Users',      'value' => number_format_i18n( $total_users ),    'icon' => '👥', 'color' => '#2d6a4f' ),
            array( 'label' => 'New Users (Period)',     'value' => number_format_i18n( $new_users ),      'icon' => '🆕', 'color' => '#52b788' ),
            array( 'label' => 'Comments (Period)',      'value' => number_format_i18n( $comments_in_range ), 'icon' => '💬', 'color' => '#f4a261' ),
            array( 'label' => 'Total Comments',        'value' => number_format_i18n( $total_comments ), 'icon' => '🗨',  'color' => '#e76f51' ),
            array( 'label' => 'Newsletter Subscribers','value' => number_format_i18n( $sub_count ),      'icon' => '📧', 'color' => '#6a4c93' ),
            array( 'label' => 'Avg Views / Article',   'value' => number_format_i18n( $avg_views ),      'icon' => '📊', 'color' => '#264653' ),
        );
    }

    /**
     * Views per day for chart.
     */
    private static function get_views_chart( $from, $to, $days ) {
        global $wpdb;

        // Pull all daily_views metas.
        $rows = $wpdb->get_results( // phpcs:ignore
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_neonews_daily_views'"
        );

        $daily = array();
        // Build date range array.
        $cursor = strtotime( $from );
        $end    = strtotime( $to );
        while ( $cursor <= $end ) {
            $daily[ gmdate( 'Y-m-d', $cursor ) ] = 0;
            $cursor += DAY_IN_SECONDS;
        }

        foreach ( $rows as $row ) {
            $data = maybe_unserialize( $row->meta_value );
            if ( ! is_array( $data ) ) { continue; }
            foreach ( $data as $date => $count ) {
                if ( isset( $daily[ $date ] ) ) {
                    $daily[ $date ] += (int) $count;
                }
            }
        }

        return array(
            'labels' => array_keys( $daily ),
            'data'   => array_values( $daily ),
        );
    }

    /**
     * Top 10 most viewed posts in range.
     */
    private static function get_top_posts( $from, $to, $days ) {
        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'meta_key'       => '_neonews_post_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ) );

        $result = array();
        foreach ( $posts as $post ) {
            $period_views = NeoNews_View_Counter::get_views_for_period( $post->ID, $days );
            if ( $period_views < 1 ) { continue; }
            $result[] = array(
                'id'       => $post->ID,
                'title'    => wp_strip_all_tags( get_the_title( $post ) ),
                'url'      => get_permalink( $post ),
                'views'    => (int) NeoNews_View_Counter::get_views( $post->ID ),
                'period_views' => $period_views,
                'date'     => get_the_date( 'M j, Y', $post ),
                'category' => implode( ', ', wp_get_post_terms( $post->ID, 'category', array( 'fields' => 'names' ) ) ),
                'thumb'    => get_the_post_thumbnail_url( $post->ID, array( 48, 48 ) ) ?: '',
            );
            if ( count( $result ) >= 10 ) { break; }
        }

        // Sort by period views desc.
        usort( $result, function( $a, $b ) { return $b['period_views'] - $a['period_views']; } );

        return $result;
    }

    /**
     * Recent user activity feed.
     */
    private static function get_activity_feed( $from, $to ) {
        if ( ! class_exists( 'NeoNews_User_Activity_Log' ) ) {
            return array();
        }

        $result = NeoNews_User_Activity_Log::query( array(
            'date_from' => $from . ' 00:00:00',
            'date_to'   => $to . ' 23:59:59',
            'per_page'  => 20,
            'page'      => 1,
        ) );

        $feed = array();
        foreach ( $result['items'] as $row ) {
            $feed[] = array(
                'event'   => $row->event_type,
                'message' => $row->message,
                'user'    => $row->user_login,
                'time'    => human_time_diff( strtotime( $row->created_at ), current_time( 'timestamp' ) ) . ' ago',
                'date'    => date_i18n( 'M j, g:i a', strtotime( $row->created_at ) ),
            );
        }

        return $feed;
    }

    /**
     * User registrations per day for chart.
     */
    private static function get_user_growth( $from, $to, $days ) {
        global $wpdb;

        $rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
            "SELECT DATE(user_registered) as reg_date, COUNT(*) as cnt
             FROM {$wpdb->users}
             WHERE DATE(user_registered) BETWEEN %s AND %s
             GROUP BY DATE(user_registered)
             ORDER BY reg_date ASC",
            $from, $to
        ) );

        $daily = array();
        $cursor = strtotime( $from );
        $end    = strtotime( $to );
        while ( $cursor <= $end ) {
            $daily[ gmdate( 'Y-m-d', $cursor ) ] = 0;
            $cursor += DAY_IN_SECONDS;
        }
        foreach ( $rows as $row ) {
            if ( isset( $daily[ $row->reg_date ] ) ) {
                $daily[ $row->reg_date ] = (int) $row->cnt;
            }
        }

        return array(
            'labels' => array_keys( $daily ),
            'data'   => array_values( $daily ),
        );
    }

    /**
     * Posts split by category (top 8).
     */
    private static function get_category_split() {
        $cats   = get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'hide_empty' => true ) );
        $labels = array();
        $data   = array();
        foreach ( $cats as $cat ) {
            $labels[] = $cat->name;
            $data[]   = (int) $cat->count;
        }
        return array( 'labels' => $labels, 'data' => $data );
    }

    /**
     * Device type split from user-agent stored in activity log.
     */
    private static function get_device_split( $from, $to ) {
        global $wpdb;

        $table = $wpdb->prefix . NeoNews_User_Activity_Log::TABLE;

        // Check table exists.
        if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) { // phpcs:ignore
            return array( 'labels' => array( 'Unknown' ), 'data' => array( 1 ) );
        }

        $rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
            "SELECT user_agent FROM {$table}
             WHERE DATE(created_at) BETWEEN %s AND %s
             AND user_agent != ''",
            $from, $to
        ) );

        $mobile  = 0;
        $tablet  = 0;
        $desktop = 0;

        foreach ( $rows as $row ) {
            $ua = strtolower( $row->user_agent );
            if ( preg_match( '/ipad|tablet|playbook|kindle|silk/', $ua ) ) {
                $tablet++;
            } elseif ( preg_match( '/mobile|android|iphone|ipod|blackberry|windows phone/', $ua ) ) {
                $mobile++;
            } else {
                $desktop++;
            }
        }

        $total = $mobile + $tablet + $desktop;
        if ( $total < 1 ) {
            return array( 'labels' => array( 'Desktop', 'Mobile', 'Tablet' ), 'data' => array( 0, 0, 0 ) );
        }

        return array(
            'labels' => array( 'Desktop', 'Mobile', 'Tablet' ),
            'data'   => array( $desktop, $mobile, $tablet ),
            'pct'    => array(
                round( $desktop / $total * 100 ),
                round( $mobile  / $total * 100 ),
                round( $tablet  / $total * 100 ),
            ),
        );
    }

    /**
     * Sum of all post views within a date range using _neonews_daily_views meta.
     */
    private static function sum_views_in_range( $from, $to ) {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_neonews_daily_views'"
        );

        $total = 0;
        foreach ( $rows as $row ) {
            $data = maybe_unserialize( $row->meta_value );
            if ( ! is_array( $data ) ) { continue; }
            foreach ( $data as $date => $count ) {
                if ( $date >= $from && $date <= $to ) {
                    $total += (int) $count;
                }
            }
        }
        return $total;
    }

    /* ── Render ───────────────────────────────────────────────────────────── */

    public static function render() {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        ?>
        <div class="wrap nn-dash-wrap" id="nn-analytics-dashboard">

            <!-- Header -->
            <div class="nn-dash-header">
                <div class="nn-dash-title">
                    <span class="nn-dash-logo">📊</span>
                    <div>
                        <h1><?php esc_html_e( 'Analytics Dashboard', 'neonews-core' ); ?></h1>
                        <p class="nn-dash-subtitle"><?php esc_html_e( 'Real-time insights for your news platform', 'neonews-core' ); ?></p>
                    </div>
                </div>

                <!-- Date range controls -->
                <div class="nn-dash-controls">
                    <div class="nn-range-pills" id="nn-range-pills">
                        <button class="nn-pill active" data-range="7"><?php esc_html_e( '7 Days', 'neonews-core' ); ?></button>
                        <button class="nn-pill" data-range="14"><?php esc_html_e( '14 Days', 'neonews-core' ); ?></button>
                        <button class="nn-pill" data-range="30"><?php esc_html_e( '30 Days', 'neonews-core' ); ?></button>
                        <button class="nn-pill" data-range="90"><?php esc_html_e( '90 Days', 'neonews-core' ); ?></button>
                        <button class="nn-pill" data-range="custom"><?php esc_html_e( 'Custom', 'neonews-core' ); ?></button>
                    </div>
                    <div class="nn-custom-range" id="nn-custom-range" style="display:none;">
                        <input type="date" id="nn-date-from" placeholder="From">
                        <span>–</span>
                        <input type="date" id="nn-date-to" placeholder="To">
                        <button class="nn-pill nn-apply-range"><?php esc_html_e( 'Apply', 'neonews-core' ); ?></button>
                    </div>
                    <button class="nn-refresh-btn" id="nn-refresh" title="Refresh">↺</button>
                </div>
            </div>

            <!-- Loading overlay -->
            <div class="nn-dash-loading" id="nn-dash-loading">
                <div class="nn-spinner"></div>
                <p><?php esc_html_e( 'Loading data…', 'neonews-core' ); ?></p>
            </div>

            <!-- Stat cards -->
            <div class="nn-stat-cards" id="nn-stat-cards"></div>

            <!-- Charts row -->
            <div class="nn-charts-row">
                <div class="nn-chart-card nn-chart-wide">
                    <div class="nn-chart-header">
                        <h3><?php esc_html_e( 'Daily Views', 'neonews-core' ); ?></h3>
                        <span class="nn-chart-badge" id="nn-views-total-badge"></span>
                    </div>
                    <canvas id="nn-chart-views" height="80"></canvas>
                </div>
                <div class="nn-chart-card">
                    <div class="nn-chart-header">
                        <h3><?php esc_html_e( 'User Registrations', 'neonews-core' ); ?></h3>
                    </div>
                    <canvas id="nn-chart-users" height="120"></canvas>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="nn-bottom-row">

                <!-- Top Posts -->
                <div class="nn-card nn-card-top-posts">
                    <div class="nn-card-header">
                        <h3>🏆 <?php esc_html_e( 'Top Articles', 'neonews-core' ); ?></h3>
                        <span class="nn-card-sub"><?php esc_html_e( 'By views in selected period', 'neonews-core' ); ?></span>
                    </div>
                    <div id="nn-top-posts"></div>
                </div>

                <!-- Donut charts -->
                <div class="nn-card nn-card-donuts">
                    <div class="nn-card-header">
                        <h3>📂 <?php esc_html_e( 'Content by Category', 'neonews-core' ); ?></h3>
                    </div>
                    <canvas id="nn-chart-cats" height="160"></canvas>

                    <div class="nn-card-header" style="margin-top:24px;">
                        <h3>📱 <?php esc_html_e( 'Devices', 'neonews-core' ); ?></h3>
                    </div>
                    <canvas id="nn-chart-devices" height="120"></canvas>
                    <div id="nn-device-labels" class="nn-device-labels"></div>
                </div>

                <!-- Activity feed -->
                <div class="nn-card nn-card-activity">
                    <div class="nn-card-header">
                        <h3>🕐 <?php esc_html_e( 'Recent Activity', 'neonews-core' ); ?></h3>
                        <span class="nn-card-sub"><?php esc_html_e( 'Latest user events', 'neonews-core' ); ?></span>
                    </div>
                    <div id="nn-activity-feed"></div>
                </div>

            </div><!-- .nn-bottom-row -->

        </div><!-- .nn-dash-wrap -->
        <?php
    }

    /* ── CSS ──────────────────────────────────────────────────────────────── */

    private static function get_css() {
        return '
        /* ── Layout ── */
        .nn-dash-wrap { max-width:1400px; padding-bottom:40px; }

        .nn-dash-header {
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:16px;
            background:#1d3557; color:#fff;
            padding:20px 24px; border-radius:10px; margin-bottom:24px;
        }
        .nn-dash-title { display:flex; align-items:center; gap:14px; }
        .nn-dash-logo  { font-size:36px; line-height:1; }
        .nn-dash-title h1 { color:#fff !important; margin:0; font-size:1.5rem; }
        .nn-dash-subtitle { margin:2px 0 0; opacity:.75; font-size:13px; }

        /* Controls */
        .nn-dash-controls { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .nn-range-pills    { display:flex; gap:6px; flex-wrap:wrap; }
        .nn-pill {
            padding:6px 14px; border-radius:20px; border:1.5px solid rgba(255,255,255,.4);
            background:transparent; color:#fff; font-size:12px; font-weight:600;
            cursor:pointer; transition:background .15s,border-color .15s;
        }
        .nn-pill:hover, .nn-pill.active {
            background:#e63946; border-color:#e63946;
        }
        .nn-apply-range { background:#e63946; border-color:#e63946; }
        .nn-custom-range {
            display:flex; align-items:center; gap:8px; flex-wrap:wrap;
        }
        .nn-custom-range input[type=date] {
            padding:5px 10px; border-radius:6px; border:1px solid rgba(255,255,255,.4);
            background:rgba(255,255,255,.1); color:#fff; font-size:12px;
            color-scheme:dark;
        }
        .nn-refresh-btn {
            background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.3);
            color:#fff; border-radius:50%; width:34px; height:34px;
            font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:background .15s;
        }
        .nn-refresh-btn:hover { background:rgba(255,255,255,.25); }

        /* Loading */
        .nn-dash-loading {
            display:none; flex-direction:column; align-items:center;
            justify-content:center; padding:60px; gap:16px; color:#666;
        }
        .nn-dash-loading.nn-show { display:flex; }
        .nn-spinner {
            width:36px; height:36px; border:4px solid #e0e0e0;
            border-top-color:#1d3557; border-radius:50%;
            animation:nn-spin .8s linear infinite;
        }
        @keyframes nn-spin { to { transform:rotate(360deg); } }

        /* ── Stat Cards ── */
        .nn-stat-cards {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
            gap:14px; margin-bottom:24px;
        }
        .nn-stat-card {
            background:#fff; border-radius:10px;
            border:1px solid #e8eaed;
            padding:16px 18px;
            display:flex; flex-direction:column; gap:6px;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
            transition:transform .15s, box-shadow .15s;
        }
        .nn-stat-card:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
        .nn-stat-icon { font-size:22px; line-height:1; }
        .nn-stat-value { font-size:1.6rem; font-weight:800; line-height:1; }
        .nn-stat-label { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; font-weight:600; }

        /* ── Charts Row ── */
        .nn-charts-row {
            display:grid;
            grid-template-columns:2fr 1fr;
            gap:16px; margin-bottom:16px;
        }
        .nn-chart-card {
            background:#fff; border-radius:10px; border:1px solid #e8eaed;
            padding:20px 20px 16px;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
        }
        .nn-chart-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .nn-chart-header h3 { margin:0; font-size:14px; font-weight:700; color:#1d3557; }
        .nn-chart-badge {
            background:#f0f4f8; color:#1d3557; font-size:11px;
            font-weight:700; padding:3px 10px; border-radius:10px;
        }

        /* ── Bottom Row ── */
        .nn-bottom-row {
            display:grid;
            grid-template-columns:2fr 1fr 1.2fr;
            gap:16px;
        }
        .nn-card {
            background:#fff; border-radius:10px; border:1px solid #e8eaed;
            padding:20px;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
            overflow:hidden;
        }
        .nn-card-header { margin-bottom:14px; }
        .nn-card-header h3 { margin:0 0 2px; font-size:14px; font-weight:700; color:#1d3557; }
        .nn-card-sub { font-size:11px; color:#888; }

        /* Top posts list */
        .nn-top-post {
            display:flex; align-items:center; gap:10px;
            padding:9px 0; border-bottom:1px solid #f4f4f4;
        }
        .nn-top-post:last-child { border-bottom:none; }
        .nn-top-rank {
            width:22px; height:22px; border-radius:50%;
            background:#1d3557; color:#fff;
            font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center;
            flex-shrink:0;
        }
        .nn-top-rank.gold   { background:#f4a261; }
        .nn-top-rank.silver { background:#adb5bd; }
        .nn-top-rank.bronze { background:#c77c40; }
        .nn-top-thumb {
            width:36px; height:36px; border-radius:5px; object-fit:cover; flex-shrink:0;
        }
        .nn-top-thumb-placeholder {
            width:36px; height:36px; border-radius:5px; background:#e9ecef;
            flex-shrink:0; display:flex; align-items:center; justify-content:center;
            font-size:16px;
        }
        .nn-top-info { flex:1; min-width:0; }
        .nn-top-title {
            font-size:12px; font-weight:600; color:#222;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
            display:block; text-decoration:none;
        }
        .nn-top-title:hover { color:#e63946; }
        .nn-top-meta { font-size:11px; color:#888; margin-top:1px; }
        .nn-top-views {
            font-size:12px; font-weight:700; color:#1d3557;
            white-space:nowrap; text-align:right;
        }
        .nn-top-views small { display:block; font-size:10px; color:#aaa; font-weight:400; }

        /* Activity feed */
        .nn-activity-item {
            display:flex; gap:10px; padding:8px 0;
            border-bottom:1px solid #f4f4f4; align-items:flex-start;
        }
        .nn-activity-item:last-child { border-bottom:none; }
        .nn-activity-icon {
            width:30px; height:30px; border-radius:50%;
            background:#f0f4f8; display:flex; align-items:center;
            justify-content:center; font-size:13px; flex-shrink:0;
        }
        .nn-activity-body { flex:1; min-width:0; }
        .nn-activity-msg  { font-size:12px; color:#333; font-weight:500; }
        .nn-activity-meta { font-size:11px; color:#aaa; margin-top:1px; }
        .nn-activity-empty { color:#aaa; font-size:13px; text-align:center; padding:24px 0; }

        /* Device labels */
        .nn-device-labels { display:flex; gap:12px; flex-wrap:wrap; margin-top:10px; justify-content:center; }
        .nn-device-label  { font-size:11px; color:#555; display:flex; align-items:center; gap:5px; }
        .nn-device-dot    { width:10px; height:10px; border-radius:50%; }

        /* ── Responsive ── */
        @media (max-width:1100px) {
            .nn-bottom-row { grid-template-columns:1fr 1fr; }
            .nn-card-top-posts { grid-column:1/-1; }
        }
        @media (max-width:820px) {
            .nn-charts-row { grid-template-columns:1fr; }
            .nn-bottom-row { grid-template-columns:1fr; }
            .nn-dash-header { flex-direction:column; align-items:flex-start; }
            .nn-stat-cards { grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); }
        }
        @media (max-width:500px) {
            .nn-stat-cards { grid-template-columns:1fr 1fr; }
            .nn-range-pills { gap:4px; }
            .nn-pill { padding:5px 10px; font-size:11px; }
        }
        ';
    }

    /* ── JS ───────────────────────────────────────────────────────────────── */

    private static function get_js() {
        return <<<'JS'
(function(){
    var charts = {};
    var currentRange = '7';
    var customFrom = '', customTo = '';

    var EVENT_ICONS = {
        login:'🔐', logout:'🚪', post_read:'📖', post_like:'❤️',
        post_share:'🔗', comment:'💬', article_submit:'✍️',
        profile_update:'👤', admin_action:'⚙️'
    };

    function el(id){ return document.getElementById(id); }

    function showLoading(yes){
        el('nn-dash-loading').classList.toggle('nn-show', yes);
        var cards = el('nn-stat-cards');
        if(cards) cards.style.opacity = yes ? '.3' : '1';
    }

    function loadData(){
        showLoading(true);
        var fd = new FormData();
        fd.append('action', 'nn_dash_data');
        fd.append('nonce',  nnDash.nonce);
        fd.append('range',  currentRange);
        if(customFrom){ fd.append('date_from', customFrom); }
        if(customTo)  { fd.append('date_to',   customTo);   }

        fetch(nnDash.ajaxUrl, { method:'POST', body:fd, credentials:'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(res){
                showLoading(false);
                if(!res.success){ return; }
                var d = res.data;
                renderStatCards(d.stat_cards);
                renderViewsChart(d.views_chart);
                renderUsersChart(d.user_growth);
                renderTopPosts(d.top_posts);
                renderActivityFeed(d.activity_feed);
                renderCategoryChart(d.category_split);
                renderDeviceChart(d.device_split);
            })
            .catch(function(){ showLoading(false); });
    }

    /* Stat cards */
    function renderStatCards(cards){
        var wrap = el('nn-stat-cards');
        if(!wrap || !cards) return;
        wrap.innerHTML = cards.map(function(c){
            return '<div class="nn-stat-card" style="border-top:3px solid '+c.color+'">'
                + '<span class="nn-stat-icon">'+c.icon+'</span>'
                + '<span class="nn-stat-value" style="color:'+c.color+'">'+c.value+'</span>'
                + '<span class="nn-stat-label">'+c.label+'</span>'
                + '</div>';
        }).join('');
    }

    /* Line chart helper */
    function makeLineChart(id, labels, data, label, color){
        var ctx = el(id);
        if(!ctx) return;
        if(charts[id]){ charts[id].destroy(); }
        charts[id] = new Chart(ctx, {
            type:'line',
            data:{
                labels: labels.map(function(d){ return d.slice(5); }), // MM-DD
                datasets:[{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: color.replace(')',', 0.08)').replace('rgb','rgba'),
                    borderWidth:2, pointRadius:3, pointHoverRadius:5,
                    tension:0.3, fill:true
                }]
            },
            options:{
                responsive:true,
                plugins:{ legend:{display:false}, tooltip:{mode:'index',intersect:false} },
                scales:{
                    x:{ grid:{display:false}, ticks:{font:{size:10}} },
                    y:{ beginAtZero:true, ticks:{font:{size:10}, precision:0} }
                }
            }
        });
    }

    function renderViewsChart(d){
        if(!d) return;
        var total = d.data.reduce(function(a,b){return a+b;},0);
        var badge = el('nn-views-total-badge');
        if(badge) badge.textContent = total.toLocaleString() + ' total views';
        makeLineChart('nn-chart-views', d.labels, d.data, 'Views', 'rgb(29,53,87)');
    }

    function renderUsersChart(d){
        if(!d) return;
        makeLineChart('nn-chart-users', d.labels, d.data, 'Registrations', 'rgb(45,106,79)');
    }

    /* Top posts */
    function renderTopPosts(posts){
        var wrap = el('nn-top-posts');
        if(!wrap) return;
        if(!posts || !posts.length){
            wrap.innerHTML = '<p style="color:#aaa;text-align:center;padding:24px 0;">No data for this period.</p>';
            return;
        }
        var rankClass = ['gold','silver','bronze'];
        wrap.innerHTML = posts.map(function(p,i){
            var thumb = p.thumb
                ? '<img src="'+p.thumb+'" class="nn-top-thumb" alt="" loading="lazy">'
                : '<div class="nn-top-thumb-placeholder">📰</div>';
            var rc = rankClass[i] || '';
            return '<div class="nn-top-post">'
                + '<span class="nn-top-rank '+rc+'">'+(i+1)+'</span>'
                + thumb
                + '<div class="nn-top-info">'
                +   '<a href="'+p.url+'" class="nn-top-title" target="_blank" title="'+p.title+'">'+p.title+'</a>'
                +   '<span class="nn-top-meta">'+p.category+' · '+p.date+'</span>'
                + '</div>'
                + '<div class="nn-top-views">'
                +   p.period_views.toLocaleString()
                +   '<small>total: '+p.views.toLocaleString()+'</small>'
                + '</div>'
                + '</div>';
        }).join('');
    }

    /* Activity feed */
    function renderActivityFeed(items){
        var wrap = el('nn-activity-feed');
        if(!wrap) return;
        if(!items || !items.length){
            wrap.innerHTML = '<div class="nn-activity-empty">No activity recorded in this period.</div>';
            return;
        }
        wrap.innerHTML = items.map(function(a){
            var icon = EVENT_ICONS[a.event] || '📌';
            return '<div class="nn-activity-item">'
                + '<div class="nn-activity-icon">'+icon+'</div>'
                + '<div class="nn-activity-body">'
                +   '<div class="nn-activity-msg">'+a.message+'</div>'
                +   '<div class="nn-activity-meta">@'+a.user+' · '+a.time+'</div>'
                + '</div>'
                + '</div>';
        }).join('');
    }

    /* Donut charts */
    function makeDonut(id, labels, data, colors){
        var ctx = el(id);
        if(!ctx) return;
        if(charts[id]){ charts[id].destroy(); }
        charts[id] = new Chart(ctx, {
            type:'doughnut',
            data:{
                labels: labels,
                datasets:[{ data:data, backgroundColor:colors, borderWidth:2, borderColor:'#fff' }]
            },
            options:{
                responsive:true,
                plugins:{
                    legend:{ position:'bottom', labels:{ font:{size:11}, padding:10, boxWidth:12 } },
                    tooltip:{ callbacks:{ label:function(c){ return ' '+c.label+': '+c.formattedValue; } } }
                },
                cutout:'60%'
            }
        });
    }

    var CAT_COLORS = ['#1d3557','#457b9d','#e63946','#2d6a4f','#f4a261','#6a4c93','#52b788','#e76f51'];

    function renderCategoryChart(d){
        if(!d) return;
        makeDonut('nn-chart-cats', d.labels, d.data, CAT_COLORS);
    }

    var DEV_COLORS = ['#1d3557','#e63946','#f4a261'];

    function renderDeviceChart(d){
        if(!d) return;
        makeDonut('nn-chart-devices', d.labels, d.data, DEV_COLORS);
        var labelsWrap = el('nn-device-labels');
        if(labelsWrap && d.pct){
            labelsWrap.innerHTML = d.labels.map(function(l,i){
                return '<span class="nn-device-label">'
                    + '<span class="nn-device-dot" style="background:'+DEV_COLORS[i]+'"></span>'
                    + l + ' ' + (d.pct[i]||0) + '%'
                    + '</span>';
            }).join('');
        }
    }

    /* Controls */
    document.addEventListener('DOMContentLoaded', function(){

        // Range pills.
        document.querySelectorAll('#nn-range-pills .nn-pill').forEach(function(btn){
            btn.addEventListener('click', function(){
                var range = this.dataset.range;
                if(range === 'custom'){
                    el('nn-custom-range').style.display = 'flex';
                    document.querySelectorAll('#nn-range-pills .nn-pill').forEach(function(b){ b.classList.remove('active'); });
                    this.classList.add('active');
                    return;
                }
                el('nn-custom-range').style.display = 'none';
                document.querySelectorAll('#nn-range-pills .nn-pill').forEach(function(b){ b.classList.remove('active'); });
                this.classList.add('active');
                currentRange = range;
                customFrom = ''; customTo = '';
                loadData();
            });
        });

        // Custom range apply.
        var applyBtn = document.querySelector('.nn-apply-range');
        if(applyBtn){
            applyBtn.addEventListener('click', function(){
                customFrom = el('nn-date-from').value;
                customTo   = el('nn-date-to').value;
                if(!customFrom || !customTo){ return; }
                currentRange = 'custom';
                loadData();
            });
        }

        // Refresh.
        var refreshBtn = el('nn-refresh');
        if(refreshBtn){
            refreshBtn.addEventListener('click', function(){
                this.style.transform = 'rotate(360deg)';
                this.style.transition = 'transform 0.5s';
                setTimeout(function(){ if(refreshBtn){ refreshBtn.style.transform=''; } }, 500);
                loadData();
            });
        }

        // Initial load.
        loadData();
    });
})();
JS;
    }
}
