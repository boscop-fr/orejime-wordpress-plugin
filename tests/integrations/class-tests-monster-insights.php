<?php
/**
 * Monster Insights integration tests.
 *
 * @package Orejime
 */

namespace Orejime;

use DOMDocument;
use DOMXPath;
use Orejime\Integration\Monster_Insights;
use WP_UnitTestCase;

/**
 * Monster Insights integration tests.
 */
class Tests_Monster_Insights extends WP_UnitTestCase {

	/**
	 * Tests if script tags are properly wrapped.
	 */
	public function test_wrap_scripts() {
		$purpose_id  = random_int( 1000, 2000 );
		$ga_id       = "G-OREJIME-$purpose_id";
		$integration = new Monster_Insights( 'test', 'Test' );
		$integration->register();
		$integration->set_purpose( $purpose_id );

		add_filter( 'monsterinsights_skip_tracking', '__return_false' );
		add_filter( 'monsterinsights_get_v4_id_to_output', fn () => $ga_id );

		ob_start();
		wp_head();
		$head = ob_get_clean();

		// We're disabling libxml errors since it doesn't
		// play well with <template> elements.
		$dom        = new DOMDocument();
		$use_errors = libxml_use_internal_errors( true );
		$dom->loadHTML( $head );
		libxml_use_internal_errors( $use_errors );

		$xpath    = new DOMXPath( $dom );
		$elements = $xpath->query( "//template[@data-purpose=$purpose_id]" );

		$this->assertEquals( 1, $elements->length );
		$this->assertStringContainsString(
			$ga_id,
			$elements->item( 0 )->nodeValue
		);
	}
}
