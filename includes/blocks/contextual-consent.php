<?php
/**
 * Contextual consent block.
 *
 * @package Orejime
 */

namespace Orejime\Block;

use Orejime\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the 'orejime/contextual-consent' block.
 *
 * @param array  $attributes The block attributes.
 * @param string $content The block content.
 * @return string Rendered block.
 */
function render_contextual_consent( $attributes, $content ) {
	if ( empty( $content ) ) {
		return '';
	}

	$id   = $attributes['purposeId'] ?? null;
	$term = get_term( $id );

	if ( ! $term || is_wp_error( $term ) ) {
		return $content;
	}

	$purpose = Plugin::load()->get_purpose_from_term( $term );
	return \Orejime\wrap_purpose_code( $content, $purpose['id'], true );
}
