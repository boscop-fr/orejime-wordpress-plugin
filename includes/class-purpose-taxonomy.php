<?php
/**
 * Purpose taxonomy.
 *
 * @package Orejime
 */

namespace Orejime;

use SplObjectStorage;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and manages a custom taxonomy to edit purposes.
 */
class Purpose_Taxonomy {

	use Hookable;

	const NAME          = 'orejime_purpose';
	const COOKIES_FIELD = 'orejime_cookies';
	const NONCE_ACTION  = 'orejime_purpose_meta';
	const NONCE_NAME    = 'orejime_purpose_meta_nonce';

	/**
	 * Hooks everything up.
	 */
	public function register() {
		add_action( 'init', $this->get_callback( 'setup_taxonomy' ) );

		// Disables bulk actions.
		add_filter( 'bulk_actions-edit-' . self::NAME, '__return_empty_array' );

		// Disables pagination. As there will always be a
		// reasonable amount of purposes, we might as well
		// display them all.
		add_filter( 'edit_' . self::NAME . '_per_page', fn () => PHP_INT_MAX );
		add_filter( 'manage_edit-' . self::NAME . '_columns', $this->get_callback( 'hide_table_columns' ) );

		add_action( self::NAME . '_pre_add_form', $this->get_callback( 'hide_term_slug_field' ) );
		add_action( self::NAME . '_pre_edit_form', $this->get_callback( 'hide_term_slug_field' ) );
		add_filter( self::NAME . '_add_form_fields', $this->get_callback( 'add_term_form_fields' ) );
		add_filter( self::NAME . '_edit_form_fields', $this->get_callback( 'edit_term_form_fields' ), 10, 2 );
		add_action( 'created_' . self::NAME, $this->get_callback( 'save_custom_fields' ) );
		add_action( 'edited_' . self::NAME, $this->get_callback( 'save_custom_fields' ) );
	}

	/**
	 * Registers a custom taxonomy to configure purposes.
	 */
	private function setup_taxonomy() {
		register_taxonomy(
			self::NAME,
			array(),
			array(
				'public'       => false,
				'hierarchical' => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'labels'       => array(
					'name'                              => __( 'Purposes', 'orejime' ),
					'singular_name'                     => __( 'Purpose', 'orejime' ),
					'menu_name'                         => __( 'Orejime purposes', 'orejime' ),
					'add_new'                           => __( 'Add new purpose', 'orejime' ),
					'add_new_item'                      => __( 'Add new purpose', 'orejime' ),
					'new_item'                          => __( 'New purpose', 'orejime' ),
					'edit_item'                         => __( 'Edit purpose', 'orejime' ),
					'update_item'                       => __( 'Update purpose', 'orejime' ),
					'back_to_items'                     => __( '&larr; Go to purposes', 'orejime' ),
					'view_item'                         => __( 'View purpose', 'orejime' ),
					'all_items'                         => __( 'All purposes', 'orejime' ),
					'search_items'                      => __( 'Search purposes', 'orejime' ),
					'parent_item'                       => __( 'Parent purpose', 'orejime' ),
					'parent_item_colon'                 => __( 'Parent purpose:', 'orejime' ),
					'name_field_description'            => __( 'Typically the name of a third-party service (i.e. Google Analytics), or a broader category name (i.e. Analytics or Ads)', 'orejime' ),
					'parent_field_description'          => __( 'Assign a parent purpose to create a hierarchy.', 'orejime' ),
					'desc_field_description'            => __( 'A short description of the purpose.', 'orejime' ),
					'orejime_cookies_field_description' => __( 'A list of cookies set by the purpose\'s scripts (separated by comas).', 'orejime' ),
				),
			)
		);

		register_term_meta(
			self::NAME,
			self::COOKIES_FIELD,
			array(
				'type'    => 'string',
				'single'  => true,
				'default' => '',
			)
		);
	}

	/**
	 * Hides unnecessary columns.
	 *
	 * @param array $columns Columns.
	 * @return array Columns.
	 */
	private function hide_table_columns( $columns ) {
		unset( $columns['cb'] );
		unset( $columns['slug'] );
		unset( $columns['posts'] );

		return $columns;
	}

	/**
	 * Hides the slug field as it is not relevant for purposes.
	 */
	private function hide_term_slug_field() {
		?>
			<style>
				.term-slug-wrap,
				.inline-edit-wrapper label:has(input[name="slug"]) {
					display: none;
				}
			</style>
		<?php
	}

	/**
	 * Adds custom fields to the taxonomy creation form.
	 *
	 * @param string $taxonomy_slug Taxonomy slug.
	 */
	private function add_term_form_fields( $taxonomy_slug ) {
		$this->print_meta_fields( $taxonomy_slug );
	}

	/**
	 * Adds custom fields to the purpose taxonomy edition form.
	 *
	 * @param WP_Term $term Term.
	 * @param string  $taxonomy_slug Taxonomy slug.
	 */
	private function edit_term_form_fields( WP_Term $term, $taxonomy_slug ) {
		$value = esc_attr( get_term_meta( $term->term_id, self::COOKIES_FIELD, true ) );
		$this->print_meta_fields( $taxonomy_slug, $value );
	}

	/**
	 * Prints form fields for term metas.
	 *
	 * @param string $taxonomy_slug Taxonomy slug.
	 * @param string $value Value.
	 */
	private function print_meta_fields( $taxonomy_slug, $value = '' ) {
		$taxonomy = get_taxonomy( $taxonomy_slug );

		?>
			<tr class="form-field term-cookies-wrap">
				<th scope="row">
					<label for="orejime-cookies">
						<?php esc_html_e( 'Cookies', 'orejime' ); ?>
					</label>
				</th>

				<td>
					<input
						name="<?php echo esc_attr( self::COOKIES_FIELD ); ?>"
						id="orejime-cookies"
						type="text"
						value="<?php echo esc_attr( $value ); ?>"
						aria-describedby="orejime-cookies-description"
					/>

					<p class="description" id="orejime-cookies-description">
						<?php echo esc_html( $taxonomy->labels->orejime_cookies_field_description ); ?>
					</p>
				</td>
			</tr>
		<?php

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
	}

	/**
	 * Saves custom fields of a purpose taxonomy.
	 *
	 * @param string $term_id Term id.
	 */
	private function save_custom_fields( $term_id ) {
		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( isset( $_POST[ self::COOKIES_FIELD ] ) ) {
			update_term_meta(
				$term_id,
				self::COOKIES_FIELD,
				sanitize_text_field( wp_unslash( $_POST[ self::COOKIES_FIELD ] ) )
			);
		}
	}

	/**
	 * Returns the URL path of the edit page.
	 *
	 * @return string Path.
	 */
	public function get_admin_edit_page_path() {
		return 'edit-tags.php?taxonomy=' . self::NAME;
	}

	/**
	 * Serializes terms into a purpose tree.
	 *
	 * @return array Purposes.
	 */
	public function get_purpose_tree() {
		$terms = get_terms(
			array(
				'taxonomy'     => self::NAME,
				'hide_empty'   => false,
				'hierarchical' => true,
			)
		);

		$purposes_by_id = array();
		$child_purposes = new SplObjectStorage();

		foreach ( $terms as $term ) {
			// We're using objects to work with references.
			// This makes it easier to arrange purposes in
			// a tree.
			$purpose                        = (object) $this->make_purpose_from_term( $term );
			$purposes_by_id[ $purpose->id ] = $purpose;

			if ( $term->parent ) {
				$child_purposes[ $purpose ] = $term->parent;
			}
		}

		$root_purposes = array();

		foreach ( $purposes_by_id as $purpose ) {
			if ( $child_purposes->offsetExists( $purpose ) ) {
				$parent_id = $child_purposes->offsetGet( $purpose );

				if ( ! isset( $purposes_by_id[ $parent_id ] ) ) {
					// If the parent doesn't exist, puts the
					// purpose at the top level so it doesn't
					// vanish.
					$root_purposes[] = $purpose;
					continue;
				}

				$parent = $purposes_by_id[ $parent_id ];

				if ( ! isset( $parent->purposes ) ) {
					$parent->purposes = array();
				}

				$parent->purposes[] = $purpose;
			} else {
				$root_purposes[] = $purpose;
			}
		}

		return $root_purposes;
	}

	/**
	 * Creates a purpose from the given term.
	 *
	 * @param WP_Term $term Term.
	 * @return array Purpose.
	 */
	protected function make_purpose_from_term( WP_Term $term ) {
		$cookies = wp_parse_list(
			get_term_meta( $term->term_id, self::COOKIES_FIELD, true )
		);

		return array(
			'id'          => $term->term_id,
			'title'       => $term->name,
			'description' => $term->description,
			'cookies'     => $cookies,
		);
	}
}
