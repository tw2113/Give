<?php
/**
 * Form API
 *
 * @package     Give
 * @subpackage  Classes/Give_Form_API
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */
class Give_Form_API {

	/**
	 * The defaults for all elements
	 *
	 * @since 1.9
	 * @access static
	 */
	static $field_defaults = array();

	/**
	 * Initialize this module
	 *
	 * @since  1.9
	 * @access static
	 */
	static function init() {}


	/**
	 * Return HTML with tag $tagname and keyed attrs $attrs.
	 *
	 * @since 1.9
	 * @access static
	 *
	 * @param string $tagname
	 * @param array  $attrs
	 * @param string $content
	 */
	static function make_tag( $tagname, $attrs, $content = null ) {}

	/**
	 * Get elements from a form.
	 *
	 * @since 1.9
	 * @access static
	 * @param array $form
	 */
	static function get_elements( $form ) {}

	/**
	 * Is the element a button?
	 *
	 * @since 1.9
	 * @access static
	 *
	 * @return bool
	 */
	static function is_button( $element ) {
		return preg_match( '/^button|submit$/', $element['#type'] );
	}

	/**
	 * Render forms.
	 *
	 * @since 1.9
	 * @access static
	 *
	 * @param array  $form
	 * @param string &$values
	 */
	static function render_form( $form, &$values ) {}

	/**
	 * Render an element
	 * @since 1.9
	 * @access static
	 *
	 * @param array $element
	 * @param array &$values
	 * @param array $form
	 */
	static function render_element( $element, &$values, $form = null ) {}

	/**
	 * Process a form, filling in $values with what's been posted
	 *
	 * @since 1.9
	 * @access static
	 *
	 * @param array $form
	 * @param array &$values
	 * @param array &$input
	 */
	static function process_form( $form, &$values, &$input = null ) {}

	/**
	 * Recursively process a meta form element, filling in $values accordingly
	 *
	 * @since 1.9
	 * @access static
	 *
	 * @param array $element
	 * @param array &$values
	 * @param array &$input
	 */
	static function process_element( $element, &$values, &$input ) {}
}

// Initialize field API.
add_action( 'init', array( 'Give_Form_API', 'init' ) );
