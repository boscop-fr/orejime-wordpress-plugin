<?php
/**
 * Embed blocks integration.
 *
 * @package Orejime
 */

/**
 * Embed blocks integration.
 */
class Orejime_Integration_Core_Embed_Block extends Orejime_Integration {

	use Orejime_Hookable;

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'render_block', $this->get_callback( 'wrap_block' ), 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_active() {
		return orejime_is_contextual_consent_enabled();
	}

	/**
	 * Wraps embed blocks within a template tag for Orejime to
	 * provide contextual consent notices.
	 *
	 * @param string $content Content.
	 * @param array  $block Block.
	 */
	private function wrap_block( $content, $block ) {
		if ( 'core/embed' !== $block['blockName'] ) {
			return $content;
		}

		if ( ! $this->is_active() ) {
			return $content;
		}

		return orejime_wrap_purpose_code( $content, $this->purpose_id, true );
	}
}
