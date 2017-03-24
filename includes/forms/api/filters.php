<?php
/**
 * Render `{{form_attributes}}` tag.
 *
 * @since 1.9
 *
 * @param $form_html
 * @param $form
 *
 * @return string
 */
function give_render_form_attributes_tag( $form_html, $form ) {
	$form_attributes_val = array();

	if ( ! empty( $form['form_attributes'] ) ) {
		foreach ( $form['form_attributes'] as $attribute_name => $attribute_val ) {
			$form_attributes_val[] = "{$attribute_name}=\"{$attribute_val}\"";
		}
	}

	$form_html = str_replace( '{{form_attributes}}', implode( ' ', $form_attributes_val ), $form_html );

	return $form_html;
}

add_filter( 'give_form_api_render_form_tags', 'give_render_form_attributes_tag', 10, 2 );

/**
 * Skip `{{continue_button}}` tag fro render process.
 *
 * @since 1.9
 *
 * @param array $form_tags
 * @param array $form
 *
 * @return string
 */
function give_do_not_process_continue_button_tag( $form_tags, $form ) {
	if ( in_array( $form['display_style'], array( 'reveal' ) ) ) {
		$form_tags[] = '{{continue_button}}';
	}

	return $form_tags;
}

add_filter( 'give_form_api_manually_render_form_tags', 'give_do_not_process_continue_button_tag', 10, 2 );

/**
 * Render `{{continue_button}}` tag.
 *
 * @since 1.9
 *
 * @param $form_html
 * @param $form
 *
 * @return string
 */
function give_render_form_continue_button_tag( $form_html, $form ) {
	if ( empty( $form['continue_button_html'] ) ) {
		$class                        = ( 'modal' === $form['display_style']
			? 'give-btn-modal'
			: ( 'button' === $form['display_style'] ? 'give-btn-button' : 'give-btn-reveal' )
		);
		$form['continue_button_html'] = '<button class="' . $class . '">' . $form['continue_button_title'] . '</button>';
	}

	$form_html = str_replace( '{{continue_button}}', $form['continue_button_html'], $form_html );

	return $form_html;
}

add_filter( 'give_form_api_render_form_tags', 'give_render_form_continue_button_tag', 10, 2 );


/**
 * Set modal related classes in form.
 *
 * @since 1.9
 *
 * @param array $form
 *
 * @return array
 */
function give_set_form_display_style_class( $form ) {
	if ( in_array( $form['display_style'], array( 'modal', 'button' ) ) ) {
		$class                       = 'give-form-modal';
		$class                       = ( 'button' === $form['display_style'] ? "{$class} mfp-hide" : $class );
		$form['form_attributes']['class'] = isset( $form['form_attributes']['class'] )
			? trim( $form['form_attributes']['class'] ) . " {$class}"
			: $class;
	}

	return $form;
}

add_filter( 'give_form_api_post_set_default_values', 'give_set_form_display_style_class' );

/**
 * Set modal related classes in field.
 *
 * @since 1.9
 *
 * @param array $field
 * @param array $form
 *
 * @return array
 */
function give_set_field_display_style_class( $field, $form ) {
	if ( is_null( $form ) ) {
		return $field;
	}

	if ( 'modal' === $form['display_style'] ) {
		$class = '';

		if ( ! empty( $field['show_without_modal'] ) ) {
			$class = 'give-show-without-modal';
		}

		if ( empty( $field['show_within_modal'] ) ) {
			$class .= ' give-hide-within-modal';
		}

		$field['wrapper_attributes']['class'] = isset( $field['wrapper_attributes']['class'] )
			? trim( $field['wrapper_attributes']['class'] ) . " {$class}"
			: $class;
	}

	return $field;
}

add_filter( 'give_field_api_post_set_default_values', 'give_set_field_display_style_class', 10, 2 );


/**
 * Set step buttons for stepper form.
 *
 * @since 1.9
 *
 * @param array $field
 * @param array $form
 *
 * @return array
 */
function give_set_step_buttons_for_stepper_from( $field, $form ) {
	// Bailout
	if ( is_null( $form ) ||  empty( $form['display_style'] ) | 'stepper' !== $form['display_style'] ) {
		return $field;
	}

	if ( ! isset( $field['fields']['prev'] ) ) {
		$field['fields']['prev'] = array(
			'type'         => 'button',
			'value'        => __( 'Previous' ),
			'wrapper'      => false,
			'before_field' => '<p class="give-clearfix">',
		);
	}

	if ( ! isset( $field['fields']['next'] ) ) {
		$field['fields']['next'] = array(
			'type'        => 'button',
			'value'       => __( 'Next' ),
			'wrapper'     => false,
			'after_field' => '</p>',
		);
	}

	return $field;
}

add_filter( 'give_field_api_pre_set_default_values', 'give_set_step_buttons_for_stepper_from', 10, 2 );


/**
 * Set field value.
 *
 * @since 1.9
 *
 * @param $field
 *
 * @return mixed
 */
function give_field_api_set_field_value( $field ) {
	switch ( $field['type'] ) {
		case 'text':
		case 'password':
		case 'number':
		case 'email':
		case 'hidden':
		case 'file':
			// Set default value.
			$field['field_attributes']['value'] = $field['default'];

			if ( ! empty( $field['value'] ) ) {
				$field['field_attributes']['value'] = $field['value'];
			} elseif ( isset( $_REQUEST[ $field['id'] ] ) ) {
				$field['field_attributes']['value'] = $_REQUEST[ $field['id'] ];
			}
			break;

		case 'checkbox':
			$field['field_attributes']['value'] = ! empty( $field['cbvalue'] ) ? $field['cbvalue'] : 'on' ;
			if (
				( ! empty( $field['value'] ) && $field['cbvalue'] === $field['value'] )
				|| isset( $_REQUEST[ $field['id'] ] )
			) {
				$field['field_attributes']['checked'] = 'checked';
			}
			break;

		case 'textarea':
		case 'radio':
		case 'select':
		case 'multi_select':
		case 'multi_checkbox':
			if ( empty( $field['value'] ) && isset( $_REQUEST[ $field['id'] ] ) ) {
				$field['value'] = give_clean( $_REQUEST[ $field['id'] ] );
			}
			break;
	}

	return $field;
}

add_filter( 'give_field_api_set_values', 'give_field_api_set_field_value' );


/**
 * Render docs link field.
 *
 * @since 1.9
 *
 * @param string $field_html
 * @param array  $field
 *
 * @return string
 */
function give_render_docs_link_field( $field_html, $field ) {
	// Set default values.
	$field['url'] = ! empty( $field['url'] ) ? $field['url'] : 'https://givewp.com/documentation';
	$label        = ! empty( $field['label'] ) ? $field['label'] : __( 'Documentation', 'give' );

	// We do not want to print label for this field, that why we unset this before getting wrapper.
	unset( $field['label'] );

	$field_wrapper = Give_Fields_API::get_instance()->render_field_wrapper( $field );

	ob_start();

	?>
	<a href="<?php echo  esc_url( $field['url'] ); ?>" target="_blank">
		<?php echo sprintf( esc_html__( 'Need Help? See docs on "%s"' ), $label ); ?>
		<span class="dashicons dashicons-editor-help"></span>
	</a>
	<?php

	return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
}

add_filter( 'give_field_api_render_docs_link_field', 'give_render_docs_link_field', 10, 2 );

/**
 * Render wysiwyg field.
 *
 * @since 1.9
 *
 * @param string $field_html
 * @param array  $field
 *
 * @return string
 */
function give_render_wysiwyg_field( $field_html, $field ) {
	$field['editor_attributes']        = array(
		'textarea_name' => isset( $field['repeatable_field_id'] )
			? $field['repeatable_field_id']
			: $field['id'],
		'textarea_rows' => '10',
		'editor_class'  => $field['field_attributes']['class'],
	);

	// Group field params.
	$field['unique_field_id'] = $field['id'];
	if( ! empty( $field['repeatable_field_id'] ) ) {
		$field['unique_field_id'] = '_give_repeater_' . uniqid() . '_wysiwyg';
		$field['wrapper_attributes']['data-wp-editor'] = base64_encode( json_encode( array(
				$field['value'],
				$field['unique_field_id'],
				$field['editor_attributes'],
			) ) );
	}

	// Do not wrap div tag with p tag.
	$field['wrapper_type'] = 'p' === $field['wrapper_type']
		? 'div'
		: $field['wrapper_type'];

	$field_wrapper = Give_Fields_API::get_instance()->render_field_wrapper( $field );

	ob_start();

	wp_editor(
		$field['value'],
		$field['unique_field_id'],
		$field['editor_attributes']
	);

	return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
}

add_filter( 'give_field_api_render_wysiwyg_field', 'give_render_wysiwyg_field', 10, 2 );

/**
 * Render file field.
 *
 * @since 1.9
 *
 * @param string $field_html
 * @param array  $field
 *
 * @return string
 */
function give_render_file_field( $field_html, $field ) {
	// Allow developer to save attachment ID or attachment url as metadata.
	$field['fvalue'] = isset( $field['fvalue'] ) ? $field['fvalue'] : 'url';
	$field['type']   = 'text';

	$field['after_field'] = '<input class="give-media-upload button" type="button" value="' . __( 'Add or Upload File', 'give' ) . '">' . $field['after_field'];
	
	return Give_Fields_API::get_instance()->render_text_field( $field );
}

add_filter( 'give_field_api_render_file_field', 'give_render_file_field', 10, 2 );