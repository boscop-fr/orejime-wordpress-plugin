<?php
/**
 * Hooks utilities.
 *
 * @package Orejime
 */

namespace Orejime;

/**
 * Hooks utilities.
 */
class Hooks {

	/**
	 * Iterates over every hook registered for the given
	 * action names.
	 *
	 * @param string[] $actions Action names.
	 * @param callable $walk Callback.
	 */
	public static function walk_actions( array $actions, callable $walk ) {
		global $wp_filter;

		foreach ( $actions as $action ) {
			if ( ! isset( $wp_filter[ $action ] ) ) {
				continue;
			}

			$callbacks = $wp_filter[ $action ]->callbacks;

			foreach ( $callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					$walk( $action, $priority, $callback );
				}
			}
		}
	}
}
