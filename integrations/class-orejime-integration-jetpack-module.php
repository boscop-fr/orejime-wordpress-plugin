<?php
/**
 * Jetpack integration.
 *
 * @package Orejime
 */

/**
 * Jetpack integration.
 */
class Orejime_Integration_Jetpack_Module extends Orejime_Integration {

	/**
	 * Module slug.
	 *
	 * @var string
	 * @readonly
	 */
	public string $slug;

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return class_exists( 'JetPack' ) && Jetpack::is_module_active( $this->slug );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return array();
	}
}
