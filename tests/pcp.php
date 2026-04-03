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
 * Excludes files & directories from `.distignore` so PCP
 * runs on the same set as a production build.
 * A "better" way would be using `wp dist-archive` to build
 * an actual production package to lint. However, this
 * proved to be quite complicated for a low return.
 */
WP_CLI::add_hook(
	'after_wp_load',
	function () {
		$root = dirname( __DIR__ );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$distignore = file_get_contents( "$root/.distignore" );
		$patterns   = array_filter( explode( PHP_EOL, $distignore ) );
		$files      = array_reduce(
			$patterns,
			function ( $all, $pattern ) use ( $root ) {
				$slashed        = str_starts_with( $pattern, '/' ) ? $pattern : "/$pattern";
				$paths          = glob( $root . $slashed, GLOB_MARK );
				$relative_paths = array_map( fn( $path ) => str_replace( "$root/", '', $path ), $paths );
				return array_merge( $all, $relative_paths );
			},
			array()
		);

		$ignored_files = array();
		$ignored_dirs  = array();

		foreach ( $files as $file ) {
			if ( ! str_ends_with( $file, '/' ) ) {
				$ignored_files[] = $file;
				continue;
			}

			$name = basename( $file );

			if ( '.' === $name || '..' === $name ) {
				continue;
			}

			$ignored_dirs[] = substr( $file, 0, -1 );
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
