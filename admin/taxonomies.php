<?php
/**
 * Purposes configuration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

define( 'OREJIME_PURPOSE_TAXONOMY', 'orejime_purpose' );
define( 'OREJIME_PURPOSE_TERM_INTEGRATION_ID', 'orejime_integration_id' );
define( 'OREJIME_PURPOSE_TERM_INTEGRATION_ID_PREFIX', 'orejime_integration_' );
define( 'OREJIME_PURPOSE_TERM_COOKIES', 'orejime_cookies' );

/**
 * Registers a custom taxonomy to provide info on purposes.
 */
function orejime_register_purpose_taxonomy() {
	register_taxonomy(
		OREJIME_PURPOSE_TAXONOMY,
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
		OREJIME_PURPOSE_TAXONOMY,
		OREJIME_PURPOSE_TERM_COOKIES,
		array(
			'type'    => 'string',
			'single'  => true,
			'default' => '',
		)
	);
}

add_action( 'init', 'orejime_register_purpose_taxonomy' );

function orejime_add_purpose_taxonomy_menu() {
	add_menu_page(
		'Orejime',
		'Orejime',
		'manage_options',
		'edit-tags.php?taxonomy=orejime_purpose'
	);
}

add_action( 'admin_menu', 'orejime_add_purpose_taxonomy_menu' );

/**
 * Registers a purpose to be managed by a given integration.
 *
 * @param Orejime_Integration $integration Integration.
 */
function orejime_register_integration_purpose_term( Orejime_Integration $integration ) {
	$term = orejime_get_integration_purpose_term( $integration );

	if ( $term ) {
		return;
	}

	$term = wp_insert_term(
		$integration->name,
		OREJIME_PURPOSE_TAXONOMY,
		array(
			'slug' => orejime_purpose_term_integration_id( $integration->id ),
		)
	);

	if ( is_wp_error( $term ) ) {
		return;
	}

	add_term_meta(
		$term['term_id'],
		OREJIME_PURPOSE_TERM_COOKIES,
		implode( ',', $integration->get_cookie_names() ),
		true
	);

	return $term;
}

add_action( 'orejime_registered_integration', 'orejime_register_integration_purpose_term' );

/**
 * Finds the term managed by the given integration, if any.
 *
 * @param Orejime_Integration $integration Integration.
 * @return WP_Term|null Term.
 */
function orejime_get_integration_purpose_term( Orejime_Integration $integration ) {
	$terms = get_terms(
		array(
			'slug'       => orejime_purpose_term_integration_id( $integration->id ),
			'taxonomy'   => OREJIME_PURPOSE_TAXONOMY,
			'hide_empty' => false,
			'number'     => 1,
		)
	);

	return $terms[0] ?? null;
}

/**
 * Finds the integration managing the given term, if any.
 *
 * @param int|WP_Term $term Term or term id.
 * @return Orejime_Integration|null Integration.
 */
function orejime_get_purpose_term_integration( $term ) {
	$term = get_term( $term );

	if ( strpos( $term->slug, OREJIME_PURPOSE_TERM_INTEGRATION_ID_PREFIX ) !== 0 ) {
		return null;
	}

	$integration_id = substr( $term->slug, strlen( OREJIME_PURPOSE_TERM_INTEGRATION_ID_PREFIX ) );
	return orejime_get_registered_integration( $integration_id );
}

/**
 *
 */
function orejime_purpose_term_integration_id( $integration_id ) {
	return OREJIME_PURPOSE_TERM_INTEGRATION_ID_PREFIX . $integration_id;
}

/**
 * Disables pagination. As there will always be a reasonable
 * amount of purposes, we might as well display them all.
 *
 * @return int
 */
function orejime_purpose_taxonomy_per_page() {
	return PHP_INT_MAX;
}

add_filter( 'edit_' . OREJIME_PURPOSE_TAXONOMY . '_per_page', 'orejime_purpose_taxonomy_per_page' );

/**
 * Disables bulk actions.
 *
 * @return array
 */
function orejime_purpose_taxonomy_bulk_actions() {
	return array();
}

add_filter( 'bulk_actions-edit-' . OREJIME_PURPOSE_TAXONOMY, 'orejime_purpose_taxonomy_bulk_actions' );

/**
 * Hides unnecessary columns and adds some to show custom
 * fields.
 *
 * @param array $columns Columns.
 * @return array Columns.
 */
function orejime_manage_purpose_term_columns( $columns ) {
	unset( $columns['cb'] );
	unset( $columns['slug'] );
	unset( $columns['posts'] );

	$columns[ OREJIME_PURPOSE_TERM_INTEGRATION_ID ] = __( 'Integration', 'orejime' );

	return $columns;
}

add_filter( 'manage_edit-' . OREJIME_PURPOSE_TAXONOMY . '_columns', 'orejime_manage_purpose_term_columns' );

/**
 * Fills custom columns.
 *
 * @param string $output Output.
 * @param string $column_name Column name.
 * @param int    $term_id Term id.
 * @return string Outputs.
 */
function orejime_manage_custom_purpose_term_column( $output, $column_name, $term_id ) {
	switch ( $column_name ) {
		case OREJIME_PURPOSE_TERM_INTEGRATION_ID:
			$integration = orejime_get_purpose_term_integration( $term_id );
			return $integration ? $integration->name : '—';

		default:
			return $output;
	}
}

add_filter( 'manage_' . OREJIME_PURPOSE_TAXONOMY . '_custom_column', 'orejime_manage_custom_purpose_term_column', 10, 3 );

/**
 * Hides the slug field, as it is not relevant for purposes.
 */
function orejime_hide_purpose_term_slug_field() {
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

add_action( OREJIME_PURPOSE_TAXONOMY . '_pre_add_form', 'orejime_hide_purpose_term_slug_field' );
add_action( OREJIME_PURPOSE_TAXONOMY . '_pre_edit_form', 'orejime_hide_purpose_term_slug_field' );

/**
 * Adds custom fields to the purpose taxonomy creation form.
 *
 * @param string $taxonomy_slug Taxonomy slug.
 */
function orejime_purpose_taxonomy_add_form_fields( $taxonomy_slug ) {
	$taxonomy    = get_taxonomy( $taxonomy_slug );
	$name        = OREJIME_PURPOSE_TERM_COOKIES;
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

add_filter( OREJIME_PURPOSE_TAXONOMY . '_add_form_fields', 'orejime_purpose_taxonomy_add_form_fields' );

/**
 * Adds custom fields to the purpose taxonomy edition form.
 *
 * @param WP_Term $term Term.
 * @param string  $taxonomy_slug Taxonomy slug.
 */
function orejime_purpose_taxonomy_edit_form_fields( WP_Term $term, $taxonomy_slug ) {
	$taxonomy    = get_taxonomy( $taxonomy_slug );
	$name        = OREJIME_PURPOSE_TERM_COOKIES;
	$value       = esc_attr( get_term_meta( $term->term_id, OREJIME_PURPOSE_TERM_COOKIES, true ) );
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

add_filter( OREJIME_PURPOSE_TAXONOMY . '_edit_form_fields', 'orejime_purpose_taxonomy_edit_form_fields', 10, 2 );

/**
 * Saves custom fields of a purpose taxonomy.
 *
 * @todo Properly check nonces.
 *
 * @param string $term_id Term id.
 */
function orejime_save_purpose_term_meta( $term_id ) {
    // phpcs:disable WordPress.Security.NonceVerification

	if ( isset( $_POST[ OREJIME_PURPOSE_TERM_COOKIES ] ) ) {
		update_term_meta(
			$term_id,
			OREJIME_PURPOSE_TERM_COOKIES,
			sanitize_text_field( wp_unslash( $_POST[ OREJIME_PURPOSE_TERM_COOKIES ] ) )
		);
	}

    // phpcs:enable WordPress.Security.NonceVerification
}

add_action( 'created_' . OREJIME_PURPOSE_TAXONOMY, 'orejime_save_purpose_term_meta' );
add_action( 'edited_' . OREJIME_PURPOSE_TAXONOMY, 'orejime_save_purpose_term_meta' );

/**
 * Adjusts user capabilities so they can't delete purposes
 * managed by the plugin's integrations.
 *
 * @param array $all_caps All user capabilities.
 * @param array $required_caps Required primitive capabilities.
 * @param array $args Additional args.
 * @return array Updated capabilities.
 */
function orejime_purpose_term_capabilities( array $all_caps, array $required_caps, array $args ) {
	if ( count( $args ) < 3 ) {
		return $all_caps;
	}

	list($cap, $user_id, $term_id) = $args;

	if ( 'delete_term' !== $cap ) {
		return $all_caps;
	}

	if ( orejime_get_purpose_term_integration( $term_id ) ) {
		// Removing only one capability is enough to deny access.
		$all_caps[ $required_caps[0] ] = false;
	}

	return $all_caps;
}

add_filter( 'user_has_cap', 'orejime_purpose_term_capabilities', 10, 4 );

/**
 * Adds configured purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_custom_purposes( array $purposes ) {
	$terms = get_terms(
		array(
			'taxonomy'     => OREJIME_PURPOSE_TAXONOMY,
			'hide_empty'   => false,
			'hierarchical' => true,
		)
	);

	foreach ( $terms as $term ) {
		$cookies = wp_parse_list(
			get_term_meta( $term->term_id, OREJIME_PURPOSE_TERM_COOKIES, true )
		);

		$integration =

		$cookies = array_unique( array_merge() );

		$purpose = array(
			'id'          => $term->term_id,
			'parent_id'   => $term->parent,
			'title'       => $term->name,
			'description' => $term->description,
			'cookies'     => $cookies,
		);

		$purpose = apply_filters( 'orejime_purpose', $purpose );

		if ( $purpose ) {
			$purposes[] = $purpose;
		}
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_custom_purposes' );
