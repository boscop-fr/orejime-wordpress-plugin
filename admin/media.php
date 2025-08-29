<?php
/**
 * Orejime administration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

define( 'OREJIME_SETTING_CONTEXTUAL_CONSENT', 'orejime_contextual_consent' );

/**
 *  Tells if contextual consent is enabled.
 *
 * @return boolean
 */
function orejime_is_contextual_consent_enabled() {
	return ( '1' === get_option( OREJIME_SETTING_CONTEXTUAL_CONSENT ) );
}

/**
 *  Renders the input for the contextual consent setting.
 */
function orejime_contextual_consent_setting() {
	$name    = OREJIME_SETTING_CONTEXTUAL_CONSENT;
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
function orejime_register_media_settings() {
	add_settings_section(
		'orejime_media',
		'Orejime',
		null,
		'media'
	);

	add_settings_field(
		OREJIME_SETTING_CONTEXTUAL_CONSENT,
		'Contextual consent',
		'orejime_contextual_consent_setting',
		'media',
		'orejime_media'
	);

	register_setting( 'media', OREJIME_SETTING_CONTEXTUAL_CONSENT );
}

add_action( 'admin_init', 'orejime_register_media_settings' );
