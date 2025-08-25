<?php
/**
 * Monster Insights integration.
 *
 * @package WordPress
 * @subpackage Orejime
 * @see https://github.com/awesomemotive/google-analytics-for-wordpress
 */

/**
 * Tells if the Monster Insights plugin is installed and enabled.
 *
 * @return boolean
 */
function orejime_is_monster_insights_plugin_active() {
	return is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' );
}

/**
 * Returns a unique purpose identifier for Monster Insights.
 *
 * @return string
 */
function orejime_monster_insights_purpose_id() {
	return orejime_purpose_id( 'monster-insights' );
}

/**
 * Starts wrapping the tracking script.
 */
function orejime_start_monster_insights_tracking_code() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo orejime_purpose_code_wrapper_start(
		orejime_monster_insights_purpose_id()
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
