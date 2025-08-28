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
require_once __DIR__ . '/admin/taxonomies.php';
require_once __DIR__ . '/integrations/google-site-kit.php';
require_once __DIR__ . '/integrations/matomo.php';
require_once __DIR__ . '/integrations/monster-insights.php';
require_once __DIR__ . '/integrations/blocks/core/embed.php';
require_once __DIR__ . '/integrations/class-orejime-integration.php';
require_once __DIR__ . '/integrations/class-orejime-integration-google-site-kit.php';

$_orejime_integrations = array();

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
 * @todo Improve algorithm (although it is good enough for
 * such a small amount of items).
 */
function orejime_purposes_tree( array $purposes ) {

	$roots = array_values(
		array_filter(
			$purposes,
			function ( $purpose ) {
				return empty( $purpose['parent_id'] );
			}
		)
	);

	foreach ( $roots as &$root ) {
		$children = array_values(
			array_filter(
				$purposes,
				function ( $purpose ) use ( $root ) {
					return isset( $purpose['parent_id'] ) && $purpose['parent_id'] === $root['id'];
				}
			)
		);

		if ( $children ) {
			$root['purposes'] = $children;
		}
	}

	return $roots;
}

/**
 * Enqueues Orejime's scripts.
 */
function orejime_enqueue_scripts() {
	$purposes = apply_filters( 'orejime_enqueue_purposes', array() );

	if ( empty( $purposes ) ) {
		return;
	}

	$lang   = substr( get_locale(), 0, 2 );
	$config = wp_json_encode(
		array(
			'privacyPolicyUrl' => orejime_privacy_policy_url(),
			'purposes'         => orejime_purposes_tree( $purposes ),
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
function orejime_activate_plugin() {
	do_action( 'orejime_activate_plugin' );
}

register_activation_hook( __FILE__, 'orejime_activate_plugin' );

/**
 * Plugin initialization.
 */
function orejime_init() {
	do_action( 'orejime_init' );

	orejime_register_integration( new Orejime_Integration_Google_Site_Kit() );
}

add_action( 'init', 'orejime_init' );

/**
 *
 */
function orejime_register_integration( Orejime_Integration $integration ) {
	global $_orejime_integrations;

	if ( isset( $_orejime_integrations[ $integration->id ] ) ) {
		return;
	}

	$integration->register();
	$_orejime_integrations[ $integration->id ] = $integration;

	do_action( 'orejime_registered_integration', $integration );
}

/**
 *
 */
function orejime_get_registered_integrations() {
	global $_orejime_integrations;
	return $_orejime_integrations;
}

/**
 *
 */
function orejime_get_registered_integration( $id ) {
	global $_orejime_integrations;
	return $_orejime_integrations[ $id ] ?? null;
}
