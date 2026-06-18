<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Orejime
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );

if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require_once dirname( __DIR__ ) . '/orejime.php';
	require_once dirname( dirname( __DIR__ ) ) . '/ga-google-analytics/ga-google-analytics.php';
	require_once dirname( dirname( __DIR__ ) ) . '/google-analytics-for-wordpress/googleanalytics.php';
	require_once dirname( dirname( __DIR__ ) ) . '/google-site-kit/google-site-kit.php';
	require_once dirname( dirname( __DIR__ ) ) . '/jetpack/jetpack.php';
	require_once dirname( dirname( __DIR__ ) ) . '/matomo/matomo.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

tests_add_filter( 'orejime_register_default_integrations', '__return_false' );

tests_add_filter(
	'pre_site_option_matomo-site-id-1',
	function () {
		return 1;
	}
);

tests_add_filter(
	'pre_option_matomo-global-option',
	function () {
		return array(
			'track_mode'         => 'default',
			'track_codeposition' => 'head',
		);
	}
);

define( 'MATOMO_DEBUG', false );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
