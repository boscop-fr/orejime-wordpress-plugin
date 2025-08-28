<?php

class Orejime_Integration_Google_Site_Kit extends Orejime_Integration {

	const ANALYTICS_4_MODULE_SLUG = 'analytics-4';

	public string $id   = 'google-site-kit';
	public string $name = 'Google Site Kit';

	private ?WP_Term $term = null;

	public function register() {
		$this->term = orejime_register_integration_purpose_term( $this );

		add_filter( 'script_loader_tag', array( $this, 'wrap_tracking_code' ), 100, 2 );

		if ( WP_DEBUG ) {
			add_filter( 'googlesitekit_setup_gtag', array( $this, 'setup_test_tag' ), 10, 1 );
		}
	}

	public function is_active() {
		return $this->is_module_active( self::ANALYTICS_4_MODULE_SLUG );
	}

	/**
	 * Tells if the given Site Kit module is connected.
	 *
	 * @param string $module_slug Slug.
	 * @return boolean
	 */
	public function is_module_active( $module_slug ) {
		return apply_filters(
			'googlesitekit_is_module_connected',
			false,
			$module_slug
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_cookie_names() {
		return array();
	}

	/**
	 * Wraps the tracker initialisation code.
	 *
	 * @param string $tag HTML.
	 * @param string $handle Handle.
	 */
	function wrap_tracking_code( $tag, $handle ) {
		if (
			$this->term
			&& class_exists( '\Google\Site_Kit\Core\Tags\GTag' )
			&& \Google\Site_Kit\Core\Tags\GTag::HANDLE === $handle
		) {
			// We're using the term id as to not disclose
			// the actual integration name.
			return orejime_wrap_purpose_code( $tag, $this->term->id );
		}

		return $tag;
	}

	/**
	 * Sets up a dummy tag for testing purposes.
	 *
	 * @param \Google\Site_Kit\Core\Tags\GTag $gtag GTag.
	 */
	function setup_test_tag( $gtag ) {
		$gtag->add_tag( 'orejime' );
	}
}
