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

	if ( ! empty( $form['attributes'] ) ) {
		foreach ( $form['attributes'] as $attribute_name => $attribute_val ) {
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
	if( in_array( $form['display_style'], array( 'reveal')  ) ) {
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
	if( empty( $form['continue_button_html'] ) ) {
		$class = ( 'modal' === $form['display_style']
			? 'give-btn-modal'
			: ( 'button' === $form['display_style'] ? 'give-btn-button' :'give-btn-reveal' )
		);
		$form['continue_button_html'] = '<button class="' . $class . '">' . $form['continue_button_title'] . '</button>';
	}

	$form_html = str_replace( '{{continue_button}}', $form['continue_button_html'], $form_html );

	return $form_html;
}

add_filter( 'give_form_api_render_form_tags', 'give_render_form_continue_button_tag', 10, 2 );


/**
 * Set modal related classes.
 *
 * @since 1.9
 *
 * @param array $form
 *
 * @return array
 */
function give_set_display_style_class( $form ) {
	if ( in_array( $form['display_style'], array( 'modal', 'button' ) ) ) {
		$form['attributes']['class'] = isset( $form['attributes']['class'] )
			? trim( $form['attributes']['class'] ) . ' give-form-modal mfp-hide'
			: 'give-form-modal mfp-hide';
	}

	return $form;
}

add_filter( 'give_form_api_set_default_values', 'give_set_display_style_class' );
