<?php
/**
 * REST API for user activity logs.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activity log REST routes.
 */
class NeoNews_Activity_Rest_Api {

    /**
     * @var NeoNews_Activity_Rest_Api|null
     */
    private static $instance = null;

    /**
     * API namespace.
     */
    const NAMESPACE = 'neonews/v1';

    /**
     * Get instance.
     *
     * @return NeoNews_Activity_Rest_Api
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
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Permission check.
     *
     * @return bool
     */
    public static function can_read() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        register_rest_route(
            self::NAMESPACE,
            '/activity',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_activity' ),
                    'permission_callback' => array( __CLASS__, 'can_read' ),
                    'args'                => $this->get_collection_args(),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/activity/stats',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_stats' ),
                    'permission_callback' => array( __CLASS__, 'can_read' ),
                    'args'                => array(
                        'days' => array(
                            'default'           => 7,
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/activity/types',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_types' ),
                    'permission_callback' => array( __CLASS__, 'can_read' ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/activity/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( __CLASS__, 'can_read' ),
                    'args'                => array(
                        'id' => array(
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Collection query args.
     *
     * @return array
     */
    private function get_collection_args() {
        return array(
            'user_id'    => array(
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ),
            'event_type' => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_key',
            ),
            'search'     => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_from'  => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'date_to'    => array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'page'       => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page'   => array(
                'default'           => 30,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * GET /activity
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_activity( WP_REST_Request $request ) {
        $result = NeoNews_User_Activity_Log::query(
            array(
                'user_id'    => $request->get_param( 'user_id' ),
                'event_type' => $request->get_param( 'event_type' ),
                'search'     => $request->get_param( 'search' ),
                'date_from'  => $request->get_param( 'date_from' ),
                'date_to'    => $request->get_param( 'date_to' ),
                'page'       => $request->get_param( 'page' ),
                'per_page'   => min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) ),
            )
        );

        $items = array_map( array( 'NeoNews_User_Activity_Log', 'format_item' ), $result['items'] );

        return rest_ensure_response(
            array(
                'items' => $items,
                'total' => $result['total'],
                'pages' => $result['pages'],
                'page'  => $result['page'],
            )
        );
    }

    /**
     * GET /activity/stats
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_stats( WP_REST_Request $request ) {
        $days  = max( 1, min( 90, (int) $request->get_param( 'days' ) ) );
        $stats = NeoNews_User_Activity_Log::get_stats( $days );
        $types = NeoNews_User_Activity_Log::get_event_types();

        foreach ( $stats['by_type'] as &$row ) {
            $row['label'] = $types[ $row['event_type'] ] ?? $row['event_type'];
        }

        return rest_ensure_response( $stats );
    }

    /**
     * GET /activity/types
     *
     * @return WP_REST_Response
     */
    public function get_types() {
        return rest_ensure_response( NeoNews_User_Activity_Log::get_event_types() );
    }

    /**
     * GET /activity/{id}
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function get_item( WP_REST_Request $request ) {
        global $wpdb;

        NeoNews_User_Activity_Log::maybe_upgrade();

        $id  = absint( $request->get_param( 'id' ) );
        $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . NeoNews_User_Activity_Log::TABLE . ' WHERE id = %d',
                $id
            ),
            ARRAY_A
        );

        if ( ! $row ) {
            return new WP_Error(
                'neonews_activity_not_found',
                __( 'Activity log entry not found.', 'neonews-core' ),
                array( 'status' => 404 )
            );
        }

        if ( ! empty( $row['context'] ) ) {
            $row['context'] = json_decode( $row['context'], true );
        }

        return rest_ensure_response( NeoNews_User_Activity_Log::format_item( $row ) );
    }
}
