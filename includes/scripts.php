<?php
/**
 * Script utilities.
 *
 * @package Orejime
 */

namespace Orejime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the opening tag of a wrapper template.
 *
 * @param string  $purpose Purpose id.
 * @param boolean $is_contextual Whether the code is contextual.
 */
function purpose_code_wrapper_start( $purpose, $is_contextual = false ) {
	return $is_contextual
		? "<template data-purpose=\"$purpose\" data-contextual>"
		: "<template data-purpose=\"$purpose\">";
}

/**
 * Builds the closing tag of a wrapper template.
 */
function purpose_code_wrapper_end() {
	return '</template>';
}

/**
 * Wraps HTML with a template tag handled by Orejime.
 *
 * @param string  $code HTML.
 * @param string  $purpose Purpose id.
 * @param boolean $is_contextual Whether the code is contextual.
 */
function wrap_purpose_code( $code, $purpose, $is_contextual = false ) {
	return purpose_code_wrapper_start(
		$purpose,
		$is_contextual
	) . $code . purpose_code_wrapper_end();
}

/**
 * Prints HTML wrapped in a template tag handled by Orejime.
 *
 * @param callable $callback Callback that prints code.
 * @param string   $purpose Purpose id.
 * @param boolean  $is_contextual Whether the code is contextual.
 */
function print_purpose_code( $callback, $purpose, $is_contextual = false ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo purpose_code_wrapper_start( $purpose, $is_contextual );

	call_user_func( $callback );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo purpose_code_wrapper_end();
}

/**
 * Keeps wrapper templates when themes or plugins pass
 * rendered content through kses.
 *
 * @param array  $tags Allowed tags.
 * @param string $context Context.
 * @return array Allowed tags.
 */
function allow_purpose_code_wrapper( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['template'] = array(
			'data-purpose'     => true,
			'data-contextual'  => true,
			'data-title-level' => true,
		);
	}

	return $tags;
}

add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\allow_purpose_code_wrapper', 10, 2 );
