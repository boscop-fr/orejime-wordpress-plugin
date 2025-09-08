<?php
/**
 * Orejime plugin.
 *
 * @package Orejime
 */

/**
 * The main entry point of the plugin.
 */
class Orejime_Plugin {

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
	 * Initializes the plugin.
	 */
	public function __construct() {
		$this->integrations = new Orejime_Integration_Registry();
		$this->taxonomy     = new Orejime_Purpose_Taxonomy_Integrated( $this->integrations );
		$this->taxonomy->register();

		add_action( 'plugins_loaded', array( $this, 'register_integrations' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Registers built-in integrations.
	 */
	public function register_integrations() {
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
	public function enqueue_scripts() {
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
}
