<?php
/**
 * Monster Insights integration.
 *
 * @package WordPress
 * @subpackage Orejime
 * @see https://github.com/awesomemotive/google-analytics-for-wordpress
 */

define( 'OREJIME_MONSTER_INSIGHTS_PURPOSE_ID', 'wp-orejime-monster-insights' );

/**
 * Tells if the Monster Insights plugin is installed and enabled.
 *
 * @return boolean
 */
function orejime_is_monster_insights_plugin_active() {
	return is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' );
}

/**
 * Adds relevant purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_monster_insights_purposes( array $purposes ) {
	if ( orejime_is_monster_insights_plugin_active() ) {
		$purposes [] = array(
			'id'    => OREJIME_MONSTER_INSIGHTS_PURPOSE_ID,
			'title' => 'Google analytics',
		);
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_monster_insights_purposes' );

/**
 * Starts wrapping the tracking script.
 */
function orejime_start_monster_insights_tracking_code() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo orejime_purpose_code_wrapper_start(
		OREJIME_MONSTER_INSIGHTS_PURPOSE_ID
	);
}

add_filter( 'monsterinsights_tracking_before', 'orejime_start_monster_insights_tracking_code', 10 );

/**
 * Ends the tracking script wrapper.
 */
function orejime_end_monster_insights_tracking_code() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo orejime_purpose_code_wrapper_end();
}

add_filter( 'monsterinsights_tracking_after', 'orejime_end_monster_insights_tracking_code', 10 );
