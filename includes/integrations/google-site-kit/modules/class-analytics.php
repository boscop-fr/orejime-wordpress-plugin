<?php
/**
 * Google Site Kit Analytics integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration\Google_Site_Kit\Module;

use Google\Site_Kit\Core\Tags\GTag;
use Orejime\Hookable;
use Orejime\Integration\Google_Site_Kit\Module;

use function Orejime\list_ga_cookies;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Site Kit Analytics integration.
 */
class Analytics extends Module {

	use Hookable;

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public string $slug = 'analytics-4';

	/**
	 * Tag id.
	 *
	 * @var string
	 */
	private string $tag_id;

	/**
	 * {@inheritDoc}
	 */
	public function get_cookie_names() {
		return list_ga_cookies( $this->tag_id );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function init_tag( $tag_id ) {
		$this->tag_id = $tag_id;

		add_filter( 'script_loader_tag', $this->get_callback( 'wrap_script' ), 100, 2 );
	}

	/**
	 * Wraps the tracker initialisation code.
	 *
	 * @param string $tag HTML.
	 * @param string $handle Handle.
	 */
	private function wrap_script( $tag, $handle ) {
		if (
			class_exists( '\Google\Site_Kit\Core\Tags\GTag' )
			&& Gtag::HANDLE === $handle
		) {
			return \Orejime\wrap_purpose_code( $tag, $this->id );
		}

		return $tag;
	}
}
