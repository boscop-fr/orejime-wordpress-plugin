<?php
/**
 * Script utilities.
 *
 * @package Orejime
 */

define( 'OREJIME_AUTO_WRAP_ATTRIBUTE_PREFIX', 'data-orejime-auto-wrap-' );

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
 * Returns an HTML attribute used to flag a script tag so
 * it is later wrapped and handled by Orejmie.
 *
 * @param string $purpose_id Purpose id.
 * @return string Attribute.
 */
function orejime_auto_wrap_attribute( $purpose_id ) {
	// We're using a valueless attribute to avoid problems
	// with double escaping. Plugins might escape quotes
	// from the value part, which would make the attribute
	// invalid.
	return OREJIME_AUTO_WRAP_ATTRIBUTE_PREFIX . $purpose_id;
}

/**
 * Wraps scripts flagged for auto wrapping.
 *
 * @todo Use WP_HTML_Processor when it is able to wrap tags.
 * @param string $html HTML.
 * @return string HTML.
 */
function orejime_auto_wrap_scripts( $html ) {
	if ( false === strpos( $html, OREJIME_AUTO_WRAP_ATTRIBUTE_PREFIX ) ) {
		return $html;
	}

	$prefix = preg_quote( OREJIME_AUTO_WRAP_ATTRIBUTE_PREFIX, '/' );

	// We're using a regex for now, as DOMDocument is quite
	// a heavy thing for such a simple task, and
	// WP_HTML_Processor does not support tag insertion yet.
	return preg_replace_callback(
		"/(?<script_start><script(\s+[^>]+?)?)\s+$prefix(?<id>[a-zA-Z0-9_-]+)(?<script_end>[^>]*?>.*?<\/script>)/is",
		function ( $matches ) {
			$id   = $matches['id'];
			$code = $matches['script_start'] . $matches['script_end'];
			return orejime_wrap_purpose_code( $code, $id );
		},
		$html
	);
}

/**
 * Starts auto wrapping scripts written to the output buffer.
 */
function orejime_start_auto_wrapping() {
	ob_start( 'orejime_auto_wrap_scripts' );
}

add_action( 'wp_head', 'orejime_start_auto_wrapping', -1 );
add_action( 'wp_footer', 'orejime_start_auto_wrapping', -1 );

/**
 * Stops auto wrapping scripts written to the output buffer.
 */
function orejime_end_auto_wrapping() {
	ob_end_flush();
}

add_action( 'wp_head', 'orejime_end_auto_wrapping', PHP_INT_MAX );
add_action( 'wp_footer', 'orejime_end_auto_wrapping', PHP_INT_MAX );
