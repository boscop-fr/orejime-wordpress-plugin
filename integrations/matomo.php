<?php
/**
 * Matomo integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

define( 'OREJIME_MATOMO_PURPOSE_ID', 'wp-orejime-matomo' );

/**
 * Tells if the Matomo plugin is installed and enabled.
 *
 * @return boolean
 */
function orejime_is_matomo_plugin_active() {
	return is_plugin_active( 'matomo/matomo.php' );
}

/**
 * Regenerates the tracking code so we can tune it.
 */
function orejime_activate_matomo() {
	if ( ! orejime_is_matomo_plugin_active() ) {
		return;
	}

	try {
		$settings = new \WpMatomo\Settings();
		$settings->apply_tracking_related_changes( array() );
	} catch ( \Throwable ) {
	}
}

add_action( 'orejime_activate_plugin', 'orejime_activate_matomo' );

/**
 * Adds relevant purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_matomo_purposes( array $purposes ) {
	if ( orejime_is_matomo_plugin_active() ) {
		$purposes [] = array(
			'id'    => OREJIME_MATOMO_PURPOSE_ID,
			'title' => 'Matomo',
		);
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_matomo_purposes' );

/**
 * Updates the tracking script so Orejime takes control over
 * it.
 *
 * @param string $script Script code.
 */
function orejime_wrap_matomo_tracking_code( $script ) {
	return orejime_wrap_purpose_code( $script, OREJIME_MATOMO_PURPOSE_ID );
}

add_filter( 'matomo_tracking_code_script', 'orejime_wrap_matomo_tracking_code', 10, 2 );
