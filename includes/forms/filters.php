<?php
// Backward compatibility start.
/**
 * @param $form_html
 * @param $form
 *
 * @return mixed
 */
function give_donation_form_actions( $form_html, $form ) {
	if( false === strpos( $form['id'], 'give-form-' ) ) {
		return $form_html;
	}

	ob_start();

	/**
	 * Fires while outputting donation form, before all other fields.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_checkout_form_top', $form['donation_form_object']->ID, $form['donation_form_arguments'] );

	/**
	 * Fires while outputing donation form, for payment gatways fields.
	 *
	 * @since 1.7
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_payment_mode_select', $form['donation_form_object']->ID, $form['donation_form_arguments'] );

	/**
	 * Fires while outputing donation form, after all other fields.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_checkout_form_bottom', $form['donation_form_object']->ID, $form['donation_form_arguments'] );

	$form_html = str_replace( '</form>', ob_get_clean() . '</form>', $form_html );

	return $form_html;
}
add_filter( 'give_form_api_render_form', 'give_donation_form_actions', 10, 2 );

// Backward compatibility end.


/**
 * Edit give-price-id hidden field in donation form.
 *
 * @since 1.9
 *
 * @param $form_args
 * @return array
 */
function give_form_edit_price_id( $form_args ) {
	if( ! empty( $form_args['fields'] ) ) {
		foreach ( $form_args['fields'] as $index => $field ) {
			if( 'hidden' === $field['type'] && 'give-price-id' === $field['id'] ) {
				// Set form id.
				$form_id = $form_args['donation_form_object']->ID;

				// Price ID hidden field for variable (multi-level) donation forms.
				if ( give_has_variable_prices( $form_id ) ) {
					// Get default selected price ID.
					$prices   = apply_filters( 'give_form_variable_prices', give_get_variable_prices( $form_id ), $form_id );
					$price_id = 0;

					//loop through prices.
					foreach ( $prices as $price ) {
						if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
							$price_id = $price['_give_id']['level_id'];
						};
					}

					// Form default price id.
					$form_args['fields'][$index]['value'] = $price_id;
				} else {
					unset( $form_args['fields'][$index] );
				}

				break;
			}
		}
	}
	return $form_args;
}
add_filter( 'give_form_args', 'give_form_edit_price_id' );