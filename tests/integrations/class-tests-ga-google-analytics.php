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
		$ga_id      = 'ga';
		$purpose_id = 12;

		add_filter(
			'ga_google_analytics_options_array',
			function ( $options ) use ( $ga_id ) {
				$options['gap_id'] = $ga_id;
				return $options;
			}
		);

		$code         = ga_google_analytics_universal();
		$wrapped_code = wrap_purpose_code( $code, $purpose_id );

		$integration = new GA_Google_Analytics( 'test', 'Test' );
		$integration->register();
		$integration->set_purpose( $purpose_id );

		ob_start();
		wp_head();
		$head = ob_get_clean();

		$this->assertStringContainsString(
			$wrapped_code,
			$head
		);
	}
}
