<?php
/**
 * GA Google Analytics integration tests.
 *
 * @package Orejime
 */

/**
 * GA Google Analytics integration tests.
 */
class Tests_Orejime_Integration_GA_Google_Analytics extends WP_UnitTestCase {

	/**
	 * Tests if embed blocks are properly wrapped.
	 */
	public function test_wrap_block() {
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
		$wrapped_code = orejime_wrap_purpose_code( $code, $purpose_id );

		$integration = new Orejime_Integration_GA_Google_Analytics( 'test', 'Test' );
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
