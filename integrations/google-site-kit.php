<?php
/**
 * Google Site Kit integration.
 *
 * @package WordPress
 * @subpackage Orejime
 * @see https://github.com/google/site-kit-wp
 */

define( 'OREJIME_GOOGLE_SITE_KIT_PURPOSE_ID', 'wp-orejime-google-site-kit' );

/**
 * Tells if the Site Kit plugin is installed and enabled.
 *
 * @return boolean
 */
function orejime_is_google_site_kit_plugin_active() {
	return is_plugin_active( 'google-site-kit/google-site-kit.php' );
}

/**
 * Adds relevant purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_google_site_kit_purposes( array $purposes ) {
	if ( orejime_is_google_site_kit_plugin_active() ) {
		$purposes [] = array(
			'id'    => OREJIME_GOOGLE_SITE_KIT_PURPOSE_ID,
			'title' => 'Google analytics',
		);
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_google_site_kit_purposes' );

/**
 * Wraps the tracker initialisation code.
 *
 * @param string $tag HTML.
 * @param string $handle Handle.
 */
function orejime_wrap_google_site_kit_tracking_code( $tag, $handle ) {
	try {
		if ( \Google\Site_Kit\Core\Tags\GTag::HANDLE === $handle ) {
			return orejime_wrap_purpose_code(
				$tag,
				OREJIME_GOOGLE_SITE_KIT_PURPOSE_ID
			);
		}
	} catch ( \Throwable ) {
	}

	return $tag;
}

add_filter( 'script_loader_tag', 'orejime_wrap_google_site_kit_tracking_code', 100, 2 );

if ( WP_DEBUG ) {
	/**
	 * Sets up a dummy tag for testing purposes.
	 *
	 * @param \Google\Site_Kit\Core\Tags\GTag $gtag GTag.
	 */
	function orejime_setup_test_google_site_kit_tag( $gtag ) {
		$gtag->add_tag( 'orejime' );
	}

	add_filter( 'googlesitekit_setup_gtag', 'orejime_setup_test_google_site_kit_tag', 10, 1 );
}
