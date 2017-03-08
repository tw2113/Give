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
