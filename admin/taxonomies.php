<?php
/**
 * Purposes configuration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

define( 'OREJIME_PURPOSE_TAXONOMY', 'orejime_purpose' );
define( 'OREJIME_PURPOSE_TAXONOMY_COOKIES', 'orejime_cookies' );

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
				'slug_field_description'            => __( 'A unique id.', 'orejime' ),
				'parent_field_description'          => __( 'Assign a parent purpose to create a hierarchy.', 'orejime' ),
				'desc_field_description'            => __( 'A short description of the purpose.', 'orejime' ),
				'orejime_cookies_field_description' => __( 'A list of cookies set by the purpose\'s scripts (separated by comas).', 'orejime' ),
			),
		)
	);
}

add_action( 'init', 'orejime_register_purpose_taxonomy' );

/**
 * Adds custom fields to the purpose taxonomy creation form.
 *
 * @param string $taxonomy_slug Taxonomy slug.
 */
function orejime_purpose_taxonomy_add_form_fields( $taxonomy_slug ) {
	$taxonomy    = get_taxonomy( $taxonomy_slug );
	$name        = OREJIME_PURPOSE_TAXONOMY_COOKIES;
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
	$name        = OREJIME_PURPOSE_TAXONOMY_COOKIES;
	$value       = esc_attr( get_term_meta( $term->term_id, OREJIME_PURPOSE_TAXONOMY_COOKIES, true ) );
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

	if ( isset( $_POST[ OREJIME_PURPOSE_TAXONOMY_COOKIES ] ) ) {
		update_term_meta(
			$term_id,
			OREJIME_PURPOSE_TAXONOMY_COOKIES,
			sanitize_text_field( $_POST[ OREJIME_PURPOSE_TAXONOMY_COOKIES ] )
		);
	}

    // phpcs:enable WordPress.Security.NonceVerification
}

add_action( 'created_' . OREJIME_PURPOSE_TAXONOMY, 'orejime_save_purpose_term_meta' );
add_action( 'edited_' . OREJIME_PURPOSE_TAXONOMY, 'orejime_save_purpose_term_meta' );

/**
 * Adds relevant purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_custom_purposes( array $purposes ) {
	$terms = get_terms(
		array(
			'taxonomy'   => OREJIME_PURPOSE_TAXONOMY,
			'hide_empty' => false,
		)
	);

	foreach ( $terms as $term ) {
		$purposes[] = array(
			'id'      => $term->term_id,
			'title'   => $term->name,
			'cookies' => wp_parse_list(
				get_term_meta( $term->term_id, OREJIME_PURPOSE_TAXONOMY_COOKIES, true )
			),
		);
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_custom_purposes' );
