<?php
/**
 * Plugin Name: Orejime
 * Description: Lighweight and accessible consent manager.
 * Author: Boscop
 * Author URI: https://boscop.fr
 * Text Domain: orejime
 *
 * @package Orejime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'OREJIME_PLUGIN_FILE' ) ) {
	define( 'OREJIME_PLUGIN_FILE', plugin_basename( __FILE__ ) );
}

require_once __DIR__ . '/includes/trait-orejime-hookable.php';
require_once __DIR__ . '/admin/class-orejime-plugin.php';
require_once __DIR__ . '/admin/class-orejime-purpose-taxonomy.php';
require_once __DIR__ . '/admin/class-orejime-purpose-taxonomy-integrated.php';
require_once __DIR__ . '/admin/media.php';
require_once __DIR__ . '/admin/scripts.php';
require_once __DIR__ . '/integrations/class-orejime-integration.php';
require_once __DIR__ . '/integrations/class-orejime-integration-core-embed-block.php';
require_once __DIR__ . '/integrations/class-orejime-integration-google-site-kit-module.php';
require_once __DIR__ . '/integrations/class-orejime-integration-google-site-kit-module-analytics.php';
require_once __DIR__ . '/integrations/class-orejime-integration-matomo.php';
require_once __DIR__ . '/integrations/class-orejime-integration-monster-insights.php';
require_once __DIR__ . '/integrations/class-orejime-integration-registry.php';

Orejime_Plugin::load();
