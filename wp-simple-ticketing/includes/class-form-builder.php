<?php
/**
 * Basic form builder class.
 *
 * @package WP_Simple_Ticketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides helpers for managing ticket form fields.
 */
class WST_Form_Builder {
	/**
	 * Option key used to store form configuration.
	 *
	 * @var string
	 */
	protected $option_key = 'wst_form_fields';

	/**
	 * Retrieve saved form configuration.
	 *
	 * @return array
	 */
	public function get_fields() {
		$fields = get_option( $this->option_key, array() );

		return is_array( $fields ) ? $fields : array();
	}

	/**
	 * Save form configuration to the database.
	 *
	 * @param array $fields Form field configuration.
	 * @return void
	 */
	public function save_fields( $fields ) {
		update_option( $this->option_key, (array) $fields );
	}
}
