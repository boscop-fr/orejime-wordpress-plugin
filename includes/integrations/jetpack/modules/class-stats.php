<?php
/**
 * Jetpack Stats integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration\Jetpack\Module;

use Orejime\Integration\Jetpack\Module;
use Orejime\Hookable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Stats integration.
 */
class Stats extends Module {

	use Hookable;

	const SCRIPT_HANDLE = 'jetpack-stats';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public string $slug = 'stats';

	/**
	 * {@inheritDoc}
	 *
	 * @todo Handle AMP (Tracking_Pixel::add_amp_pixel()).
	 */
	public function register() {
		add_filter( 'script_loader_tag', $this->get_callback( 'wrap_script' ), 10, 2 );
	}

	/**
	 * Wraps the tracker initialisation code.
	 *
	 * @param string $tag HTML.
	 * @param string $handle Handle.
	 */
	private function wrap_script( $tag, $handle ) {
		if ( self::SCRIPT_HANDLE === $handle ) {
			return \Orejime\wrap_purpose_code( $tag, $this->id );
		}

		return $tag;
	}
}
