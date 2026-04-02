<?php
/**
 * Orejime administration.
 *
 * @package Orejime
 */

namespace Orejime;

const CONTEXTUAL_CONSENT_SETTING = 'orejime_contextual_consent';

/**
 *  Tells if contextual consent is enabled.
 *
 * @return boolean
 */
function is_contextual_consent_enabled() {
	$is_enabled = ( '1' === get_option( CONTEXTUAL_CONSENT_SETTING ) );
	return apply_filters( 'orejime_is_contextual_consent_enabled', $is_enabled );
}

/**
 *  Renders the input for the contextual consent setting.
 */
function contextual_consent_setting() {
	$name    = CONTEXTUAL_CONSENT_SETTING;
	$checked = checked( 1, get_option( $name ), false );
	$html    = <<<HTML
		<label for="$name">
			<input
				id="$name"
				name="$name"
				type="checkbox"
				value="1"
				class="code"
				$checked
			/>

			Provide contextual consent for embeds
		</label>
	HTML;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $html;
}

/**
 * Registers Orejime settings.
 */
function register_media_settings() {
	add_settings_section(
		'orejime_media',
		'Orejime',
		null,
		'media'
	);

	add_settings_field(
		CONTEXTUAL_CONSENT_SETTING,
		'Contextual consent',
		__NAMESPACE__ . '\contextual_consent_setting',
		'media',
		'orejime_media'
	);

	register_setting( 'media', CONTEXTUAL_CONSENT_SETTING );
}

add_action( 'admin_init', __NAMESPACE__ . '\register_media_settings' );
