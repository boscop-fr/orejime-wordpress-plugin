<?php
/**
 * Matomo integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Matomo integration.
 */
class Orejime_Integration_Matomo extends Orejime_Integration {

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'matomo_tracking_code_script', array( $this, 'wrap_script' ), 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return is_plugin_active( 'matomo/matomo.php' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return array();
	}

	/**
	 * Wraps the tracking script.
	 *
	 * @param string $script HTML.
	 */
	public function wrap_script( $script ) {
		return orejime_wrap_purpose_code( $script, $this->purpose_id );
	}
}
