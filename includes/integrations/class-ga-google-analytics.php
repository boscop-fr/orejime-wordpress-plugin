<?php
/**
 * GA Google Analytics integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration;

use Orejime\Hookable;
use Orejime\Integration;

/**
 * GA Google Analytics integration.
 */
class GA_Google_Analytics extends Integration {

	use Hookable;

	const TRACKING_CODE_CALLBACK = 'ga_google_analytics_tracking_code';

	/**
	 * Wraps the original action setup by the plugin.
	 */
	public function register() {
		$this->wrap_action( 'admin_head' );
		$this->wrap_action( 'wp_head' );
		$this->wrap_action( 'admin_footer' );
		$this->wrap_action( 'wp_footer' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return class_exists( '\GA_Google_Analytics' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return array(
			'_ga',
			'_ga_*',
		);
	}

	/**
	 * Wraps the original action setup by the plugin if it
	 * is registered under the given hook name.
	 *
	 * @param string $hook_name Hook name.
	 */
	private function wrap_action( $hook_name ) {
		if ( has_action( $hook_name, self::TRACKING_CODE_CALLBACK ) ) {
			remove_action( $hook_name, self::TRACKING_CODE_CALLBACK );
			add_action(
				$hook_name,
				fn() => \Orejime\print_purpose_code(
					self::TRACKING_CODE_CALLBACK,
					$this->purpose_id
				)
			);
		}
	}
}
