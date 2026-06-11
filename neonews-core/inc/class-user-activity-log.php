<?php
/**
 * Central user activity log (database-backed).
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * User activity logger.
 */
class NeoNews_User_Activity_Log {

    const TABLE = 'neonews_user_activity';

    /**
     * @var NeoNews_User_Activity_Log|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return NeoNews_User_Activity_Log
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        if ( ! self::is_enabled() ) {
            return;
        }

        add_action( 'wp_login', array( $this, 'on_login' ), 10, 2 );
        add_action( 'wp_logout', array( $this, 'on_logout' ) );
        add_action( 'comment_post', array( $this, 'on_comment' ), 10, 3 );
        add_action( 'profile_update', array( $this, 'on_profile_update' ), 10, 2 );
    }

    /**
     * Is logging enabled?
     *
     * @return bool
     */
    public static function is_enabled() {
        return (bool) NeoNews_Core::get_settings( 'enable_activity_tracking' );
    }

    /**
     * Create DB table.
     */
    public static function create_table() {
        global $wpdb;

        $table   = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            user_login varchar(60) NOT NULL DEFAULT '',
            event_type varchar(32) NOT NULL DEFAULT '',
            object_id bigint(20) unsigned NOT NULL DEFAULT 0,
            message text NOT NULL,
            context longtext NULL,
            ip_hash varchar(64) NOT NULL DEFAULT '',
            user_agent varchar(255) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY object_id (object_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Ensure table exists.
     */
    public static function maybe_upgrade() {
        $ver = get_option( 'neonews_user_activity_db_version', '' );
        if ( '1.0' !== $ver ) {
            self::create_table();
            update_option( 'neonews_user_activity_db_version', '1.0' );
        }
    }

    /**
     * Event type labels.
     *
     * @return array
     */
    public static function get_event_types() {
        return array(
            'login'          => __( 'Login', 'neonews-core' ),
            'logout'         => __( 'Logout', 'neonews-core' ),
            'post_read'      => __( 'Read article', 'neonews-core' ),
            'article_submit' => __( 'Submitted article', 'neonews-core' ),
            'profile_update' => __( 'Profile updated', 'neonews-core' ),
            'profile_photo'  => __( 'Profile photo', 'neonews-core' ),
            'post_like'      => __( 'Liked article', 'neonews-core' ),
            'post_share'     => __( 'Shared article', 'neonews-core' ),
            'comment'        => __( 'Posted comment', 'neonews-core' ),
            'admin_action'   => __( 'Admin action', 'neonews-core' ),
        );
    }

    /**
     * Log an activity event.
     *
     * @param string $event_type Event slug.
     * @param string $message    Human-readable message.
     * @param int    $object_id  Related post/user ID.
     * @param array  $context    Extra data.
     * @param int    $user_id    User ID (0 = current).
     * @return int|false Insert ID.
     */
    public static function log( $event_type, $message, $object_id = 0, $context = array(), $user_id = 0 ) {
        if ( ! self::is_enabled() ) {
            return false;
        }

        global $wpdb;

        self::maybe_upgrade();

        $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
        $user    = $user_id ? get_userdata( $user_id ) : null;
        $login   = $user ? $user->user_login : ( $user_id ? 'user-' . $user_id : 'guest' );

        $context_json = ! empty( $context ) ? wp_json_encode( $context ) : '';

        $result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . self::TABLE,
            array(
                'user_id'    => $user_id,
                'user_login' => sanitize_user( $login, true ),
                'event_type' => sanitize_key( $event_type ),
                'object_id'  => absint( $object_id ),
                'message'    => sanitize_text_field( $message ),
                'context'    => $context_json,
                'ip_hash'    => self::hash_ip(),
                'user_agent' => self::truncate_agent(),
                'created_at' => current_time( 'mysql', true ),
            ),
            array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( false === $result ) {
            return false;
        }

        $insert_id = (int) $wpdb->insert_id;

        /**
         * Fires after an activity row is stored.
         *
         * @param int    $insert_id  Row ID.
         * @param string $event_type Event slug.
         * @param int    $user_id    User ID.
         * @param array  $context    Context array.
         */
        do_action( 'neonews_user_activity_logged', $insert_id, $event_type, $user_id, $context );

        self::maybe_prune();

        return $insert_id;
    }

    /**
     * Query logs with filters.
     *
     * @param array $args Query args.
     * @return array{items: array, total: int}
     */
    public static function query( $args = array() ) {
        global $wpdb;

        self::maybe_upgrade();

        $defaults = array(
            'user_id'    => 0,
            'event_type' => '',
            'search'     => '',
            'date_from'  => '',
            'date_to'    => '',
            'per_page'   => 30,
            'page'       => 1,
        );

        $args  = wp_parse_args( $args, $defaults );
        $table = $wpdb->prefix . self::TABLE;
        $where = array( '1=1' );
        $params = array();

        if ( ! empty( $args['user_id'] ) ) {
            $where[]  = 'user_id = %d';
            $params[] = absint( $args['user_id'] );
        }

        if ( ! empty( $args['event_type'] ) ) {
            $where[]  = 'event_type = %s';
            $params[] = sanitize_key( $args['event_type'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $where[]  = '(message LIKE %s OR user_login LIKE %s)';
            $like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ( ! empty( $args['date_from'] ) ) {
            $where[]  = 'created_at >= %s';
            $params[] = gmdate( 'Y-m-d 00:00:00', strtotime( $args['date_from'] ) );
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[]  = 'created_at <= %s';
            $params[] = gmdate( 'Y-m-d 23:59:59', strtotime( $args['date_to'] ) );
        }

        $where_sql = implode( ' AND ', $where );

        if ( $params ) {
            $count_sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params ); // phpcs:ignore WordPress.DB.PreparedSQL
            $total     = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        } else {
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        }

        $per_page = max( 10, min( 100, absint( $args['per_page'] ) ) );
        $page     = max( 1, absint( $args['page'] ) );
        $offset   = ( $page - 1 ) * $per_page;

        $query_params = $params;
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";

        if ( $query_params ) {
            $items = $wpdb->get_results( $wpdb->prepare( $sql, $query_params ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
        } else {
            $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        }

        foreach ( $items as &$row ) {
            if ( ! empty( $row['context'] ) ) {
                $row['context'] = json_decode( $row['context'], true );
            }
            if ( ! empty( $row['object_id'] ) && in_array( $row['event_type'], array( 'post_read', 'post_like', 'post_share', 'article_submit', 'comment' ), true ) ) {
                $row['object_title'] = get_the_title( (int) $row['object_id'] );
                $row['object_url']   = get_permalink( (int) $row['object_id'] );
            }
        }

        return array(
            'items' => $items ? $items : array(),
            'total' => $total,
            'pages' => (int) ceil( $total / $per_page ),
            'page'  => $page,
        );
    }

    /**
     * Summary stats for dashboard widgets.
     *
     * @param int $days Days back.
     * @return array
     */
    public static function get_stats( $days = 7 ) {
        global $wpdb;

        self::maybe_upgrade();

        $table = $wpdb->prefix . self::TABLE;
        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $since ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        $by_type = $wpdb->get_results( $wpdb->prepare( "SELECT event_type, COUNT(*) as cnt FROM {$table} WHERE created_at >= %s GROUP BY event_type ORDER BY cnt DESC", $since ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        $active_users = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$table} WHERE created_at >= %s AND user_id > 0", $since ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return array(
            'total_events'  => $total,
            'active_users'  => $active_users,
            'by_type'       => $by_type ? $by_type : array(),
            'days'          => $days,
        );
    }

    /**
     * Get the most recent log rows.
     *
     * @param int $limit Max rows.
     * @return array
     */
    public static function get_recent( $limit = 10 ) {
        $result = self::query(
            array(
                'per_page' => max( 1, min( 50, absint( $limit ) ) ),
                'page'     => 1,
            )
        );

        return $result['items'];
    }

    /**
     * Count matching events within a time window.
     *
     * @param array $args user_id, event_type, ip_hash, minutes.
     * @return int
     */
    public static function count_recent_events( $args = array() ) {
        global $wpdb;

        self::maybe_upgrade();

        $defaults = array(
            'user_id'    => 0,
            'event_type' => '',
            'ip_hash'    => '',
            'minutes'    => 15,
        );

        $args    = wp_parse_args( $args, $defaults );
        $table   = $wpdb->prefix . self::TABLE;
        $where   = array( 'created_at >= %s' );
        $params  = array( gmdate( 'Y-m-d H:i:s', strtotime( '-' . absint( $args['minutes'] ) . ' minutes' ) ) );

        if ( ! empty( $args['user_id'] ) ) {
            $where[]  = 'user_id = %d';
            $params[] = absint( $args['user_id'] );
        }

        if ( ! empty( $args['event_type'] ) ) {
            $where[]  = 'event_type = %s';
            $params[] = sanitize_key( $args['event_type'] );
        }

        if ( ! empty( $args['ip_hash'] ) ) {
            $where[]  = 'ip_hash = %s';
            $params[] = sanitize_text_field( $args['ip_hash'] );
        }

        $where_sql = implode( ' AND ', $where );

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
    }

    /**
     * Get current request IP hash (same algorithm as stored rows).
     *
     * @return string
     */
    public static function get_current_ip_hash() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $ip = trim( explode( ',', $ip )[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return md5( $ip . wp_salt( 'nonce' ) . gmdate( 'Y-m-d' ) );
    }

    /**
     * Format a row for REST/API output.
     *
     * @param array $row DB row.
     * @return array
     */
    public static function format_item( $row ) {
        $types = self::get_event_types();
        $item  = array(
            'id'         => (int) $row['id'],
            'user_id'    => (int) $row['user_id'],
            'user_login' => $row['user_login'],
            'event_type' => $row['event_type'],
            'event_label'=> $types[ $row['event_type'] ] ?? $row['event_type'],
            'object_id'  => (int) $row['object_id'],
            'message'    => $row['message'],
            'context'    => is_array( $row['context'] ?? null ) ? $row['context'] : ( ! empty( $row['context'] ) ? json_decode( $row['context'], true ) : null ),
            'created_at' => $row['created_at'],
        );

        if ( ! empty( $row['object_id'] ) && in_array( $row['event_type'], array( 'post_read', 'post_like', 'post_share', 'article_submit', 'comment' ), true ) ) {
            $item['object_title'] = get_the_title( (int) $row['object_id'] );
            $item['object_url']   = get_permalink( (int) $row['object_id'] );
        }

        return $item;
    }

    /**
     * Login hook.
     *
     * @param string  $user_login Username.
     * @param WP_User $user       User.
     */
    public function on_login( $user_login, $user ) {
        self::log(
            'login',
            sprintf(
                /* translators: %s: username */
                __( 'User logged in: %s', 'neonews-core' ),
                $user_login
            ),
            0,
            array( 'role' => ! empty( $user->roles[0] ) ? $user->roles[0] : '' ),
            $user->ID
        );
    }

    /**
     * Logout hook.
     */
    public function on_logout() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }
        $user = get_userdata( $user_id );
        self::log(
            'logout',
            sprintf(
                /* translators: %s: username */
                __( 'User logged out: %s', 'neonews-core' ),
                $user ? $user->user_login : $user_id
            ),
            0,
            array(),
            $user_id
        );
    }

    /**
     * Comment hook.
     *
     * @param int    $comment_id Comment ID.
     * @param int    $approved   Approved.
     * @param array  $commentdata Comment data.
     */
    public function on_comment( $comment_id, $approved, $commentdata ) {
        $post_id = isset( $commentdata['comment_post_ID'] ) ? (int) $commentdata['comment_post_ID'] : 0;
        $user_id = isset( $commentdata['user_id'] ) ? (int) $commentdata['user_id'] : 0;
        if ( ! $user_id ) {
            return;
        }
        self::log(
            'comment',
            sprintf(
                /* translators: %s: post title */
                __( 'Commented on: %s', 'neonews-core' ),
                get_the_title( $post_id )
            ),
            $post_id,
            array( 'comment_id' => $comment_id, 'approved' => $approved ),
            $user_id
        );
    }

    /**
     * Profile update hook.
     *
     * @param int   $user_id User ID.
     * @param array $old     Old data.
     */
    public function on_profile_update( $user_id, $old ) {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }
        self::log(
            'profile_update',
            sprintf(
                /* translators: %s: username */
                __( 'Profile updated: %s', 'neonews-core' ),
                $user->user_login
            ),
            $user_id,
            array(),
            $user_id
        );
    }

    /**
     * Hash IP for privacy.
     *
     * @return string
     */
    private static function hash_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $ip = trim( explode( ',', $ip )[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return md5( $ip . wp_salt( 'nonce' ) . gmdate( 'Y-m-d' ) );
    }

    /**
     * Truncate user agent.
     *
     * @return string
     */
    private static function truncate_agent() {
        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return '';
        }
        return substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 );
    }

    /**
     * Prune old rows.
     */
    private static function maybe_prune() {
        $days = (int) apply_filters( 'neonews_user_activity_retention_days', 180 );
        if ( $days < 30 ) {
            $days = 30;
        }

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) )
            )
        );
    }
}

/**
 * Public helper to log user activity.
 *
 * @param string $event_type Event type.
 * @param string $message    Message.
 * @param int    $object_id  Object ID.
 * @param array  $context    Context.
 * @param int    $user_id    User ID.
 */
function neonews_log_user_activity( $event_type, $message, $object_id = 0, $context = array(), $user_id = 0 ) {
    NeoNews_User_Activity_Log::log( $event_type, $message, $object_id, $context, $user_id );
}
