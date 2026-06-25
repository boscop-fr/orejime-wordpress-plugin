<?php
/**
 * Orejime administration.
 *
 * @package Orejime
 */

namespace Orejime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	?>
		<label for="orejime-contextual-consent">
			<input
				id="orejime-contextual-consent"
				name="<?php echo esc_attr( CONTEXTUAL_CONSENT_SETTING ); ?>"
				type="checkbox"
				value="1"
				class="code"
				<?php checked( '1', get_option( CONTEXTUAL_CONSENT_SETTING ) ); ?>
			/>

			<?php esc_html_e( 'Provide contextual consent for embeds', 'orejime' ); ?>
		</label>
	<?php
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

	register_setting(
		'media',
		CONTEXTUAL_CONSENT_SETTING,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
}

add_action( 'admin_init', __NAMESPACE__ . '\register_media_settings' );
