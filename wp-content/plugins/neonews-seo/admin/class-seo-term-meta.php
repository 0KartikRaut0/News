<?php
/**
 * Category and tag SEO fields.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Term_Meta {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'category_add_form_fields', array( $this, 'add_fields' ) );
        add_action( 'category_edit_form_fields', array( $this, 'edit_fields' ) );
        add_action( 'post_tag_add_form_fields', array( $this, 'add_fields' ) );
        add_action( 'post_tag_edit_form_fields', array( $this, 'edit_fields' ) );
        add_action( 'created_term', array( $this, 'save' ), 10, 3 );
        add_action( 'edited_term', array( $this, 'save' ), 10, 3 );
    }

    public function add_fields() {
        ?>
        <div class="form-field nn-seo-term-fields">
            <h3><?php esc_html_e( 'SEO', 'neonews-seo' ); ?></h3>
            <label for="neonews-seo-term-title"><?php esc_html_e( 'SEO title', 'neonews-seo' ); ?></label>
            <input type="text" id="neonews-seo-term-title" name="neonews_seo_term_title" value="" />
            <label for="neonews-seo-term-description"><?php esc_html_e( 'Meta description', 'neonews-seo' ); ?></label>
            <textarea id="neonews-seo-term-description" name="neonews_seo_term_description" rows="3"></textarea>
            <label for="neonews-seo-term-robots"><?php esc_html_e( 'Robots', 'neonews-seo' ); ?></label>
            <select id="neonews-seo-term-robots" name="neonews_seo_term_robots">
                <option value=""><?php esc_html_e( 'Default', 'neonews-seo' ); ?></option>
                <option value="noindex"><?php esc_html_e( 'Noindex', 'neonews-seo' ); ?></option>
            </select>
        </div>
        <?php
    }

    /**
     * @param WP_Term $term Term.
     */
    public function edit_fields( $term ) {
        $title  = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_TITLE );
        $desc   = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_DESCRIPTION );
        $robots = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_ROBOTS );
        ?>
        <tr class="form-field nn-seo-term-fields">
            <th scope="row" colspan="2"><h3><?php esc_html_e( 'SEO', 'neonews-seo' ); ?></h3></th>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="neonews-seo-term-title"><?php esc_html_e( 'SEO title', 'neonews-seo' ); ?></label></th>
            <td><input type="text" id="neonews-seo-term-title" name="neonews_seo_term_title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="neonews-seo-term-description"><?php esc_html_e( 'Meta description', 'neonews-seo' ); ?></label></th>
            <td><textarea id="neonews-seo-term-description" name="neonews_seo_term_description" rows="3" class="large-text"><?php echo esc_textarea( $desc ); ?></textarea></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="neonews-seo-term-robots"><?php esc_html_e( 'Robots', 'neonews-seo' ); ?></label></th>
            <td>
                <select id="neonews-seo-term-robots" name="neonews_seo_term_robots">
                    <option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default', 'neonews-seo' ); ?></option>
                    <option value="noindex" <?php selected( $robots, 'noindex' ); ?>><?php esc_html_e( 'Noindex', 'neonews-seo' ); ?></option>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * @param int    $term_id Term ID.
     * @param int    $tt_id Term taxonomy ID.
     * @param string $taxonomy Taxonomy.
     */
    public function save( $term_id, $tt_id, $taxonomy ) {
        unset( $tt_id );

        if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_categories' ) ) {
            return;
        }

        $map = array(
            NeoNews_SEO_Meta::TERM_TITLE       => sanitize_text_field( wp_unslash( $_POST['neonews_seo_term_title'] ?? '' ) ),
            NeoNews_SEO_Meta::TERM_DESCRIPTION => sanitize_textarea_field( wp_unslash( $_POST['neonews_seo_term_description'] ?? '' ) ),
            NeoNews_SEO_Meta::TERM_ROBOTS      => sanitize_text_field( wp_unslash( $_POST['neonews_seo_term_robots'] ?? '' ) ),
        );

        foreach ( $map as $key => $value ) {
            if ( '' === $value ) {
                delete_term_meta( $term_id, $key );
            } else {
                update_term_meta( $term_id, $key, $value );
            }
        }
    }
}
