<?php

abstract class Orejime_Integration {

	/**
	 * Unique id.
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Name.
	 *
	 * @var string
	 */
	public string $name;

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
		return false;
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
