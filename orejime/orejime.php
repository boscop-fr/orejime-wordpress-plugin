<?php
/**
 * Plugin Name: Orejime
 * Text Domain: orejime
 * Author: Boscop
 * Author URI: https://boscop.fr
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Includes.
 */
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/integrations/gutemberg.php';
require_once __DIR__ . '/integrations/matomo.php';

define( 'OREJIME_VERSION', 'latest' );
define( 'OREJIME_CDN_URL', 'https://cdn.jsdelivr.net/npm/orejime' );

/**
 * Generates a unique purpose id.
 *
 * @param string $name Purpose name.
 */
function orejime_purpose_id( $name ) {
	return "wp-orejime-$name";
}

/**
 * Wraps HTML with a template tag handled by Orejime.
 *
 * @param string  $code HTML.
 * @param string  $purpose Purpose id.
 * @param boolean $is_contextual Whether the code is contextual.
 */
function orejime_wrap_purpose_code( $code, $purpose, $is_contextual = false ) {
	$attrs  = "data-purpose=\"$purpose\"";
	$attrs .= $is_contextual ? ' data-contextual' : '';

	return <<<HTML
		<template $attrs>
			$code
		</template>
HTML;
}

/**
 * Finds the permalink of the privacy policy page.
 *
 * @return string
 */
function orejime_privacy_policy_url() {
	return get_page_link( (int) get_option( 'wp_page_for_privacy_policy' ) );
}

/**
 * Builds an URL pointing to the given file on Orejime's CDN.
 *
 * @param string $path Relative file path.
 * @return string
 */
function orejime_cdn_url( $path ) {
	return OREJIME_CDN_URL . '@' . OREJIME_VERSION . $path;
}

/**
 * Builds the list of purposes depending on config and
 * integrations.
 *
 * @return array
 */
function orejime_purposes() {
	$purposes = array();

	if ( orejime_is_contextual_consent_enabled() ) {
		$purposes [] = array(
			'id'    => orejime_purpose_id( 'embeds' ),
			'title' => 'Embeds',
		);
	}

	if ( orejime_is_matomo_plugin_active() ) {
		$purposes [] = array(
			'id'    => orejime_purpose_id( 'matomo' ),
			'title' => 'Matomo',
		);
	}

	return $purposes;
}

/**
 * Enqueues Orejime's scripts.
 */
function orejime_enqueue_scripts() {
	$lang   = substr( get_locale(), 0, 2 );
	$config = wp_json_encode(
		array(
			'privacyPolicyUrl' => orejime_privacy_policy_url(),
			'purposes'         => orejime_purposes(),
		)
	);

	wp_enqueue_script(
		'orejime-script',
		orejime_cdn_url( "/dist/orejime-standard-$lang.js" ),
		array(),
		OREJIME_VERSION,
		array(
			'in_footer' => false,
		)
	);

	wp_add_inline_script(
		'orejime-script',
		"window.orejimeConfig = $config;",
		'before'
	);

	wp_enqueue_style(
		'orejime-style',
		orejime_cdn_url( '/dist/orejime-standard.css' ),
		array(),
		OREJIME_VERSION
	);
}

add_action( 'wp_enqueue_scripts', 'orejime_enqueue_scripts' );
