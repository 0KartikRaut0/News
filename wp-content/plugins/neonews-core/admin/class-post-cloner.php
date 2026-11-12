<?php
/**
 * Post/Page Cloner — adds "Clone" row action in Posts and Pages list.
 * Copies post content, meta, taxonomies → saves as Draft with "Copy of" prefix.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Post_Cloner {

    /**
     * Post types to support cloning.
     *
     * @var string[]
     */
    private static $post_types = array( 'post', 'page' );

    public static function init() {
        // Add "Clone" row action link.
        add_filter( 'post_row_actions', array( __CLASS__, 'add_row_action' ), 10, 2 );
        add_filter( 'page_row_actions', array( __CLASS__, 'add_row_action' ), 10, 2 );

        // Handle the clone action.
        add_action( 'admin_action_nn_clone_post', array( __CLASS__, 'handle_clone' ) );

        // Admin notice after clone.
        add_action( 'admin_notices', array( __CLASS__, 'show_notice' ) );
    }

    /**
     * Add "Clone" link to row actions in post/page list tables.
     *
     * @param array   $actions Existing row actions.
     * @param WP_Post $post    Current post.
     * @return array
     */
    public static function add_row_action( $actions, $post ) {
        if ( ! in_array( $post->post_type, self::$post_types, true ) ) {
            return $actions;
        }

        $cap = 'page' === $post->post_type ? 'edit_pages' : 'edit_posts';
        if ( ! current_user_can( $cap, $post->ID ) ) {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'action'  => 'nn_clone_post',
                    'post_id' => $post->ID,
                ),
                admin_url( 'admin.php' )
            ),
            'nn_clone_post_' . $post->ID
        );

        $actions['nn_clone'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url( $url ),
            /* translators: %s: post title */
            esc_attr( sprintf( __( 'Clone "%s"', 'neonews-core' ), get_the_title( $post ) ) ),
            esc_html__( 'Clone', 'neonews-core' )
        );

        return $actions;
    }

    /**
     * Perform the clone and redirect back to the list.
     */
    public static function handle_clone() {
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_die( esc_html__( 'Invalid post ID.', 'neonews-core' ) );
        }

        check_admin_referer( 'nn_clone_post_' . $post_id );

        $original = get_post( $post_id );

        if ( ! $original || ! in_array( $original->post_type, self::$post_types, true ) ) {
            wp_die( esc_html__( 'Post not found.', 'neonews-core' ) );
        }

        $cap = 'page' === $original->post_type ? 'edit_pages' : 'edit_posts';
        if ( ! current_user_can( $cap, $post_id ) ) {
            wp_die( esc_html__( 'You do not have permission to clone this post.', 'neonews-core' ) );
        }

        $new_id = self::clone_post( $original );

        if ( is_wp_error( $new_id ) ) {
            wp_die( esc_html( $new_id->get_error_message() ) );
        }

        // Redirect to the cloned post editor.
        $redirect = add_query_arg(
            array(
                'post'        => $new_id,
                'action'      => 'edit',
                'nn_cloned'   => '1',
            ),
            admin_url( 'post.php' )
        );

        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Core clone logic — copies post, meta, and taxonomies.
     *
     * @param WP_Post $original Original post object.
     * @return int|WP_Error New post ID or error.
     */
    private static function clone_post( $original ) {
        // Build new post data.
        $new_post = array(
            'post_title'     => sprintf(
                /* translators: %s: original post title */
                __( 'Copy of %s', 'neonews-core' ),
                $original->post_title
            ),
            'post_content'   => $original->post_content,
            'post_excerpt'   => $original->post_excerpt,
            'post_status'    => 'draft',          // Always draft — never publish immediately.
            'post_type'      => $original->post_type,
            'post_author'    => get_current_user_id(),
            'post_parent'    => $original->post_parent,
            'menu_order'     => $original->menu_order,
            'ping_status'    => $original->ping_status,
            'comment_status' => $original->comment_status,
            'post_password'  => $original->post_password,
        );

        $new_id = wp_insert_post( $new_post, true );

        if ( is_wp_error( $new_id ) ) {
            return $new_id;
        }

        // ── Copy all post meta ────────────────────────────────────────────
        $meta_keys_skip = array(
            '_edit_lock',
            '_edit_last',
        );

        $all_meta = get_post_meta( $original->ID );
        if ( $all_meta ) {
            foreach ( $all_meta as $key => $values ) {
                if ( in_array( $key, $meta_keys_skip, true ) ) {
                    continue;
                }
                foreach ( $values as $value ) {
                    add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
                }
            }
        }

        // ── Copy all taxonomies (categories, tags, custom) ────────────────
        $taxonomies = get_object_taxonomies( $original->post_type );
        foreach ( $taxonomies as $taxonomy ) {
            $terms = wp_get_object_terms( $original->ID, $taxonomy, array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                wp_set_object_terms( $new_id, $terms, $taxonomy );
            }
        }

        // ── Copy featured image ───────────────────────────────────────────
        $thumbnail_id = get_post_thumbnail_id( $original->ID );
        if ( $thumbnail_id ) {
            set_post_thumbnail( $new_id, $thumbnail_id );
        }

        do_action( 'neonews_post_cloned', $new_id, $original->ID );

        return $new_id;
    }

    /**
     * Show success notice after clone redirect to editor.
     */
    public static function show_notice() {
        if ( ! isset( $_GET['nn_cloned'] ) || '1' !== $_GET['nn_cloned'] ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'post' !== $screen->base ) {
            return;
        }
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php esc_html_e( '✅ Post cloned successfully! You are now editing the copy. It has been saved as a Draft.', 'neonews-core' ); ?>
            </p>
        </div>
        <?php
    }
}
