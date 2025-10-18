<?php
/**
 * Admin menu handler.
 *
 * @package WP_Simple_Ticketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers admin pages for the plugin.
 */
class WST_Admin_Menu {
	/**
	 * Register admin menu pages.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Tickets', 'wp-simple-ticketing' ),
			__( 'Tickets', 'wp-simple-ticketing' ),
			'manage_options',
			'wst_tickets',
			array( $this, 'render_tickets_page' ),
			'dashicons-sos',
			26
		);

		add_submenu_page(
			'wst_tickets',
			__( 'All Tickets', 'wp-simple-ticketing' ),
			__( 'All Tickets', 'wp-simple-ticketing' ),
			'manage_options',
			'wst_tickets',
			array( $this, 'render_tickets_page' )
		);

		add_submenu_page(
			'wst_tickets',
			__( 'Form Builder', 'wp-simple-ticketing' ),
			__( 'Form Builder', 'wp-simple-ticketing' ),
			'manage_options',
			'wst_form_builder',
			array( $this, 'render_form_builder_page' )
		);

		add_submenu_page(
			'wst_tickets',
			__( 'Settings', 'wp-simple-ticketing' ),
			__( 'Settings', 'wp-simple-ticketing' ),
			'manage_options',
			'wst_settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render All Tickets admin page.
	 *
	 * @return void
	 */
	public function render_tickets_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'All Tickets', 'wp-simple-ticketing' ) . '</h1></div>';
	}

	/**
	 * Render Form Builder admin page placeholder.
	 *
	 * @return void
	 */
	public function render_form_builder_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Form Builder', 'wp-simple-ticketing' ) . '</h1></div>';
	}

	/**
	 * Render Settings admin page placeholder.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Settings', 'wp-simple-ticketing' ) . '</h1></div>';
	}
}
