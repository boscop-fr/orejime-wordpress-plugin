<?php
/**
 * Purpose taxonomy with integrations.
 *
 * @package Orejime
 */

namespace Orejime;

use Exception;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	public function hook_up() {
		parent::hook_up();

		add_action( 'init', $this->get_callback( 'setup_integrations' ), PHP_INT_MAX );

		add_filter( 'manage_edit-' . self::NAME . '_columns', $this->get_callback( 'add_integration_table_column' ) );
		add_filter( 'manage_' . self::NAME . '_custom_column', $this->get_callback( 'fill_custom_table_column' ), 10, 3 );
		add_filter( 'user_has_cap', $this->get_callback( 'user_capabilities' ), 10, 4 );
		add_filter( 'list_terms_exclusions', $this->get_callback( 'list_terms_exclusions' ), 10, 3 );
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
				$this->register_integration_term( $integration );
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
				'slug' => $this->integration_slug( $integration->id ),
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
			$this->integration_slug( $integration->id ),
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

		return $this->is_integration_slug( $term->slug )
			? $this->integrations->get( $this->integration_id( $term->slug ) )
			: null;
	}

	/**
	 * Generates a unique slug from the given integration id.
	 * This way, we can store the relationship between terms
	 * and integrations in a direct way without using a term
	 * meta, which would complexify and slow down queries.
	 *
	 * @param string $integration_id Integration id.
	 * @return string Slug.
	 */
	private function integration_slug( $integration_id ) {
		return self::INTEGRATION_PREFIX . $integration_id;
	}

	/**
	 * Tells if the given slug references an integration.
	 *
	 * @param string $slug Slug.
	 * @return boolean
	 */
	private function is_integration_slug( $slug ) {
		return strpos( $slug, self::INTEGRATION_PREFIX ) === 0;
	}

	/**
	 * Parses an integration id from the given slug.
	 *
	 * @param string $slug Slug.
	 * @return string Integration id.
	 */
	private function integration_id( $slug ) {
		return str_replace( self::INTEGRATION_PREFIX, '', $slug );
	}

	/**
	 * Excludes purpose terms associated with inactive
	 * integrations.
	 *
	 * @param string $exclusions `NOT IN` clause of the query.
	 * @param array  $args Arguments.
	 * @param array  $taxonomies Taxonomies.
	 * @return string Clause.
	 */
	private function list_terms_exclusions( $exclusions, $args, $taxonomies ) {
		if ( ! in_array( self::NAME, $taxonomies, true ) ) {
			return $exclusions;
		}

		if ( ! empty( $args['slug'] ) ) {
			return $exclusions;
		}

		$active_slugs = array_map(
			fn ( $i ) => $this->integration_slug( $i->id ),
			$this->integrations->get_active()
		);

		$prefix = self::INTEGRATION_PREFIX;
		$query  = "t.slug NOT LIKE '{$prefix}%'";

		if ( ! empty( $active_slugs ) ) {
			$sanitized = array_map( 'sanitize_title', $active_slugs );
			$query     = "($query OR t.slug IN ('" . implode( "', '", $sanitized ) . "'))";
		}

		return "$exclusions AND $query";
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
			$purpose['id']      = $integration->id;
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
