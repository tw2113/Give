<?php

/**
 * Fields API
 *
 * @package     Give
 * @subpackage  Classes/Give_Fields_API
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */
class Give_Fields_API {

	/**
	 * The defaults for all elements
	 *
	 * @since  1.9
	 * @access static
	 */
	static $field_defaults = array(
		'name'       => '',
		'desc'       => '',
		'id'         => '',
		'type'       => '',
		'default'    => '',
		'data_type'  => '',
		'options'    => array(),
		'attributes' => array(),
	);

	/**
	 * Initialize this module
	 *
	 * @since  1.9
	 * @access static
	 */
	static function init() {
	}


	/**
	 * Return HTML with tag $tagname and keyed attrs $attrs.
	 *
	 * @since  1.9
	 * @access static
	 */
	static function make_tag() {
	}

	/**
	 * Get elements from a form.
	 *
	 * @since  1.9
	 * @access static
	 *
	 * @param array $form
	 */
	static function get_elements( $form ) {
	}

	/**
	 * Is the element a button?
	 *
	 * @since  1.9
	 * @access static
	 *
	 * @param array $element
	 *
	 * @return bool
	 */
	static function is_button( $element ) {
		return preg_match( '/^button|submit$/', $element['#type'] );
	}

	/**
	 * Render forms.
	 *
	 * @since  1.9
	 * @access static
	 */
	static function render_form() {
	}

	/**
	 * Render an element
	 * @since  1.9
	 * @access static
	 */
	static function render_element() {
	}

	/**
	 * Process a form, filling in $values with what's been posted
	 *
	 * @since  1.9
	 * @access static
	 */
	static function process_form() {
	}

	/**
	 * Recursively process a meta form element, filling in $values accordingly
	 *
	 * @since  1.9
	 * @access static
	 */
	static function process_element() {
	}
}