<?php
/**
 * Embed blocks integration tests.
 *
 * @package Orejime
 */

namespace Orejime;

use Orejime\Integration\Core_Embed_Block;
use WP_UnitTestCase;

/**
 * Embed blocks integration tests.
 */
class Tests_Core_Embed_Block extends WP_UnitTestCase {

	/**
	 * Tests if embed blocks are properly wrapped.
	 */
	public function test_wrap_block() {
		$purpose_id = 12;
		$embed_code = '<iframe></iframe>';

		$integration = new Core_Embed_Block( 'test', 'Test' );
		$integration->register();
		$integration->set_purpose( $purpose_id );

		$content = render_block(
			array(
				'blockName'    => 'core/embed',
				'attrs'        => array(),
				'innerContent' => array( $embed_code ),
				'innerHTML'    => $embed_code,
			)
		);

		$this->assertEquals(
			wrap_purpose_code( $embed_code, $purpose_id, true ),
			$content
		);
	}
}
