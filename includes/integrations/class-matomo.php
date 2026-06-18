<?php
/**
 * Matomo integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration;

use Orejime\Hookable;
use Orejime\Hooks;
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
		$actions = array();

		Hooks::walk_actions(
			array( 'wp_head', 'wp_footer' ),
			function ( $action, $priority, $callback ) use ( &$actions ) {
				if ( ! is_array( $callback['function'] ) ) {
					return;
				}

				list($instance, $method) = $callback['function'];

				if ( 'add_javascript_code' !== $method ) {
					return;
				}

				$class = new \ReflectionClass( $instance );

				if ( 'WpMatomo\TrackingCode' === $class->name ) {
					$actions[] = array(
						'action'        => $action,
						'function'      => $callback['function'],
						'priority'      => $priority,
						'accepted_args' => $callback['accepted_args'],
					);
				}
			}
		);

		foreach ( $actions as $action ) {
			remove_action(
				$action['action'],
				$action['function'],
				$action['priority']
			);

			add_action(
				$action['action'],
				fn() => \Orejime\print_purpose_code(
					$action['function'],
					$this->id
				),
				$action['priority'],
				$action['accepted_args']
			);
		}
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
}
