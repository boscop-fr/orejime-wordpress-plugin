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
 * Updates the tracking script so Orejime takes control over
 * it.
 *
 * @param string $script Script code.
 */
function orejime_wrap_matomo_tracking_code( $script ) {
	return orejime_wrap_purpose_code( $script, orejime_purpose_id( 'matomo' ) );
}

add_filter( 'matomo_tracking_code_script', 'orejime_wrap_matomo_tracking_code', 10, 2 );
