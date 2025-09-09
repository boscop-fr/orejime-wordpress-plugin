<?php
/**
 * Orejime hookable trait.
 *
 * @package Orejime
 */

/**
 * Allows using private methods as hook callbacks.
 */
trait Orejime_Hookable {

	/**
	 * Proxifies the given method so that it can be used
	 * as a hook callback.
	 *
	 * @param string $method Method name.
	 * @return callable A proxy function.
	 */
	private function get_callback( $method ) {
		return fn ( ...$args ) => $this->{ $method }( ...$args );
	}
}
