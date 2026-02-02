<?php
/**
 * Integration.
 *
 * @package Orejime
 */

namespace Orejime;

/**
 * Base class for a plugin integration.
 * This allows Orejime to hook itself to other plugins as to
 * handle their scripts automatically.
 */
abstract class Integration {

	/**
	 * Unique id.
	 *
	 * @var string
	 * @readonly
	 */
	public string $id = 'none';

	/**
	 * Name.
	 *
	 * @var string
	 * @readonly
	 */
	public string $name = 'None';

	/**
	 * Id of the associated purpose.
	 *
	 * @var int
	 * @readonly
	 */
	public int $purpose_id;

	/**
	 * Initializes the integration.
	 *
	 * @param string $id Id.
	 * @param string $name Name.
	 */
	public function __construct( $id, $name ) {
		$this->id   = $id;
		$this->name = $name;
	}

	/**
	 * Sets the associated purpose.
	 *
	 * @param string $purpose_id Purpose id.
	 */
	public function set_purpose( $purpose_id ) {
		$this->purpose_id = $purpose_id;
	}

	/**
	 * Hooks everything up.
	 */
	public function register() {}

	/**
	 * Tells if the integration is currently active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return true;
	}

	/**
	 * Returns a list of cookie names set by the scripts.
	 *
	 * @return string[]
	 */
	public function get_cookie_names() {
		return array();
	}
}
