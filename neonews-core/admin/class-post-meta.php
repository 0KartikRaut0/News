<?php
/**
 * Post Meta Boxes
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Post Meta Class
 */
class NeoNews_Post_Meta {

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
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
        add_filter( 'manage_posts_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
        add_filter( 'manage_edit-post_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'sort_by_views' ) );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'neonews_post_options',
            __( 'NeoNews Options', 'neonews-core' ),
            array( $this, 'render_meta_box' ),
            'post',
            'side',
            'high'
        );

        add_meta_box(
            'neonews_post_stats',
            __( 'Post Statistics', 'neonews-core' ),
            array( $this, 'render_stats_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render options meta box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'neonews_save_post_meta', 'neonews_post_meta_nonce' );

        $is_featured = get_post_meta( $post->ID, '_neonews_featured_post', true );
        $is_breaking = get_post_meta( $post->ID, '_neonews_breaking_news', true );
        $push_notification = get_post_meta( $post->ID, '_neonews_push_notification', true );
        $show_social_share   = get_post_meta( $post->ID, '_neonews_show_social_share', true );
        $show_whatsapp_share = get_post_meta( $post->ID, '_neonews_show_whatsapp_share', true );
        $show_social_share   = ( '' === $show_social_share || '1' === $show_social_share );
        $show_whatsapp_share = ( '' === $show_whatsapp_share || '1' === $show_whatsapp_share );
        ?>
        <div class="nn-meta-box">
            <p>
                <label>
                    <input type="checkbox" 
                           name="_neonews_featured_post" 
                           value="1" 
                           <?php checked( $is_featured, '1' ); ?>>
                    <?php esc_html_e( 'Featured Post', 'neonews-core' ); ?>
                </label>
            </p>
            <p class="description">
                <?php esc_html_e( 'Display this post in the featured carousel.', 'neonews-core' ); ?>
            </p>

            <hr>

            <p>
                <label>
                    <input type="checkbox" 
                           name="_neonews_breaking_news" 
                           value="1" 
                           <?php checked( $is_breaking, '1' ); ?>>
                    <?php esc_html_e( 'Breaking News', 'neonews-core' ); ?>
                </label>
            </p>
            <p class="description">
                <?php esc_html_e( 'Display in breaking news ticker. Auto-expires after 24 hours.', 'neonews-core' ); ?>
            </p>

            <?php if ( NeoNews_OneSignal_Integration::is_configured() ) : ?>
                <hr>

                <p>
                    <label>
                        <input type="checkbox" 
                               name="_neonews_push_notification" 
                               value="1" 
                               <?php checked( $push_notification, '1' ); ?>>
                        <?php esc_html_e( 'Send Push Notification', 'neonews-core' ); ?>
                    </label>
                </p>
                <p class="description">
                    <?php esc_html_e( 'Send a push notification when this post is published.', 'neonews-core' ); ?>
                </p>
            <?php endif; ?>

            <hr>

            <p>
                <label>
                    <input type="checkbox"
                           name="_neonews_show_social_share"
                           value="1"
                           <?php checked( $show_social_share ); ?>>
                    <?php esc_html_e( 'Show social share buttons', 'neonews-core' ); ?>
                </label>
            </p>
            <p class="description">
                <?php esc_html_e( 'Facebook, X, LinkedIn, and copy link on this post. Also requires NewsPulse → Features → Post shares.', 'neonews-core' ); ?>
            </p>

            <p>
                <label>
                    <input type="checkbox"
                           name="_neonews_show_whatsapp_share"
                           value="1"
                           <?php checked( $show_whatsapp_share ); ?>>
                    <?php esc_html_e( 'Show WhatsApp share button', 'neonews-core' ); ?>
                </label>
            </p>
            <p class="description">
                <?php esc_html_e( 'WhatsApp icon on this post. Requires social shares above and NewsPulse → Footer → WhatsApp on posts.', 'neonews-core' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render stats meta box
     */
    public function render_stats_meta_box( $post ) {
        $views = NeoNews_View_Counter::get_views( $post->ID );
        $views_7days = NeoNews_View_Counter::get_views_for_period( $post->ID, 7 );
        $submitted_via_frontend = get_post_meta( $post->ID, '_neonews_submitted_via_frontend', true );
        $submission_date = get_post_meta( $post->ID, '_neonews_submission_date', true );
        ?>
        <div class="nn-meta-box nn-stats-box">
            <table class="widefat">
                <tr>
                    <th><?php esc_html_e( 'Total Views', 'neonews-core' ); ?></th>
                    <td><strong><?php echo esc_html( number_format_i18n( $views ) ); ?></strong></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Views (7 days)', 'neonews-core' ); ?></th>
                    <td><?php echo esc_html( number_format_i18n( $views_7days ) ); ?></td>
                </tr>
                <?php if ( $submitted_via_frontend ) : ?>
                    <tr>
                        <th><?php esc_html_e( 'Source', 'neonews-core' ); ?></th>
                        <td>
                            <span class="nn-badge nn-badge-info">
                                <?php esc_html_e( 'Frontend Submission', 'neonews-core' ); ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ( $submission_date ) : ?>
                        <tr>
                            <th><?php esc_html_e( 'Submitted', 'neonews-core' ); ?></th>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission_date ) ) ); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
            </table>
        </div>
        <?php
    }

    /**
     * Save meta data
     */
    public function save_meta( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( 'post' !== $post->post_type ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['neonews_post_meta_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_post_meta_nonce'] ) ), 'neonews_save_post_meta' ) ) {
            return;
        }

        $is_featured = isset( $_POST['_neonews_featured_post'] ) ? '1' : '';
        update_post_meta( $post_id, '_neonews_featured_post', $is_featured );

        if ( NeoNews_OneSignal_Integration::is_configured() ) {
            $push_notification = isset( $_POST['_neonews_push_notification'] ) ? '1' : '';
            update_post_meta( $post_id, '_neonews_push_notification', $push_notification );
        }

        update_post_meta( $post_id, '_neonews_show_social_share', isset( $_POST['_neonews_show_social_share'] ) ? '1' : '0' );
        update_post_meta( $post_id, '_neonews_show_whatsapp_share', isset( $_POST['_neonews_show_whatsapp_share'] ) ? '1' : '0' );
    }

    /**
     * Add custom columns
     */
    public function add_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            
            if ( 'title' === $key ) {
                $new_columns['nn_views'] = __( 'Views', 'neonews-core' );
                $new_columns['nn_flags'] = __( 'Flags', 'neonews-core' );
            }
        }
        
        return $new_columns;
    }

    /**
     * Render custom columns
     */
    public function render_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'nn_views':
                $views = NeoNews_View_Counter::get_views( $post_id );
                echo '<span class="nn-view-count">' . esc_html( NeoNews_View_Counter::format_views( $views ) ) . '</span>';
                break;

            case 'nn_flags':
                $flags = array();
                
                if ( get_post_meta( $post_id, '_neonews_featured_post', true ) ) {
                    $flags[] = '<span class="nn-flag nn-flag-featured" title="' . esc_attr__( 'Featured', 'neonews-core' ) . '">★</span>';
                }
                
                if ( get_post_meta( $post_id, '_neonews_breaking_news', true ) ) {
                    $flags[] = '<span class="nn-flag nn-flag-breaking" title="' . esc_attr__( 'Breaking', 'neonews-core' ) . '">⚡</span>';
                }
                
                if ( get_post_meta( $post_id, '_neonews_submitted_via_frontend', true ) ) {
                    $flags[] = '<span class="nn-flag nn-flag-submission" title="' . esc_attr__( 'Frontend Submission', 'neonews-core' ) . '">📝</span>';
                }

                $show_social = get_post_meta( $post_id, '_neonews_show_social_share', true );
                if ( '0' === $show_social ) {
                    $flags[] = '<span class="nn-flag nn-flag-no-share" title="' . esc_attr__( 'Share buttons hidden', 'neonews-core' ) . '">🔗</span>';
                }
                
                echo wp_kses_post( implode( ' ', $flags ) );
                break;
        }
    }

    /**
     * Make views column sortable
     */
    public function sortable_columns( $columns ) {
        $columns['nn_views'] = 'nn_views';
        return $columns;
    }

    /**
     * Sort by views
     */
    public function sort_by_views( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( 'nn_views' === $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_neonews_post_views' );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }
}
