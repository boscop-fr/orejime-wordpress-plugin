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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'OREJIME_VERSION', 'latest' );
define( 'OREJIME_CDN_URL', 'https://cdn.jsdelivr.net/npm/orejime' );
define( 'OREJIME_SCRIPT_HANDLE', 'orejime-script' );
define( 'OREJIME_STYLE_HANDLE', 'orejime-style' );

require_once __DIR__ . '/admin/media.php';
require_once __DIR__ . '/integrations/google-site-kit.php';
require_once __DIR__ . '/integrations/matomo.php';
require_once __DIR__ . '/integrations/monster-insights.php';
require_once __DIR__ . '/integrations/blocks/core/embed.php';

/**
 * Generates a unique purpose id.
 *
 * @param string $name Purpose name.
 */
function orejime_purpose_id( $name ) {
	return "wp-orejime-$name";
}

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
			'id'    => orejime_embed_core_block_purpose_id(),
			'title' => 'Embeds',
		);
	}

	if ( orejime_is_matomo_plugin_active() ) {
		$purposes [] = array(
			'id'    => orejime_matomo_purpose_id(),
			'title' => 'Matomo',
		);
	}

	if ( orejime_is_google_site_kit_plugin_active() ) {
		$purposes [] = array(
			'id'    => orejime_google_site_kit_purpose_id(),
			'title' => 'Google analytics',
		);
	}

	if ( orejime_is_monster_insights_plugin_active() ) {
		$purposes [] = array(
			'id'    => orejime_monster_insights_purpose_id(),
			'title' => 'Google analytics',
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
		OREJIME_SCRIPT_HANDLE,
		orejime_cdn_url( "/dist/orejime-standard-$lang.js" ),
		array(),
		OREJIME_VERSION,
		array(
			'in_footer' => false,
		)
	);

	wp_add_inline_script(
		OREJIME_SCRIPT_HANDLE,
		"window.orejimeConfig = $config;",
		'before'
	);

	wp_enqueue_style(
		OREJIME_STYLE_HANDLE,
		orejime_cdn_url( '/dist/orejime-standard.css' ),
		array(),
		OREJIME_VERSION
	);
}

add_action( 'wp_enqueue_scripts', 'orejime_enqueue_scripts' );

/**
 * Plugin activation.
 */
function orejime_activate() {
	orejime_activate_matomo();
}

register_activation_hook( __FILE__, 'orejime_activate' );
