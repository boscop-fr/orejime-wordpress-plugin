<?php
/**
 * Google Site Kit Tag Manager integration.
 *
 * @package Orejime
 */

namespace Orejime\Integration\Google_Site_Kit\Module;

use Orejime\Hooks;
use Orejime\Integration\Google_Site_Kit\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Site Kit Tag Manager integration.
 */
class Tag_Manager extends Module {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public string $slug = 'tagmanager';

	/**
	 * {@inheritDoc}
	 *
	 * The user will have to list cookies manually, as there
	 * is no way to tell which cookies are actually set
	 * depending on the tag manager setup.
	 */
	public function get_cookie_names() {
		return array();
	}

	/**
	 * {@inheritDoc}
	 *
	 * Wraps original actions setup by the module to
	 * customize the output code.
	 */
	protected function init_tag( $tag_id ) {
		$callbacks = $this->get_tag_callbacks();

		foreach ( $callbacks as $callback ) {
			remove_action(
				$callback['action'],
				$callback['function'],
				$callback['priority']
			);

			add_action(
				$callback['action'],
				fn() => \Orejime\print_purpose_code(
					$callback['function'],
					$this->id
				),
				$callback['priority'],
				$callback['accepted_args']
			);
		}
	}

	/**
	 * Lists actions registered by the module to output
	 * tracking codes.
	 *
	 * This is way too tied to the module implementation,
	 * but it doesn't provide any hook to do this easily.
	 *
	 * @return array Actions.
	 */
	private function get_tag_callbacks() {
		$actions = array();

		Hooks::walk_actions(
			array( 'wp_head', 'wp_body_open', 'wp_footer' ),
			function ( $action, $priority, $callback, ) use ( &$actions ) {
				// We're searching for closures, introduced
				// by Method_Proxy_Trait::get_method_proxy().
				if ( ! is_object( $callback['function'] ) ) {
					return;
				}

				$accepted_args = $callback['accepted_args'] ?? 1;

				// Functions that render tracking codes
				// have no parameters. Still, the count
				// defaults to 1 when unspecified.
				if ( $accepted_args > 1 ) {
					return;
				}

				$function = new \ReflectionFunction( $callback['function'] );
				$class    = get_class( $function->getClosureThis() );

				if ( 'Google\Site_Kit\Modules\Tag_Manager\Web_Tag' === $class ) {
					$actions[] = array(
						'action'        => $action,
						'function'      => $callback['function'],
						'priority'      => $priority,
						'accepted_args' => $accepted_args,
					);
				}
			}
		);

		return $actions;
	}
}
