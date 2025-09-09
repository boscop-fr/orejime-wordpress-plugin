<?php
/**
 * Orejime plugin.
 *
 * @package Orejime
 */

/**
 * The main entry point of the plugin.
 */
final class Orejime_Plugin {

	use Orejime_Hookable;

	const MENU_SLUG     = 'orejime';
	const CDN_URL       = 'https://cdn.jsdelivr.net/npm/orejime';
	const VERSION       = 'latest';
	const SCRIPT_HANDLE = 'orejime-script';
	const STYLE_HANDLE  = 'orejime-style';

	/**
	 * Integration registry.
	 *
	 * @var Orejime_Integration_Registry
	 */
	private Orejime_Integration_Registry $integrations;

	/**
	 * Taxonomy manager.
	 *
	 * @var Orejime_Purpose_Taxonomy_Integrated
	 */
	private Orejime_Purpose_Taxonomy_Integrated $taxonomy;

	/**
	 * Loads the plugin.
	 */
	public static function load() {
		static $instance = null;

		if ( ! $instance ) {
			$instance = new self();
		}
	}

	/**
	 * Initializes the plugin.
	 */
	private function __construct() {
		$this->integrations = new Orejime_Integration_Registry();
		$this->taxonomy     = new Orejime_Purpose_Taxonomy_Integrated( $this->integrations );
		$this->taxonomy->register();

		add_action( 'plugins_loaded', $this->get_callback( 'register_integrations' ) );
		add_action( 'wp_enqueue_scripts', $this->get_callback( 'enqueue_scripts' ) );
		add_action( 'admin_menu', $this->get_callback( 'setup_menu' ) );
		add_filter(
			'plugin_action_links_' . OREJIME_PLUGIN_FILE,
			$this->get_callback( 'setup_action_links' )
		);
	}

	/**
	 * Registers built-in integrations.
	 */
	private function register_integrations() {
		$this->integrations->register(
			new Orejime_Integration_Core_Embed_Block(
				'core-embed-block',
				'Embedded content'
			),
		);

		$this->integrations->register(
			new Orejime_Integration_Google_Site_Kit(
				'google-site-kit',
				'Google Site Kit',
			),
		);

		$this->integrations->register(
			new Orejime_Integration_Matomo(
				'matomo',
				'Matomo',
			),
		);

		$this->integrations->register(
			new Orejime_Integration_Monster_Insights(
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

		$lang   = substr( get_locale(), 0, 2 );
		$config = wp_json_encode(
			array(
				'privacyPolicyUrl' => $this->get_privacy_policy_url(),
				'purposes'         => $purposes,
			)
		);

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			$this->make_cdn_url( "/dist/orejime-standard-$lang.js" ),
			array(),
			self::VERSION,
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
			$this->make_cdn_url( '/dist/orejime-standard.css' ),
			array(),
			self::VERSION
		);
	}

	/**
	 * Finds the permalink of the privacy policy page.
	 *
	 * @return string
	 */
	private function get_privacy_policy_url() {
		return get_page_link( (int) get_option( 'wp_page_for_privacy_policy' ) );
	}

	/**
	 * Builds an URL pointing to the given file on Orejime's CDN.
	 *
	 * @param string $path Relative file path.
	 * @return string
	 */
	private function make_cdn_url( $path ) {
		return self::CDN_URL . '@' . self::VERSION . $path;
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
