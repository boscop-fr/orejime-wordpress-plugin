<?php
/**
 * Gutemberg integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Returns a unique purpose identifier for the core embed
 * block.
 *
 * @return string
 */
function orejime_embed_core_block_purpose_id() {
	return orejime_purpose_id( 'core-embed' );
}

/**
 * Wraps embed blocks within a template tag for Orejime to
 * provide contextual consent notices.
 *
 * @param string $content Content.
 * @param array  $block Block.
 */
function orejime_render_embed_block( $content, $block ) {
	if ( 'core/embed' !== $block['blockName'] ) {
		return $content;
	}

	if ( ! orejime_is_contextual_consent_enabled() ) {
		return $content;
	}

	return orejime_wrap_purpose_code( $content, orejime_embed_core_block_purpose_id(), true );
}

add_filter( 'render_block', 'orejime_render_embed_block', 10, 2 );
