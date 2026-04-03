<?php
/**
 * Monster Insights integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration;

use Orejime\Hookable;
use Orejime\Integration;

/**
 * Monster Insights integration.
 */
class Monster_Insights extends Integration {

	use Hookable;

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'monsterinsights_tracking_before', $this->get_callback( 'open_tracking_code' ), 10 );
		add_filter( 'monsterinsights_tracking_after', $this->get_callback( 'close_tracking_code' ), 10 );
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
	private function open_tracking_code() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo \Orejime\purpose_code_wrapper_start( $this->id );
	}

	/**
	 * Finishes wrapping the tracking script.
	 */
	private function close_tracking_code() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo \Orejime\purpose_code_wrapper_end();
	}
}
