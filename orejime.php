<?php
/**
 * Plugin Name: Orejime
 * Text Domain: orejime
 * Author: Boscop
 * Author URI: https://boscop.fr
 *
 * @package WordPress
 * @subpackage Orejime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/admin/class-orejime-plugin.php';
require_once __DIR__ . '/admin/class-orejime-purpose-taxonomy.php';
require_once __DIR__ . '/admin/class-orejime-purpose-taxonomy-integrated.php';
require_once __DIR__ . '/admin/media.php';
require_once __DIR__ . '/admin/scripts.php';
require_once __DIR__ . '/integrations/class-orejime-integration.php';
require_once __DIR__ . '/integrations/class-orejime-integration-core-embed-block.php';
require_once __DIR__ . '/integrations/class-orejime-integration-google-site-kit.php';
require_once __DIR__ . '/integrations/class-orejime-integration-matomo.php';
require_once __DIR__ . '/integrations/class-orejime-integration-monster-insights.php';
require_once __DIR__ . '/integrations/class-orejime-integration-registry.php';

new Orejime_Plugin();
