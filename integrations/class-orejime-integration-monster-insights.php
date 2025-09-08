<?php
/**
 * Monster Insights integration.
 *
 * @package Orejime
 */

/**
 * Monster Insights integration.
 */
class Orejime_Integration_Monster_Insights extends Orejime_Integration {

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'monsterinsights_tracking_before', array( $this, 'open_tracking_code' ), 10 );
		add_filter( 'monsterinsights_tracking_after', array( $this, 'close_tracking_code' ), 10 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return array();
	}

	/**
	 * Starts wrapping the tracking script.
	 */
	public function open_tracking_code() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo orejime_purpose_code_wrapper_start( $this->purpose_id );
	}

	/**
	 * Finishes wrapping the tracking script.
	 */
	public function close_tracking_code() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo orejime_purpose_code_wrapper_end();
	}
}
