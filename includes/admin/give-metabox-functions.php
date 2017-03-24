<?php
/**
 * Give Meta Box Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Check if field callback exist or not.
 *
 * @since  1.8
 *
 * @param  $field
 *
 * @return bool|string
 */
function give_is_field_callback_exist( $field ) {
	return ( give_get_field_callback( $field ) ? true : false );
}

/**
 * Get field callback.
 *
 * @since  1.8
 *
 * @param  $field
 *
 * @return bool|string
 */
function give_get_field_callback( $field ) {
	$func_name_prefix = 'give';
	$func_name        = '';

	// Set callback function on basis of cmb2 field name.
	switch ( $field['type'] ) {
		case 'radio_inline':
			$func_name = "{$func_name_prefix}_radio";
			break;

		case 'text':
		case 'text-medium':
		case 'text_medium':
		case 'text-small' :
		case 'text_small' :
			$func_name = "{$func_name_prefix}_text_input";
			break;

		case 'textarea' :
			$func_name = "{$func_name_prefix}_textarea_input";
			break;

		case 'colorpicker' :
			$func_name = "{$func_name_prefix}_{$field['type']}";
			break;

		case 'hidden':
		case 'levels_id':
			$func_name = "{$func_name_prefix}_hidden_input";
			break;

		case 'group' :
			$func_name = "_{$func_name_prefix}_metabox_form_data_repeater_fields";
			break;

		case 'give_default_radio_inline':
			$func_name = "{$func_name_prefix}_radio";
			break;

		default:

			if (
				array_key_exists( 'callback', $field )
				&& ! empty( $field['callback'] )
			) {
				$func_name = $field['callback'];
			} else {
				$func_name = "{$func_name_prefix}_{$field['type']}";
			}
	}

	/**
	 * Filter the metabox setting render function
	 *
	 * @since 1.8
	 */
	$func_name = apply_filters( 'give_get_field_callback', $func_name, $field );

	// Exit if not any function exist.
	// Check if render callback exist or not.
	if ( empty( $func_name ) ) {
		return false;
	} elseif ( is_string( $func_name ) && ! function_exists( "$func_name" ) ) {
		return false;
	} elseif ( is_array( $func_name ) && ! method_exists( $func_name[0], "$func_name[1]" ) ) {
		return false;
	}

	return $func_name;
}

/**
 * This function adds backward compatibility to render cmb2 type field type.
 *
 * @since  1.8
 *
 * @param  array $field Field argument array.
 *
 * @return bool
 */
function give_render_field( $field ) {

	// Check if render callback exist or not.
	if ( ! ( $func_name = give_get_field_callback( $field ) ) ) {
		return false;
	}

	$field = give_backward_compatibility_setting_api_1_8( $field );

	// Call render function.
	if ( is_array( $func_name ) ) {
		$func_name[0]->$func_name[1]( $field );
	} else {
		$func_name( $field );
	}

	return true;
}

/**
 * Output a text input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */
function give_text_input( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );
	$data_type      = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price' :
			$field['value'] = ( ! empty( $field['value'] ) ? give_format_amount( $field['value'] ) : $field['value'] );

			$field['before_field'] = ! empty( $field['before_field'] ) ? $field['before_field'] : ( give_get_option( 'currency_position', 'before' ) == 'before' ? '<span class="give-money-symbol give-money-symbol-before">' . give_currency_symbol() . '</span>' : '' );
			$field['after_field']  = ! empty( $field['after_field'] ) ? $field['after_field'] : ( give_get_option( 'currency_position', 'before' ) == 'after' ? '<span class="give-money-symbol give-money-symbol-after">' . give_currency_symbol() . '</span>' : '' );
			break;

		case 'decimal' :
			$field['attributes']['class'] .= ' give_input_decimal';
			$field['value'] = ( ! empty( $field['value'] ) ? give_format_decimal( $field['value'] ) : $field['value'] );
			break;

		default :
			break;
	}

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a hidden input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */
function give_hidden_input( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a textarea input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */
function give_textarea_input( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a wysiwyg.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of WordPress editor field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 * @return void
 */
function give_wysiwyg( $field ) {
	global $thepostid, $post;

	$thepostid                = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value']           = give_get_field_value( $field, $thepostid );
	$field['unique_field_id'] = give_get_field_name( $field );
	$field['wrapper_type']    = 'div';

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	$field['wrapper_attributes']['data-wp-editor'] = base64_encode( json_encode( array(
			$field['value'],
			$field['unique_field_id'],
			//$field['editor_attributes'],
		) ) ) . '"';

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );

	// @todo: label must be linked to wordpress editor.
}

/**
 * Output a checkbox input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */
function give_checkbox( $field ) {
	global $thepostid, $post;

	$thepostid        = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value']   = give_get_field_value( $field, $thepostid );
	$field['cbvalue'] = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'on';

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a select input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */
function give_select( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a radio input box.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 * @param array $field
 *
 * @return void
 */
function give_radio( $field ) {
	global $thepostid, $post;

	$thepostid             = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value']        = give_get_field_value( $field, $thepostid );
	$field['wrapper_type'] = 'fieldset';

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a colorpicker.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 * @param array $field
 *
 * @return void
 */
function give_colorpicker( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );
	$field['type']  = 'text';

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}


/**
 * Output a media upload field.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 * @param array $field
 *
 * @return void
 */
function give_media( $field ) {
	global $thepostid, $post;

	// $field = give_backward_compatibility_setting_api_1_8( $field );

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Set description.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['after_field'] = ! empty( $field['after_field'] )
		? $field['after_field'] . give_get_field_description( $field )
		: give_get_field_description( $field );

	// Reset label for repeater field compatibility.
	$field['name'] = give_get_field_name( $field );

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Output a select field with payment options list.
 *
 * @since  1.8
 *
 * @param  array $field
 *
 * @return void
 */
function give_default_gateway( $field ) {
	global $thepostid, $post;

	// get all active payment gateways.
	$gateways         = give_get_enabled_payment_gateways( $thepostid );
	$field['options'] = array();

	// Set field option value.
	if ( ! empty( $gateways ) ) {
		foreach ( $gateways as $key => $option ) {
			$field['options'][ $key ] = $option['admin_label'];
		}
	}

	// Add a field to the Give Form admin single post view of this field
	if ( is_object( $post ) && 'give_forms' === $post->post_type ) {
		$field['options'] = array_merge( array( 'global' => esc_html__( 'Global Default', 'give' ) ), $field['options'] );
	}

	$field['type'] = 'select';

	// Render select field.
	give_select( $field );
}

/**
 * Output the documentation link.
 *
 * @since  1.8
 * @since  1.9 Render field with field api
 *
 * @param array $field Field arguments
 *                     Check includes/forms/api/class-give-field-api.php:28 for arguments.
 *
 * @return void
 */

function give_docs_link( $field ) {
	// $field = give_backward_compatibility_setting_api_1_8( $field );

	// Set default class.
	// Backward compatibility ( 1.8=<version>1.9).
	$field['wrapper_attributes']['class'] = ! empty( $field['wrapper_attributes']['class'] )
		? "{$field['wrapper_attributes']['class']} give-docs-link"
		: 'give-docs-link';

	// Render Field.
	echo Give_Fields_API::render_tag( $field );
}

/**
 * Get setting field value.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param  array $field
 * @param  int   $postid
 *
 * @return mixed
 */
function give_get_field_value( $field, $postid ) {
	if ( isset( $field['attributes']['value'] ) ) {
		return $field['attributes']['value'];
	}

	// Get value from db.
	$field_value = get_post_meta( $postid, $field['id'], true );

	/**
	 * Filter the field value before apply default value.
	 *
	 * @since 1.8
	 *
	 * @param mixed $field_value Field value.
	 */
	$field_value = apply_filters( "{$field['id']}_field_value", $field_value, $field, $postid );

	// Set default value if no any data saved to db.
	if ( ! $field_value && isset( $field['default'] ) ) {
		$field_value = $field['default'];
	}

	return $field_value;
}


/**
 * Get field description html.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function give_get_field_description( $field ) {
	$field_desc_html = '';
	if ( ! empty( $field['description'] ) ) {
		$field_desc_html = '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	return $field_desc_html;
}


/**
 * Get field custom attributes as string.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function give_get_custom_attributes( $field ) {
	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	return implode( ' ', $custom_attributes );
}

/**
 * Get repeater field value.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param array $field
 * @param array $field_group
 * @param array $fields
 *
 * @return string
 */
function give_get_repeater_field_value( $field, $field_group, $fields ) {
	$field_value = ( isset( $field_group[ $field['id'] ] ) ? $field_group[ $field['id'] ] : '' );

	/**
	 * Filter the specific repeater field value
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_value = apply_filters( "give_get_repeater_field_{$field['id']}_value", $field_value, $field, $field_group, $fields );

	/**
	 * Filter the repeater field value
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_value = apply_filters( 'give_get_repeater_field_value', $field_value, $field, $field_group, $fields );

	return $field_value;
}

/**
 * Get repeater field id.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param array    $field
 * @param array    $fields
 * @param int|bool $default
 *
 * @return string
 */
function give_get_repeater_field_id( $field, $fields, $default = false ) {
	$row_placeholder = false !== $default ? $default : '{{row-count-placeholder}}';

	// Get field id.
	$field_id = "{$fields['id']}[{$row_placeholder}][{$field['id']}]";

	/**
	 * Filter the specific repeater field id
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_id = apply_filters( "give_get_repeater_field_{$field['id']}_id", $field_id, $field, $fields, $default );

	/**
	 * Filter the repeater field id
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_id = apply_filters( 'give_get_repeater_field_id', $field_id, $field, $fields, $default );

	return $field_id;
}


/**
 * Get field name.
 *
 * @since  1.8
 *
 * @param  array $field
 *
 * @return string
 */
function give_get_field_name( $field ) {
	$field_name = esc_attr( empty( $field['repeat'] ) ? $field['id'] : $field['repeatable_field_id'] );

	/**
	 * Filter the field name.
	 *
	 * @since 1.8
	 *
	 * @param string $field_name
	 */
	$field_name = apply_filters( 'give_get_field_name', $field_name, $field );

	return $field_name;
}

/**
 * Output repeater field or multi donation type form on donation from edit screen.
 * Note: internal use only.
 *
 * @since  1.8
 *
 * @param  array $fields
 *
 * @return void
 */
function _give_metabox_form_data_repeater_fields( $fields ) {
	global $thepostid, $post;
	$fields            = give_backward_compatibility_setting_api_1_8( $fields );
	$fields['value']   = get_post_meta( $thepostid, $fields['id'], true );
	$fields['wrapper'] = false;

	// error_log( print_r( $fields, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );

	echo Give_Fields_API::render_tag( $fields );
}


/**
 * Get current setting tab.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_tab() {
	// Get current setting page.
	$current_setting_page = give_get_current_setting_page();

	/**
	 * Filter the default tab for current setting page.
	 *
	 * @since 1.8
	 *
	 * @param string
	 */
	$default_current_tab = apply_filters( "give_default_setting_tab_{$current_setting_page}", 'general' );

	// Get current tab.
	$current_tab = empty( $_GET['tab'] ) ? $default_current_tab : urldecode( $_GET['tab'] );

	// Output.
	return $current_tab;
}


/**
 * Get current setting section.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_section() {
	// Get current tab.
	$current_tab = give_get_current_setting_tab();

	/**
	 * Filter the default section for current setting page tab.
	 *
	 * @since 1.8
	 *
	 * @param string
	 */
	$default_current_section = apply_filters( "give_default_setting_tab_section_{$current_tab}", '' );

	// Get current section.
	$current_section = empty( $_REQUEST['section'] ) ? $default_current_section : urldecode( $_REQUEST['section'] );

	// Output.
	return $current_section;
}

/**
 * Get current setting page.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_page() {
	// Get current page.
	$setting_page = ! empty( $_GET['page'] ) ? urldecode( $_GET['page'] ) : '';

	// Output.
	return $setting_page;
}

/**
 * Set value for Form content --> Display content field setting.
 *
 * Backward compatibility:  set value by _give_content_option form meta field value if _give_display_content is not set
 * yet.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_display_content_field_value( $field_value, $field, $postid ) {
	$show_content = get_post_meta( $postid, '_give_content_option', true );

	if (
		! get_post_meta( $postid, '_give_display_content', true )
		&& $show_content
		&& ( 'none' !== $show_content )
	) {
		$field_value = 'enabled';
	}

	return $field_value;
}

add_filter( '_give_display_content_field_value', '_give_display_content_field_value', 10, 3 );


/**
 * Set value for Form content --> Content placement field setting.
 *
 * Backward compatibility:  set value by _give_content_option form meta field value if _give_content_placement is not
 * set yet.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_content_placement_field_value( $field_value, $field, $postid ) {
	$show_content = get_post_meta( $postid, '_give_content_option', true );

	if (
		! get_post_meta( $postid, '_give_content_placement', true )
		&& ( 'none' !== $show_content )
	) {
		$field_value = $show_content;
	}

	return $field_value;
}

add_filter( '_give_content_placement_field_value', '_give_content_placement_field_value', 10, 3 );


/**
 * Set value for Terms and Conditions --> Terms and Conditions field setting.
 *
 * Backward compatibility:  set value by _give_terms_option form meta field value if it's value is none.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_terms_option_field_value( $field_value, $field, $postid ) {
	$term_option = get_post_meta( $postid, '_give_terms_option', true );

	if ( in_array( $term_option, array( 'none', 'yes' ) ) ) {
		$field_value = ( 'yes' === $term_option ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_terms_option_field_value', '_give_terms_option_field_value', 10, 3 );


/**
 * Set value for Form Display --> Offline Donation --> Billing Fields.
 *
 * Backward compatibility:  set value by _give_offline_donation_enable_billing_fields_single form meta field value if
 * it's value is on.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_offline_donation_enable_billing_fields_single_field_value( $field_value, $field, $postid ) {
	$offline_donation = get_post_meta( $postid, '_give_offline_donation_enable_billing_fields_single', true );

	if ( 'on' === $offline_donation ) {
		$field_value = 'enabled';
	}

	return $field_value;
}

add_filter( '_give_offline_donation_enable_billing_fields_single_field_value', '_give_offline_donation_enable_billing_fields_single_field_value', 10, 3 );


/**
 * Set value for Donation Options --> Custom Amount.
 *
 * Backward compatibility:  set value by _give_custom_amount form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_custom_amount_field_value( $field_value, $field, $postid ) {
	$custom_amount = get_post_meta( $postid, '_give_custom_amount', true );

	if ( in_array( $custom_amount, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $custom_amount ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_custom_amount_field_value', '_give_custom_amount_field_value', 10, 3 );


/**
 * Set value for Donation Goal --> Donation Goal.
 *
 * Backward compatibility:  set value by _give_goal_option form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_goal_option_field_value( $field_value, $field, $postid ) {
	$goal_option = get_post_meta( $postid, '_give_goal_option', true );

	if ( in_array( $goal_option, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $goal_option ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_goal_option_field_value', '_give_goal_option_field_value', 10, 3 );

/**
 * Set value for Donation Goal --> close Form.
 *
 * Backward compatibility:  set value by _give_close_form_when_goal_achieved form meta field value if it's value is yes
 * or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_close_form_when_goal_achieved_value( $field_value, $field, $postid ) {
	$close_form = get_post_meta( $postid, '_give_close_form_when_goal_achieved', true );

	if ( in_array( $close_form, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $close_form ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_close_form_when_goal_achieved_field_value', '_give_close_form_when_goal_achieved_value', 10, 3 );


/**
 * Set value for Form display --> Guest Donation.
 *
 * Backward compatibility:  set value by _give_logged_in_only form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_logged_in_only_value( $field_value, $field, $postid ) {
	$guest_donation = get_post_meta( $postid, '_give_logged_in_only', true );

	if ( in_array( $guest_donation, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $guest_donation ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_logged_in_only_field_value', '_give_logged_in_only_value', 10, 3 );

/**
 * Set value for Offline Donations --> Offline Donations.
 *
 * Backward compatibility:  set value by _give_customize_offline_donations form meta field value if it's value is yes
 * or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_customize_offline_donations_value( $field_value, $field, $postid ) {
	$customize_offline_text = get_post_meta( $postid, '_give_customize_offline_donations', true );

	if ( in_array( $customize_offline_text, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $customize_offline_text ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_customize_offline_donations_field_value', '_give_customize_offline_donations_value', 10, 3 );


/**
 * Set repeater field id for multi donation form.
 *
 * @since 1.8
 *
 * @param int   $field_id
 * @param array $field
 * @param array $fields
 * @param bool  $default
 *
 * @return mixed
 */
function _give_set_multi_level_repeater_field_id( $field_id, $field, $fields, $default ) {
	$row_placeholder = false !== $default ? $default : '{{row-count-placeholder}}';
	$field_id        = "{$fields['id']}[{$row_placeholder}][{$field['id']}][level_id]";

	return $field_id;
}

add_filter( 'give_get_repeater_field__give_id_id', '_give_set_multi_level_repeater_field_id', 10, 4 );

/**
 * Set repeater field value for multi donation form.
 *
 * @since 1.8
 *
 * @param string $field_value
 * @param array  $field
 * @param array  $field_group
 * @param array  $fields
 *
 * @return mixed
 */
function _give_set_multi_level_repeater_field_value( $field_value, $field, $field_group, $fields ) {
	$field_value = $field_group[ $field['id'] ]['level_id'];

	return $field_value;
}

add_filter( 'give_get_repeater_field__give_id_value', '_give_set_multi_level_repeater_field_value', 10, 4 );

/**
 * Set default value for _give_id field.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function _give_set_field_give_id_default_value( $field ) {
	return 0;
}

add_filter( 'give_default_field_group_field__give_id_value', '_give_set_field_give_id_default_value' );

/**
 * Set default value for _give_default field.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function _give_set_field_give_default_default_value( $field ) {
	return 'default';
}

add_filter( 'give_default_field_group_field__give_default_value', '_give_set_field_give_default_default_value' );
