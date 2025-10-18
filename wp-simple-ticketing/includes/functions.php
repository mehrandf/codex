<?php
/**
 * Common helper functions for WP Simple Ticketing.
 *
 * @package WP_Simple_Ticketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WST_PLUGIN_DIR . 'includes/class-ticket-manager.php';
require_once WST_PLUGIN_DIR . 'includes/class-form-builder.php';
require_once WST_PLUGIN_DIR . 'includes/class-admin-menu.php';

/**
 * Get ticket manager instance.
 *
 * @return WST_Ticket_Manager
 */
function wst_get_ticket_manager() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new WST_Ticket_Manager();
	}

	return $instance;
}

/**
 * Get form builder instance.
 *
 * @return WST_Form_Builder
 */
function wst_get_form_builder() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new WST_Form_Builder();
	}

	return $instance;
}

/**
 * Get admin menu instance.
 *
 * @return WST_Admin_Menu
 */
function wst_get_admin_menu() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new WST_Admin_Menu();
	}

	return $instance;
}

/**
 * Register the ticket custom post type.
 *
 * @return void
 */
function wst_register_ticket_post_type() {
	$manager = wst_get_ticket_manager();

	add_action( 'init', array( $manager, 'register_post_type' ) );
}

/**
 * Register plugin shortcodes.
 *
 * @return void
 */
function wst_register_shortcodes() {
	$manager = wst_get_ticket_manager();

	add_shortcode( 'wst_ticket_form', array( $manager, 'render_ticket_form' ) );
	add_shortcode( 'wst_my_tickets', array( $manager, 'render_ticket_list' ) );
}

/**
 * Register plugin admin menu.
 *
 * @return void
 */
function wst_register_admin_menu() {
	$admin_menu = wst_get_admin_menu();

	add_action( 'admin_menu', array( $admin_menu, 'register_menu' ) );
}

/**
 * Load a template part from the plugin.
 *
 * @param string $template Template file name.
 * @param array  $vars     Optional variables to extract for the template.
 * @return void
 */
function wst_get_template_part( $template, $vars = array() ) {
	$filepath = trailingslashit( WST_PLUGIN_DIR ) . 'templates/' . $template;

	if ( ! file_exists( $filepath ) ) {
		return;
	}

	if ( ! empty( $vars ) ) {
		extract( $vars, EXTR_SKIP );
	}

	include $filepath;
}
