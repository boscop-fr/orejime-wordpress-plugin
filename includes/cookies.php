<?php
/**
 * Cookies.
 *
 * @package Orejime
 */

namespace Orejime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lists names and patterns to match cookies set by Google
 * Analytics.
 *
 * @param string $id Account Id.
 * @see https://cookiedatabase.org/service/google-analytics
 */
function list_ga_cookies( ?string $id = null ) {
	return array_merge(
		array(
			'_ga',
			'_gat',
			'_gid',
			'/^__utm[a-z]$/',
		),
		$id ? array(
			'_ga_' . $id,
			'_gat_' . $id,
		) : array(
			'/^_ga_.*$/',
			'/^_gat_.*$/',
		)
	);
}
