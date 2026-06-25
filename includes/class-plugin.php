<?php
/**
 * Orejime plugin.
 *
 * @package Orejime
 */

namespace Orejime;

use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main entry point of the plugin.
 */
final class Plugin {

	use Hookable;

	const MENU_SLUG     = 'orejime';
	const SCRIPT_HANDLE = 'orejime-script';
	const STYLE_HANDLE  = 'orejime-style';

	/**
	 * Meta information about the JS library.
	 *
	 * @var array{'version': string, 'langs': string[]}
	 */
	private array $orejime_manifest;

	/**
	 * Integration registry.
	 *
	 * @var Integration_Registry
	 */
	private Integration_Registry $integrations;

	/**
	 * Taxonomy manager.
	 *
	 * @var Purpose_Taxonomy_Integrated
	 */
	private Purpose_Taxonomy_Integrated $taxonomy;

	/**
	 * Loads the plugin.
	 */
	public static function load() {
		static $instance = null;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Gets the purpose associated with the given term.
	 *
	 * @param WP_Term $term Term.
	 * @return array Purpose.
	 */
	public function get_purpose_from_term( WP_Term $term ) {
		return $this->taxonomy->make_purpose_from_term( $term );
	}

	/**
	 * Initializes the plugin.
	 */
	private function __construct() {
		$this->orejime_manifest = include_once plugin_dir_path( OREJIME_PLUGIN_FILE ) . 'public/orejime-manifest.php';
		$this->integrations     = new Integration_Registry();
		$this->taxonomy         = new Purpose_Taxonomy_Integrated( $this->integrations );
		$this->taxonomy->hook_up();

		/**
		 * Tells if the plugin should register its default
		 * integrations automatically on startup.
		 *
		 * @param bool $register Whether to register integrations.
		 */
		$register_defaults = apply_filters( 'orejime_register_default_integrations', true );

		if ( $register_defaults ) {
			add_action( 'init', $this->get_callback( 'register_integrations' ), 100 );
		}

		add_action( 'init', $this->get_callback( 'register_blocks' ), 100 );
		add_action( 'wp_enqueue_scripts', $this->get_callback( 'enqueue_scripts' ) );
		add_action( 'admin_menu', $this->get_callback( 'setup_menu' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( OREJIME_PLUGIN_FILE ),
			$this->get_callback( 'setup_action_links' )
		);
	}

	/**
	 * Registers custom blocks.
	 */
	private function register_blocks() {
		register_block_type_from_metadata(
			plugin_dir_path( OREJIME_PLUGIN_FILE ) . 'build/contextual-consent',
			array(
				'render_callback' => 'Orejime\Block\render_contextual_consent',
			)
		);
	}

	/**
	 * Registers built-in integrations.
	 */
	private function register_integrations() {
		$this->integrations->register(
			new \Orejime\Integration\Core_Embed_Block(
				'core-embed-block',
				'Embedded content'
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\GA_Google_Analytics(
				'ga-google-analytics',
				'GA Google Analytics',
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\Google_Site_Kit\Module\Analytics(
				'google-site-kit-analytics',
				'Google Site Kit Analytics',
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\Google_Site_Kit\Module\Tag_Manager(
				'google-site-kit-tag-manager',
				'Google Site Kit Tag Manager',
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\Jetpack\Module\Stats(
				'jetpack-stats',
				'Jetpack Stats',
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\Matomo(
				'matomo',
				'Matomo',
			),
		);

		$this->integrations->register(
			new \Orejime\Integration\Monster_Insights(
				'monster-insights',
				'Monster Insights',
			),
		);
	}

	/**
	 * Enqueues Orejime's scripts and config.
	 */
	private function enqueue_scripts() {
		$purposes = $this->taxonomy->get_purpose_tree();

		if ( empty( $purposes ) ) {
			return;
		}

		$lang   = $this->get_preferred_lang();
		$config = wp_json_encode(
			array(
				'privacyPolicyUrl' => $this->get_privacy_policy_url(),
				'purposes'         => $purposes,
			)
		);

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			plugins_url( "public/orejime-standard-$lang.js", OREJIME_PLUGIN_FILE ),
			array(),
			$this->orejime_manifest['version'],
			array(
				'in_footer' => false,
			)
		);

		wp_add_inline_script(
			self::SCRIPT_HANDLE,
			"window.orejimeConfig = $config;",
			'before'
		);

		wp_enqueue_style(
			self::STYLE_HANDLE,
			plugins_url( 'public/orejime-standard.css', OREJIME_PLUGIN_FILE ),
			array(),
			$this->orejime_manifest['version']
		);
	}

	/**
	 * Finds which language should be used depending on the
	 * current blog config.
	 *
	 * @return string Language code.
	 */
	private function get_preferred_lang() {
		$lang = substr( get_locale(), 0, 2 );
		return in_array( $lang, $this->orejime_manifest['langs'], true ) ? $lang : 'en';
	}

	/**
	 * Finds the permalink of the privacy policy page.
	 *
	 * @return string URL.
	 */
	private function get_privacy_policy_url() {
		return get_page_link( (int) get_option( 'wp_page_for_privacy_policy' ) );
	}

	/**
	 * Adds admin menu entries.
	 */
	private function setup_menu() {
		add_menu_page(
			'Orejime',
			'Orejime',
			// Using an unknown capability makes the page
			// a mere container for sub pages, while not being
			// directly accessible.
			// phpcs:ignore WordPress.WP.Capabilities.Unknown
			'orejime_unknown',
			self::MENU_SLUG,
			null,
			'dashicons-privacy'
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Purposes', 'orejime' ),
			__( 'Purposes', 'orejime' ),
			'manage_options',
			$this->taxonomy->get_admin_edit_page_path()
		);
	}

	/**
	 * Adds custom plugin actions.
	 *
	 * @param array $links Default links.
	 * @return array Links.
	 */
	private function setup_action_links( array $links ) {
		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( $this->taxonomy->get_admin_edit_page_path(), false ),
				__( 'Configure', 'orejime' )
			)
		);

		return $links;
	}
}
