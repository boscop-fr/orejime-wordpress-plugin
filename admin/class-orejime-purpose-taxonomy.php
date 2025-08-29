<?php
/**
 * Purpose taxonomy.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Registers and manages a custom taxonomy to edit purposes.
 */
class Orejime_Purpose_Taxonomy {

	const ID            = 'orejime_purpose';
	const COOKIES_FIELD = 'orejime_cookies';

	/**
	 * Hooks everything up.
	 */
	public function register() {
		add_action( 'init', array( $this, 'setup_taxonomy' ) );
		add_action( 'admin_menu', array( $this, 'setup_menu' ) );

		// Disables bulk actions.
		add_filter( 'bulk_actions-edit-' . self::ID, '__return_empty_array' );

		// Disables pagination. As there will always be a
		// reasonable amount of purposes, we might as well
		// display them all.
		add_filter( 'edit_' . self::ID . '_per_page', fn () => PHP_INT_MAX );
		add_filter( 'manage_edit-' . self::ID . '_columns', array( $this, 'hide_table_columns' ) );

		add_action( self::ID . '_pre_add_form', array( $this, 'hide_term_slug_field' ) );
		add_action( self::ID . '_pre_edit_form', array( $this, 'hide_term_slug_field' ) );
		add_filter( self::ID . '_add_form_fields', array( $this, 'add_term_form_fields' ) );
		add_filter( self::ID . '_edit_form_fields', array( $this, 'edit_term_form_fields' ), 10, 2 );
		add_action( 'created_' . self::ID, array( $this, 'save_custom_fields' ) );
		add_action( 'edited_' . self::ID, array( $this, 'save_custom_fields' ) );
	}

	/**
	 * Registers a custom taxonomy to configure purposes.
	 */
	public function setup_taxonomy() {
		register_taxonomy(
			self::ID,
			array(),
			array(
				'public'       => false,
				'hierarchical' => true,
				'show_ui'      => true,
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
			self::ID,
			self::COOKIES_FIELD,
			array(
				'type'    => 'string',
				'single'  => true,
				'default' => '',
			)
		);
	}

	/**
	 * Adds a menu entry to configure purposes.
	 */
	public function setup_menu() {
		add_menu_page(
			'Orejime',
			'Orejime',
			'manage_options',
			'edit-tags.php?taxonomy=orejime_purpose'
		);
	}

	/**
	 * Hides unnecessary columns.
	 *
	 * @param array $columns Columns.
	 * @return array Columns.
	 */
	public function hide_table_columns( $columns ) {
		unset( $columns['cb'] );
		unset( $columns['slug'] );
		unset( $columns['posts'] );

		return $columns;
	}

	/**
	 * Hides the slug field as it is not relevant for purposes.
	 */
	public function hide_term_slug_field() {
		$html = <<<'HTML'
			<style>
				.term-slug-wrap,
				.inline-edit-wrapper label:has(input[name="slug"]) {
					display: none;
				}
			</style>
		HTML;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Adds custom fields to the taxonomy creation form.
	 *
	 * @param string $taxonomy_slug Taxonomy slug.
	 */
	public function add_term_form_fields( $taxonomy_slug ) {
		$taxonomy    = get_taxonomy( $taxonomy_slug );
		$name        = self::COOKIES_FIELD;
		$label       = __( 'Cookies', 'orejime' );
		$description = $taxonomy->labels->orejime_cookies_field_description;

		$html = <<<HTML
			<div class="form-field term-cookies-wrap">
				<label for="tag-cookies">$label</label>

				<input
					name="$name"
					id="tag-cookies"
					type="text"
					value=""
					aria-describedby="cookies-description"
				/>

				<p id="cookies-description">
					$description
				</p>
			</div>
		HTML;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Adds custom fields to the purpose taxonomy edition form.
	 *
	 * @param WP_Term $term Term.
	 * @param string  $taxonomy_slug Taxonomy slug.
	 */
	public function edit_term_form_fields( WP_Term $term, $taxonomy_slug ) {
		$taxonomy    = get_taxonomy( $taxonomy_slug );
		$name        = self::COOKIES_FIELD;
		$value       = esc_attr( get_term_meta( $term->term_id, self::COOKIES_FIELD, true ) );
		$label       = __( 'Cookies', 'orejime' );
		$description = $taxonomy->labels->orejime_cookies_field_description;

		$html = <<<HTML
			<tr class="form-field term-cookies-wrap">
				<th scope="row">
					<label for="cookies">$label</label>
				</th>

				<td>
					<input
						name="$name"
						id="cookies"
						type="text"
						value="$value"
						aria-describedby="cookies-description"
					/>

					<p class="description" id="cookies-description">
						$description
					</p>
				</td>
			</tr>
		HTML;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Saves custom fields of a purpose taxonomy.
	 *
	 * @todo Properly check nonces.
	 *
	 * @param string $term_id Term id.
	 */
	public function save_custom_fields( $term_id ) {
		// phpcs:disable WordPress.Security.NonceVerification

		if ( isset( $_POST[ self::COOKIES_FIELD ] ) ) {
			update_term_meta(
				$term_id,
				self::COOKIES_FIELD,
				sanitize_text_field( wp_unslash( $_POST[ self::COOKIES_FIELD ] ) )
			);
		}

		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Serializes terms into a purpose tree.
	 *
	 * @return array Purposes.
	 */
	public function get_purpose_tree() {
		$terms = get_terms(
			array(
				'taxonomy'     => self::ID,
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
			if ( $child_purposes->contains( $purpose ) ) {
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
