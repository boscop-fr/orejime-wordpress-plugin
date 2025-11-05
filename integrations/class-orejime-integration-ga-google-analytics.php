<?php
/**
 * GA Google Analytics integration.
 *
 * @package Orejime
 */

/**
 * GA Google Analytics integration.
 */
class Orejime_Integration_GA_Google_Analytics extends Orejime_Integration {

	use Orejime_Hookable;

	const SCRIPT_HANDLE = 'ga-google-analytics';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'ga_google_analytics_script_atts', $this->get_callback( 'script_attributes' ), 100 );
		add_filter( 'ga_google_analytics_script_atts_ext', $this->get_callback( 'script_attributes' ), 100 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return class_exists( 'GA_Google_Analytics' );
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
	 * Adds attributes to the script tags so they can be
	 * picked up and wrapped automatically.
	 *
	 * @param string $attrs Attributes.
	 * @return string Attributes.
	 */
	private function script_attributes( $attrs ) {
		return $attrs . ' ' . orejime_auto_wrap_attribute( $this->purpose_id );
	}
}
