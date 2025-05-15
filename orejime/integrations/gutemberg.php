<?php
/**
 * Gutemberg integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Wraps embed blocks within a template tag for Orejime to
 * provide contextual consent notices.
 *
 * @param string $content Content.
 * @param array  $block Block.
 */
function orejime_render_embed_blocks( $content, $block ) {
	if ( 'core/embed' !== $block['blockName'] ) {
		return $content;
	}

	if ( ! orejime_is_contextual_consent_enabled() ) {
		return $content;
	}

	return orejime_wrap_purpose_code( $content, orejime_purpose_id( 'embeds' ), true );
}

add_filter( 'render_block', 'orejime_render_embed_blocks', 10, 2 );
