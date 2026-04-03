<?php
/**
 * Wraps content with a contextual consent placeholder.
 *
 * @package Orejime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orejime_purpose_id      = $attributes['purposeId'] ?? null;
$orejime_purpose_term    = get_term( $orejime_purpose_id );
$orejime_term_exists     = $orejime_purpose_term && ! is_wp_error( $orejime_purpose_term );
$orejime_wrapped_content = $orejime_term_exists && ! empty( $content )
	? \Orejime\wrap_purpose_code( $content, $orejime_purpose_term->term_id, true )
	: $content;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $orejime_wrapped_content;
