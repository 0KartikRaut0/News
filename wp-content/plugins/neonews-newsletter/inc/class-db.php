<?php
/**
 * Database layer — creates and queries wp_neonews_subscribers.
 *
 * @package NeoNews_Newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Newsletter_DB {

    const TABLE = 'neonews_subscribers';

    /**
     * Full table name with prefix.
     */
    public static function table() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Create table on activation.
     */
    public static function install() {
        global $wpdb;

        $table      = self::table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email         VARCHAR(200)        NOT NULL,
            first_name    VARCHAR(100)        NOT NULL DEFAULT '',
            status        ENUM('pending','confirmed','unsubscribed') NOT NULL DEFAULT 'pending',
            confirm_token VARCHAR(64)         NOT NULL DEFAULT '',
            ip_address    VARCHAR(45)         NOT NULL DEFAULT '',
            subscribed_at DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
            confirmed_at  DATETIME                     DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY   email (email),
            KEY          status (status)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'neonews_newsletter_db_version', NN_NEWS_VERSION );
    }

    /**
     * Insert a new pending subscriber.
     *
     * @param string $email      Subscriber email.
     * @param string $first_name Optional first name.
     * @param string $ip         IP address.
     * @return int|false Inserted ID or false on failure / duplicate.
     */
    public static function add_subscriber( $email, $first_name = '', $ip = '' ) {
        global $wpdb;

        // Already exists?
        $existing = self::get_by_email( $email );
        if ( $existing ) {
            return (int) $existing->id;
        }

        $token = bin2hex( random_bytes( 32 ) );

        $result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            self::table(),
            array(
                'email'         => sanitize_email( $email ),
                'first_name'    => sanitize_text_field( $first_name ),
                'status'        => 'pending',
                'confirm_token' => $token,
                'ip_address'    => sanitize_text_field( $ip ),
                'subscribed_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Confirm subscription by token.
     *
     * @param string $token Confirmation token.
     * @return bool
     */
    public static function confirm_by_token( $token ) {
        global $wpdb;

        $token = sanitize_text_field( $token );

        $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT * FROM %i WHERE confirm_token = %s AND status = 'pending'",
                self::table(),
                $token
            )
        );

        if ( ! $row ) {
            return false;
        }

        $updated = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            self::table(),
            array(
                'status'       => 'confirmed',
                'confirmed_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $row->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        return (bool) $updated;
    }

    /**
     * Unsubscribe by token or email.
     *
     * @param string $token_or_email Token or email address.
     * @return bool
     */
    public static function unsubscribe( $token_or_email ) {
        global $wpdb;

        $field = is_email( $token_or_email ) ? 'email' : 'confirm_token';
        $value = sanitize_text_field( $token_or_email );

        $updated = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            self::table(),
            array( 'status' => 'unsubscribed' ),
            array( $field   => $value ),
            array( '%s' ),
            array( '%s' )
        );

        return (bool) $updated;
    }

    /**
     * Get subscriber by email.
     *
     * @param string $email Email address.
     * @return object|null
     */
    public static function get_by_email( $email ) {
        global $wpdb;

        return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                'SELECT * FROM %i WHERE email = %s LIMIT 1',
                self::table(),
                sanitize_email( $email )
            )
        );
    }

    /**
     * Get paginated list of subscribers.
     *
     * @param array $args Query args.
     * @return array { rows, total }
     */
    public static function get_subscribers( $args = array() ) {
        global $wpdb;

        $args = wp_parse_args( $args, array(
            'status'   => '',
            'search'   => '',
            'per_page' => 20,
            'page'     => 1,
            'orderby'  => 'subscribed_at',
            'order'    => 'DESC',
        ) );

        $table  = self::table();
        $where  = array( '1=1' );
        $params = array();

        if ( $args['status'] ) {
            $where[]  = 'status = %s';
            $params[] = $args['status'];
        }

        if ( $args['search'] ) {
            $where[]  = 'email LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        $where_sql = implode( ' AND ', $where );

        $allowed_orderby = array( 'id', 'email', 'status', 'subscribed_at', 'confirmed_at' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'subscribed_at';
        $order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

        $offset  = ( max( 1, (int) $args['page'] ) - 1 ) * (int) $args['per_page'];
        $limit   = (int) $args['per_page'];

        // Total count.
        if ( $params ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params ) );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", array_merge( $params, array( $limit, $offset ) ) ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $limit, $offset ) );
        }

        return array(
            'rows'  => $rows ?: array(),
            'total' => $total,
        );
    }

    /**
     * Get all confirmed subscribers (for CSV export).
     *
     * @return array
     */
    public static function get_all_confirmed() {
        global $wpdb;

        return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT id, email, first_name, subscribed_at, confirmed_at FROM %i WHERE status = 'confirmed' ORDER BY subscribed_at DESC",
                self::table()
            )
        ) ?: array();
    }

    /**
     * Count by status.
     *
     * @return array { pending, confirmed, unsubscribed, total }
     */
    public static function get_counts() {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                'SELECT status, COUNT(*) as cnt FROM %i GROUP BY status',
                self::table()
            )
        );

        $counts = array( 'pending' => 0, 'confirmed' => 0, 'unsubscribed' => 0 );
        foreach ( (array) $rows as $row ) {
            $counts[ $row->status ] = (int) $row->cnt;
        }
        $counts['total'] = array_sum( $counts );
        return $counts;
    }

    /**
     * Delete a subscriber by ID.
     *
     * @param int $id Subscriber ID.
     * @return bool
     */
    public static function delete( $id ) {
        global $wpdb;

        return (bool) $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            self::table(),
            array( 'id' => (int) $id ),
            array( '%d' )
        );
    }
}
