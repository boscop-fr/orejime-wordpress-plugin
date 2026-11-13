<?php
/**
 * Sanitization tests.
 *
 * @package Orejime
 */

namespace Orejime;

use WP_UnitTestCase;

/**
 * Sanitization tests.
 */
class Tests_Sanitization extends WP_UnitTestCase {

	/**
	 * Tests if wrappers survive kses filtering.
	 */
	public function test_kses_keeps_wrapper() {
		$code = wrap_purpose_code( '<p>Content</p>', 'purpose', true );

		$this->assertSame( $code, wp_kses_post( $code ) );
	}

	/**
	 * Tests if wrapped content is still filtered by kses.
	 */
	public function test_kses_filters_wrapped_content() {
		$code = wrap_purpose_code( '<script>alert(1)</script>', 'purpose', true );

		$this->assertSame(
			wrap_purpose_code( 'alert(1)', 'purpose', true ),
			wp_kses_post( $code )
		);
	}
}
