<?php
/**
 * Purpose tree tests.
 *
 * @package Orejime
 */

namespace Orejime;

use WP_UnitTestCase;

/**
 * Purpose tree tests.
 */
class Tests_Purpose_Tree extends WP_UnitTestCase {

	/**
	 * Tests if children are nested under their parent.
	 */
	public function test_nest_children() {
		$group = wp_insert_term( 'Group', Purpose_Taxonomy::NAME );
		wp_insert_term( 'Child', Purpose_Taxonomy::NAME, array( 'parent' => $group['term_id'] ) );

		$tree = ( new Purpose_Taxonomy() )->get_purpose_tree();

		$this->assertSame( array( 'group' ), wp_list_pluck( $tree, 'id' ) );
		$this->assertSame( array( 'child' ), wp_list_pluck( $tree[0]->purposes, 'id' ) );
	}
}
