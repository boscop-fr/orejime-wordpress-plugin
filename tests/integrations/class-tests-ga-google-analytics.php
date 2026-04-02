<?php
/**
 * GA Google Analytics integration tests.
 *
 * @package Orejime
 */

namespace Orejime;

use Orejime\Integration\GA_Google_Analytics;
use WP_UnitTestCase;

/**
 * GA Google Analytics integration tests.
 */
class Tests_GA_Google_Analytics extends WP_UnitTestCase {

	/**
	 * Tests if script tags are properly wrapped.
	 */
	public function test_wrap_scripts() {
		$purpose_id  = random_int( 1000, 2000 );
		$ga_id       = "G-OREJIME-$purpose_id";
		$integration = new GA_Google_Analytics( 'test', 'Test' );
		$integration->register();
		$integration->set_purpose( $purpose_id );

		add_filter(
			'ga_google_analytics_options_array',
			function ( $options ) use ( $ga_id ) {
				$options['tracking_id'] = $ga_id;
				return $options;
			}
		);

		ob_start();
		call_user_func( GA_Google_Analytics::TRACKING_CODE_CALLBACK );
		$code = ob_get_clean();

		ob_start();
		wp_head();
		$head = ob_get_clean();

		$this->assertStringContainsString(
			wrap_purpose_code( $code, $purpose_id ),
			$head
		);
	}
}
