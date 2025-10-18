<?php
/**
 * Ticket manager.
 *
 * @package WP_Simple_Ticketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles ticket registration and rendering.
 */
class WST_Ticket_Manager {
	/**
	 * Register custom post type for tickets.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => __( 'Tickets', 'wp-simple-ticketing' ),
			'singular_name'         => __( 'Ticket', 'wp-simple-ticketing' ),
			'add_new'               => __( 'Add New', 'wp-simple-ticketing' ),
			'add_new_item'          => __( 'Add New Ticket', 'wp-simple-ticketing' ),
			'edit_item'             => __( 'Edit Ticket', 'wp-simple-ticketing' ),
			'new_item'              => __( 'New Ticket', 'wp-simple-ticketing' ),
			'all_items'             => __( 'All Tickets', 'wp-simple-ticketing' ),
			'view_item'             => __( 'View Ticket', 'wp-simple-ticketing' ),
			'search_items'          => __( 'Search Tickets', 'wp-simple-ticketing' ),
			'not_found'             => __( 'No tickets found', 'wp-simple-ticketing' ),
			'not_found_in_trash'    => __( 'No tickets found in Trash', 'wp-simple-ticketing' ),
			'menu_name'             => __( 'Tickets', 'wp-simple-ticketing' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'capability_type'    => 'post',
			'supports'           => array( 'title', 'editor', 'author' ),
			'has_archive'        => false,
			'rewrite'            => false,
		);

		register_post_type( 'wst_ticket', $args );
	}

	/**
	 * Render the ticket submission form.
	 *
	 * @return string
	 */
	public function render_ticket_form() {
		ob_start();

		wst_get_template_part( 'ticket-form.php' );

		return ob_get_clean();
	}

	/**
	 * Render the current user's ticket list.
	 *
	 * @return string
	 */
	public function render_ticket_list() {
		ob_start();

		wst_get_template_part( 'ticket-list.php' );

		return ob_get_clean();
	}
}
