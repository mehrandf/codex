<?php
/**
 * Plugin Name:       WP Simple Ticketing
 * Plugin URI:        https://example.com/wp-simple-ticketing
 * Description:       Provides a minimal support ticketing system with role management and custom forms.
 * Version:           0.1.0
 * Author:            Codex Team
 * Author URI:        https://example.com
 * Text Domain:       wp-simple-ticketing
 * Domain Path:       /languages
 *
 * @package WP_Simple_Ticketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WST_VERSION', '0.1.0' );
define( 'WST_PLUGIN_FILE', __FILE__ );
define( 'WST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load plugin files.
require_once WST_PLUGIN_DIR . 'includes/functions.php';

/**
 * Initialize the plugin components on plugins_loaded.
 *
 * @return void
 */
function wst_plugin_init() {
	wst_register_ticket_post_type();
	wst_register_shortcodes();
	wst_register_admin_menu();
}
add_action( 'plugins_loaded', 'wst_plugin_init' );
