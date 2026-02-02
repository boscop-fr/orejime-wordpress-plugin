<?php
/**
 * Purpose taxonomy with integrations.
 *
 * @package Orejime
 */

namespace Orejime;

use Exception;
use WP_Term;

/**
 * Custom taxonomy for purposes with support for integrations.
 */
class Purpose_Taxonomy_Integrated extends Purpose_Taxonomy {

	use Hookable;

	const INTEGRATION_FIELD  = 'orejime_integration_id';
	const INTEGRATION_PREFIX = 'orejime_integration_';

	/**
	 * Integration registry.
	 *
	 * @var Integration_Registry
	 */
	private Integration_Registry $integrations;

	/**
	 * Initializes the taxonomy.
	 *
	 * @param Integration_Registry $integrations Integration registry.
	 */
	public function __construct( Integration_Registry $integrations ) {
		$this->integrations = $integrations;
	}

	/**
	 * Hooks everything up.
	 */
	public function register() {
		parent::register();

		add_action( 'init', $this->get_callback( 'setup_integrations' ), PHP_INT_MAX );

		add_filter( 'manage_edit-' . self::NAME . '_columns', $this->get_callback( 'add_integration_table_column' ) );
		add_filter( 'manage_' . self::NAME . '_custom_column', $this->get_callback( 'fill_custom_table_column' ), 10, 3 );
		add_filter( 'user_has_cap', $this->get_callback( 'user_capabilities' ), 10, 4 );

		add_filter( 'get_terms_args', $this->get_callback( 'exclude_inactive_terms' ), 10, 2 );
	}

	/**
	 * Registers all integrations.
	 *
	 * @todo Properly handle errors and find a way to report
	 * them to within the interface.
	 */
	private function setup_integrations() {
		foreach ( $this->integrations->get_all() as $integration ) {
			try {
				$id = $this->register_integration_term( $integration );
				$integration->set_purpose( $id );
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( Exception $e ) {
				// See @todo.
			}
		}
	}

	/**
	 * Registers a purpose to be managed by a given integration.
	 *
	 * @param Integration $integration Integration.
	 * @return int Term id.
	 */
	private function register_integration_term( Integration $integration ) {
		return $this->get_integration_term( $integration )
			?? $this->create_integration_term( $integration );
	}

	/**
	 * Creates a term to be managed by a given integration.
	 *
	 * @param Integration $integration Integration.
	 * @return int Term id.
	 * @throws Exception When the term couldn't be created.
	 */
	private function create_integration_term( Integration $integration ) {
		$term = wp_insert_term(
			$integration->name,
			self::NAME,
			array(
				'slug' => $this->term_slug( $integration->id ),
			)
		);

		if ( is_wp_error( $term ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( "Unable to create term for integration `{$integration->id}`" );
		}

		return $term['term_id'];
	}

	/**
	 * Finds the term managed by the given integration, if any.
	 *
	 * @param Integration $integration Integration.
	 * @return int Term id.
	 */
	private function get_integration_term( Integration $integration ) {
		$term = get_term_by(
			'slug',
			$this->term_slug( $integration->id ),
			self::NAME
		);

		if ( ! $term ) {
			return null;
		}

		return $term->term_id;
	}

	/**
	 * Finds the integration managing the given term, if any.
	 *
	 * @param int|WP_Term $term Term or term id.
	 * @return Integration|null Integration.
	 */
	private function get_term_integration( $term ) {
		$term = get_term( $term );

		if ( strpos( $term->slug, self::INTEGRATION_PREFIX ) !== 0 ) {
			return null;
		}

		$integration_id = substr( $term->slug, strlen( self::INTEGRATION_PREFIX ) );
		return $this->integrations->get( $integration_id );
	}

	/**
	 * Generates a unique slug from the given integration id.
	 * This way, we can store the relationship between terms
	 * and integrations in a direct way without using a term
	 * meta, which would complexify and slow down queries.
	 *
	 * @param int $integration_id Integration id.
	 */
	private function term_slug( $integration_id ) {
		return self::INTEGRATION_PREFIX . $integration_id;
	}

	/**
	 * Excludes purpose terms associated with inactive
	 * integrations.
	 *
	 * @param array $query Query.
	 * @param array $taxonomies Taxonomies.
	 * @return array Query.
	 */
	private function exclude_inactive_terms( $query, $taxonomies ) {
		if ( ! in_array( self::NAME, $taxonomies, true ) ) {
			return $query;
		}

		$inactive_ids = array_filter(
			array_map(
				fn ( $i ) => $i->purpose_id ?? null,
				$this->integrations->get_inactive()
			)
		);

		if ( empty( $inactive_ids ) ) {
			return $query;
		}

		$query['exclude'] = empty( $query['exclude'] )
			? $inactive_ids
			: array_unique( array_merge( $query['exclude'], $inactive_ids ) );

		return $query;
	}

	/**
	 * Adds a column to show integrations managing purposes.
	 *
	 * @param array $columns Columns.
	 * @return array Columns.
	 */
	private function add_integration_table_column( $columns ) {
		$columns[ self::INTEGRATION_FIELD ] = __( 'Plugin integration', 'orejime' );

		return $columns;
	}

	/**
	 * Fills custom columns.
	 *
	 * @param string $output Output.
	 * @param string $column_name Column name.
	 * @param int    $term_id Term id.
	 * @return string Outputs.
	 */
	private function fill_custom_table_column( $output, $column_name, $term_id ) {
		switch ( $column_name ) {
			case self::INTEGRATION_FIELD:
				$integration = $this->get_term_integration( $term_id );
				return $integration ? $integration->name : '—';

			default:
				return $output;
		}
	}

	/**
	 * Adjusts user capabilities so they can't delete purposes
	 * managed by the plugin's integrations.
	 *
	 * @param array $all_caps All user capabilities.
	 * @param array $required_caps Required primitive capabilities.
	 * @param array $args Additional args.
	 * @return array Updated capabilities.
	 */
	private function user_capabilities( array $all_caps, array $required_caps, array $args ) {
		if ( count( $args ) < 3 ) {
			return $all_caps;
		}

		list($cap, $user_id, $term_id) = $args;

		if ( 'delete_term' !== $cap ) {
			return $all_caps;
		}

		if ( $this->get_term_integration( $term_id ) ) {
			// Removing only one capability is enough to deny access.
			$all_caps[ $required_caps[0] ] = false;
		}

		return $all_caps;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function make_purpose_from_term( WP_Term $term ) {
		$purpose     = parent::make_purpose_from_term( $term );
		$integration = $this->get_term_integration( $term );

		if ( $integration ) {
			$purpose['cookies'] = array_unique(
				array_merge(
					$purpose['cookies'],
					$integration->get_cookie_names()
				)
			);
		}

		return $purpose;
	}
}
