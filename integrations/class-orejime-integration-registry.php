<?php
/**
 * Integration registry.
 *
 * @package WordPress
 * @subpackage Orejime
 */

/**
 * Stores registered integrations.
 */
class Orejime_Integration_Registry {

	/**
	 * Integrations.
	 *
	 * @var array
	 */
	private array $integrations = array();

	/**
	 * Registers an integration.
	 *
	 * @param Orejime_Integration $integration Integration.
	 * @throws Exception When the integration is already registered.
	 */
	public function register( Orejime_Integration $integration ) {
		if ( isset( $this->integrations[ $integration->id ] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( "Integration `{$integration->id}` is already registered." );
		}

		if ( $integration->is_active() ) {
			$integration->register();
		}

		$this->integrations[ $integration->id ] = $integration;
	}

	/**
	 * Returns all registered integrations.
	 *
	 * @return Orejime_Integration[] Integrations.
	 */
	public function get_all() {
		return array_values( $this->integrations );
	}

	/**
	 * Returns all active integrations.
	 *
	 * @return Orejime_Integration[] Integrations.
	 */
	public function get_active() {
		return array_values(
			array_filter( $this->integrations, fn ( $i ) => $i->is_active() )
		);
	}

	/**
	 * Returns all inactive integrations.
	 *
	 * @return Orejime_Integration[] Integrations.
	 */
	public function get_inactive() {
		return array_values(
			array_filter( $this->integrations, fn ( $i ) => ! $i->is_active() )
		);
	}

	/**
	 * Finds a registered integration by id.
	 *
	 * @param string $id Integration id.
	 * @return Orejime_Integration|null Integration.
	 */
	public function get( $id ) {
		return $this->integrations[ $id ] ?? null;
	}
}
