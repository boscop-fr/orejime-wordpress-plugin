<?php
/**
 * Google Site Kit integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Google Site Kit integration.
 */
class Orejime_Integration_Google_Site_Kit extends Orejime_Integration {

	const ANALYTICS_4_MODULE_SLUG = 'analytics-4';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'script_loader_tag', array( $this, 'wrap_script' ), 100, 2 );

		if ( WP_DEBUG ) {
			add_filter( 'googlesitekit_setup_gtag', array( $this, 'setup_test_tag' ), 10, 1 );
		}
	}

	/**
	 * {@inheritDoc}
	 */
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
	 * {@inheritDoc}
	 *
	 * @todo Find a way to provide a regex to Orejime, as
	 * cookie names are escaped if they are string.
	 */
	public function get_cookie_names() {
		return array(
			'_ga',
			'_ga_.*',
		);
	}

	/**
	 * Wraps the tracker initialisation code.
	 *
	 * @param string $tag HTML.
	 * @param string $handle Handle.
	 */
	public function wrap_script( $tag, $handle ) {
		if (
			class_exists( '\Google\Site_Kit\Core\Tags\GTag' )
			&& \Google\Site_Kit\Core\Tags\GTag::HANDLE === $handle
		) {
			return orejime_wrap_purpose_code( $tag, $this->purpose_id );
		}

		return $tag;
	}

	/**
	 * Sets up a dummy tag for testing purposes.
	 *
	 * @param \Google\Site_Kit\Core\Tags\GTag $gtag GTag.
	 */
	public function setup_test_tag( $gtag ) {
		$gtag->add_tag( 'orejime' );
	}
}
