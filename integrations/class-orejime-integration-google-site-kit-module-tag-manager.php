<?php
/**
 * Google Site Kit Tag Manager integration.
 *
 * @package Orejime
 */

/**
 * Google Site Kit Tag Manager integration.
 */
class Orejime_Integration_Google_Site_Kit_Module_Tag_Manager extends Orejime_Integration_Google_Site_Kit_Module {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public string $slug = 'tagmanager';

	/**
	 * {@inheritDoc}
	 *
	 * @todo Find a way to provide a regex to Orejime, as
	 * cookie names are escaped if they are string.
	 */
	public function get_cookie_names() {
		return array(
			'_ga',
			'_ga_*',
		);
	}

	/**
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
				fn() => orejime_print_purpose_code(
					$callback['function'],
					$this->purpose_id
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
		global $wp_filter;

		$actions       = array( 'wp_head', 'wp_body_open', 'wp_footer' );
		$tag_class     = 'Google\Site_Kit\Modules\Tag_Manager\Web_Tag';
		$tag_callbacks = array();

		foreach ( $actions as $action ) {
			if ( ! isset( $wp_filter[ $action ] ) ) {
				continue;
			}

			$callbacks = $wp_filter[ $action ]->callbacks;

			foreach ( $callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					// We're searching for closures, introduced
					// by Method_Proxy_Trait::get_method_proxy().
					if ( ! is_object( $callback['function'] ) ) {
						continue;
					}

					$accepted_args = $callback['accepted_args'] ?? 1;

					// Functions that render tracking codes
					// have no parameters. Still, the count
					// defaults to 1 when unspecified.
					if ( $accepted_args > 1 ) {
						continue;
					}

					$function = new ReflectionFunction( $callback['function'] );
					$class    = get_class( $function->getClosureThis() );

					if ( $class === $tag_class ) {
						$tag_callbacks[] = array(
							'action'        => $action,
							'function'      => $callback['function'],
							'priority'      => $priority,
							'accepted_args' => $accepted_args,
						);
					}
				}
			}
		}

		return $tag_callbacks;
	}
}
