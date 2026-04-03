<?php
/**
 * Matomo integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration;

use Orejime\Hookable;
use Orejime\Integration;
use WpMatomo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matomo integration.
 */
class Matomo extends Integration {

	use Hookable;

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'matomo_tracking_code_script', $this->get_callback( 'wrap_script' ), 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		if ( ! class_exists( '\WpMatomo' ) ) {
			return false;
		}

		if ( ! WpMatomo::$settings ) {
			return false;
		}

		return WpMatomo::$settings->is_tracking_enabled()
			&& ! WpMatomo::$settings->get_global_option( 'disable_cookies' );
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
	private function wrap_script( $script ) {
		return \Orejime\wrap_purpose_code( $script, $this->id );
	}
}
