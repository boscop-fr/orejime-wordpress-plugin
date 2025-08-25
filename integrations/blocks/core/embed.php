<?php
/**
 * Gutemberg integration.
 *
 * @package WordPress
 * @subpackage Orejime
 */

define( 'OREJIME_EMBED_BLOCK_PURPOSE_ID', 'wp-orejime-embed-block' );

/**
 * Adds relevant purposes to the list.
 *
 * @param array $purposes Purposes.
 * @return array Purposes.
 */
function orejime_enqueue_embed_block_purposes( array $purposes ) {
	if ( orejime_is_contextual_consent_enabled() ) {
		$purposes [] = array(
			'id'    => OREJIME_EMBED_BLOCK_PURPOSE_ID,
			'title' => 'Embeds',
		);
	}

	return $purposes;
}

add_filter( 'orejime_enqueue_purposes', 'orejime_enqueue_embed_block_purposes' );

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

	return orejime_wrap_purpose_code( $content, OREJIME_EMBED_BLOCK_PURPOSE_ID, true );
}

add_filter( 'render_block', 'orejime_render_embed_block', 10, 2 );
