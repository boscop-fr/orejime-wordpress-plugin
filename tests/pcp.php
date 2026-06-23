<?php
/**
 * Plugin-check bootstrap file.
 *
 * @package Orejime
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Excludes files & directories given the npm packlist
 * config so PCP runs on the same set as a production build.
 * A "better" way would be using `wp dist-archive` to build
 * an actual production package to lint. However, this
 * proved to be quite complicated for a low return.
 */
WP_CLI::add_hook(
	'after_wp_load',
	function () {
		$root = dirname( __DIR__ );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$package = json_decode( file_get_contents( "$root/package.json" ) );
		$files   = new DirectoryIterator( $root );

		$ignored_files = array();
		$ignored_dirs  = array();

		foreach ( $files as $file ) {
			$basename = $file->getBasename();

			if ( in_array( $basename, $package->files, true ) ) {
				continue;
			}

			if ( $file->isDir() ) {
				$ignored_dirs[] = $basename;
			} else {
				$ignored_files[] = $basename;
			}
		}

		add_filter(
			'wp_plugin_check_ignore_files',
			fn ( $files ) => array_merge( $files, $ignored_files )
		);

		add_filter(
			'wp_plugin_check_ignore_directories',
			fn ( $dirs ) => array_merge( $dirs, $ignored_dirs )
		);
	}
);
