<?php
/**
 * Wraps content with a contextual consent placeholder.
 *
 * @package Orejime
 */

$purpose_id      = $attributes['purposeId'] ?? null;
$purpose_term    = get_term( $purpose_id );
$term_exists     = $purpose_term && ! is_wp_error( $purpose_term );
$wrapped_content = $term_exists && ! empty( $content )
	? \Orejime\wrap_purpose_code( $content, $purpose_term->term_id, true )
	: $content;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $wrapped_content;
