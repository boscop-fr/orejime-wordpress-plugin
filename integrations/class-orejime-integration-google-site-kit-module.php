<?php
/**
 * Google Site Kit integration.
 *
 * @package Orejime
 */

/**
 * Google Site Kit integration.
 */
abstract class Orejime_Integration_Google_Site_Kit_Module extends Orejime_Integration {

	use Orejime_Hookable;

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
	public function register() {
		add_action( "googlesitekit_{$this->slug}_init_tag", $this->get_callback( 'init_tag' ), 10, 1 );

		if ( WP_DEBUG ) {
			add_filter( 'googlesitekit_setup_gtag', $this->get_callback( 'setup_test_tag' ), 10, 1 );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return apply_filters(
			'googlesitekit_is_module_connected',
			false,
			$this->slug
		);
	}

	/**
	 * Initialization logic for tags that will actually be
	 * rendered.
	 *
	 * @param string $tag_id Tag id.
	 */
	abstract protected function init_tag( $tag_id );

	/**
	 * Sets up a dummy tag for testing purposes.
	 *
	 * @param \Google\Site_Kit\Core\Tags\GTag $gtag GTag.
	 */
	private function setup_test_tag( $gtag ) {
		$gtag->add_tag( 'orejime-' . $this->slug );
	}
}
