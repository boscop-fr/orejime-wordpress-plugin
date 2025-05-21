<?php
/**
 * Matomo integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Tells if the Matomo plugin is installed and enabled.
 *
 * @return boolean
 */
function orejime_is_matomo_plugin_active() {
	return is_plugin_active( 'matomo/matomo.php' );
}

/**
 * Returns a unique purpose identifier for Matomo.
 *
 *  @return string
 */
function orejime_matomo_purpose_id() {
	return orejime_purpose_id( 'matomo' );
}

/**
 * Regenerates the tracking code so we can tune it.
 */
function orejime_activate_matomo() {
	if ( ! orejime_is_matomo_plugin_active() ) {
		return;
	}

	$settings = new \WpMatomo\Settings();
	$settings->apply_tracking_related_changes( array() );
}

/**
 * Updates the tracking script so Orejime takes control over
 * it.
 *
 * @param string $script Script code.
 */
function orejime_wrap_matomo_tracking_code( $script ) {
	return orejime_wrap_purpose_code( $script, orejime_matomo_purpose_id() );
}

add_filter( 'matomo_tracking_code_script', 'orejime_wrap_matomo_tracking_code', 10, 2 );
