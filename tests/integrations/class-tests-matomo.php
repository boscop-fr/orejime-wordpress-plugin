<?php
/**
 * Matomo integration tests.
 *
 * @package Orejime
 */

namespace Orejime;

use Orejime\Integration\Matomo;
use WP_UnitTestCase;
use WpMatomo;
use WpMatomo\TrackingCode\GeneratorOptions;
use WpMatomo\TrackingCode\TrackingCodeGenerator;

/**
 * Matomo integration tests.
 */
class Tests_Matomo extends WP_UnitTestCase {

	/**
	 * Tests if script tags are properly wrapped.
	 */
	public function test_wrap_scripts() {
		$integration = new Matomo( 'matomo', 'Matomo' );
		$integration->hook_up();

		ob_start();
		wp_head();
		$head = ob_get_clean();

		$generator = new TrackingCodeGenerator(
			WpMatomo::$settings,
			new GeneratorOptions( WpMatomo::$settings )
		);

		$this->assertStringContainsString(
			wrap_purpose_code( $generator->get_tracking_code(), $integration->id ),
			$head
		);
	}
}
