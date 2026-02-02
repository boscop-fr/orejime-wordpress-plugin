<?php
/**
 * Jetpack integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration\Jetpack;

use Orejime\Integration;
use Jetpack;

/**
 * Jetpack integration.
 */
class Module extends Integration {

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
		return class_exists( '\JetPack' ) && Jetpack::is_module_active( $this->slug );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return array();
	}
}
