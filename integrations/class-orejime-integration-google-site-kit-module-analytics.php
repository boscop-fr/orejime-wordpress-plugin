<?php
/**
 * Google Site Kit Analytics integration.
 *
 * @package Orejime
 */

/**
 * Google Site Kit Analytics integration.
 */
class Orejime_Integration_Google_Site_Kit_Module_Analytics extends Orejime_Integration_Google_Site_Kit_Module {

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
	 *
	 * @see https://support.google.com/analytics/answer/11397207
	 */
	public function get_cookie_names() {
		$cookies = array( '_ga' );

		if ( $this->tag_id ) {
			$cookies[] = '_ga_' . $this->tag_id;
		}

		return $cookies;
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
			&& \Google\Site_Kit\Core\Tags\GTag::HANDLE === $handle
		) {
			return orejime_wrap_purpose_code( $tag, $this->purpose_id );
		}

		return $tag;
	}
}
