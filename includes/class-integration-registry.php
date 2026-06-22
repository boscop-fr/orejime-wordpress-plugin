<?php
/**
 * Integration registry.
 *
 * @package Orejime
 */

namespace Orejime;

use Exception;

/**
 * Stores registered integrations.
 */
class Integration_Registry {

	/**
	 * Integrations.
	 *
	 * @var array
	 */
	private array $integrations = array();

	/**
	 * Registers an integration.
	 *
	 * @param Integration $integration Integration.
	 * @throws Exception When the integration is already registered.
	 */
	public function register( Integration $integration ) {
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
	 * @return Integration[] Integrations.
	 */
	public function get_all() {
		return array_values( $this->integrations );
	}

	/**
	 * Returns all active integrations.
	 *
	 * @return Integration[] Integrations.
	 */
	public function get_active() {
		return array_values(
			array_filter( $this->integrations, fn ( $i ) => $i->is_active() )
		);
	}

	/**
	 * Finds a registered integration by id.
	 *
	 * @param string $id Integration id.
	 * @return Integration|null Integration.
	 */
	public function get( $id ) {
		return $this->integrations[ $id ] ?? null;
	}
}
