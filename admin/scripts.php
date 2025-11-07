<?php
/**
 * Script utilities.
 *
 * @package Orejime
 */

/**
 * Builds the opening tag of a wrapper template.
 *
 * @param string  $purpose Purpose id.
 * @param boolean $is_contextual Whether the code is contextual.
 */
function orejime_purpose_code_wrapper_start( $purpose, $is_contextual = false ) {
	return $is_contextual
		? "<template data-purpose=\"$purpose\" data-contextual>"
		: "<template data-purpose=\"$purpose\">";
}

/**
 * Builds the closing tag of a wrapper template.
 */
function orejime_purpose_code_wrapper_end() {
	return '</template>';
}

/**
 * Wraps HTML with a template tag handled by Orejime.
 *
 * @param string  $code HTML.
 * @param string  $purpose Purpose id.
 * @param boolean $is_contextual Whether the code is contextual.
 */
function orejime_wrap_purpose_code( $code, $purpose, $is_contextual = false ) {
	return orejime_purpose_code_wrapper_start(
		$purpose,
		$is_contextual
	) . $code . orejime_purpose_code_wrapper_end();
}

/**
 * Prints HTML wrapped in a template tag handled by Orejime.
 *
 * @param callable $callback Callback that prints code.
 * @param string   $purpose Purpose id.
 * @param boolean  $is_contextual Whether the code is contextual.
 */
function orejime_print_purpose_code( $callback, $purpose, $is_contextual = false ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo orejime_purpose_code_wrapper_start( $purpose, $is_contextual );

	call_user_func( $callback );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo orejime_purpose_code_wrapper_end();
}
