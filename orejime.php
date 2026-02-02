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

namespace Orejime;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'OREJIME_PLUGIN_FILE' ) ) {
	define( 'OREJIME_PLUGIN_FILE', plugin_basename( __FILE__ ) );
}

require_once __DIR__ . '/includes/trait-hookable.php';
require_once __DIR__ . '/includes/class-plugin.php';
require_once __DIR__ . '/includes/class-purpose-taxonomy.php';
require_once __DIR__ . '/includes/class-purpose-taxonomy-integrated.php';
require_once __DIR__ . '/includes/media.php';
require_once __DIR__ . '/includes/scripts.php';
require_once __DIR__ . '/includes/class-integration.php';
require_once __DIR__ . '/includes/class-integration-registry.php';
require_once __DIR__ . '/includes/integrations/class-core-embed-block.php';
require_once __DIR__ . '/includes/integrations/class-ga-google-analytics.php';
require_once __DIR__ . '/includes/integrations/google-site-kit/class-module.php';
require_once __DIR__ . '/includes/integrations/google-site-kit/modules/class-analytics.php';
require_once __DIR__ . '/includes/integrations/google-site-kit/modules/class-tag-manager.php';
require_once __DIR__ . '/includes/integrations/jetpack/class-module.php';
require_once __DIR__ . '/includes/integrations/jetpack/modules/class-stats.php';
require_once __DIR__ . '/includes/integrations/class-matomo.php';
require_once __DIR__ . '/includes/integrations/class-monster-insights.php';

Plugin::load();
