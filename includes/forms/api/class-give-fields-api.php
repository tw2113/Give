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
	 * Instance.
	 *
	 * @since  1.9
	 * @access private
	 * @var Give_Fields_API
	 */
	static private $instance;

	/**
	 * The defaults for all elements
	 *
	 * @since  1.9
	 * @access static
	 */
	static $field_defaults = array(
		'type'                 => '',
		'name'                 => '',
		'data_type'            => '',
		'value'                => '',
		'required'             => false,
		'options'              => array(),

		// Set default value to field.
		'default'              => '',

		// Add label, before and after field.
		'label'                => '',
		'label_position'       => 'before',

		// Add description to field as tooltip.
		'tooltip'              => '',

		// Show multiple fields in same row with in sub section.
		'sub_section_start'    => false,
		'sub_section_end'      => false,

		// Add custom attributes.
		'field_attributes'     => array(),
		'wrapper_attributes'   => array(),

		// Show/Hide field in before/after modal view.
		'show_without_modal'   => false,
		'show_within_modal'    => true,

		// Params to edit field html.
		'before_field'         => '',
		'after_field'          => '',
		'before_field_wrapper' => '',
		'after_field_wrapper'  => '',
		'before_label'         => '',
		'after_label'          => '',

		// Manually render field.
		'callback'             => '',

	);

	/**
	 * The defaults for all elements
	 *
	 * @since  1.9
	 * @access static
	 */
	static $section_defaults = array(
		'label'            => '',
		'name'             => '',
		'field_attributes' => array(),

		// Manually render section.
		'callback'         => '',
	);


	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Initialize this module
	 *
	 * @since  1.9
	 * @access static
	 */
	public function init() {
		add_filter( 'give_form_api_render_form_tags', array( $this, 'render_tags' ), 10, 2 );
	}


	/**
	 * Render custom field.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	private function render_custom_field( $field ) {
		$field_html = '';

		if ( empty( $field['callback'] ) ) {
			$callback = $field['callback'];

			// Process callback to get field html.
			if ( is_string( $callback ) && function_exists( "$callback" ) ) {
				$field_html = $callback( $field );
			} elseif ( is_array( $callback ) && method_exists( $callback[0], "$callback[1]" ) ) {
				$field_html = $callback[0]->$callback[1]( $field );
			}
		}

		return $field_html;
	}

	/**
	 * Render tag
	 *
	 * @since   1.9
	 * @access  public
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return string
	 */
	public static function render_tag( $field, $form = null ) {
		$field_html     = '';
		$functions_name = "render_{$field['type']}_field";

		if ( method_exists( self::$instance, $functions_name ) ) {
			$field_html .= self::$instance->{$functions_name}( $field );
		} else {
			$field_html .= apply_filters( "give_field_api_render_{$field['type']}_field", '', $field, $form );
		}

		return $field_html;
	}


	/**
	 * Render `{{form_fields}}` tag.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  string $form_html
	 * @param  array  $form
	 *
	 * @return string
	 */
	public function render_tags( $form_html, $form ) {
		// Bailout: If form does not contain any field.
		if ( empty( $form['fields'] ) ) {
			str_replace( '{{form_fields}}', '', $form_html );

			return $form_html;
		}

		$fields_html = '';

		// Set responsive fields.
		self::$instance->set_responsive_field( $form );

		// Render fields.
		foreach ( $form['fields'] as $key => $field ) {
			// Set default value.
			$field['name'] = empty( $field['name'] ) ? $key : $field['name'];
			$field         = self::$instance->set_default_values( $field, $form );


			// Render custom form with callback.
			if ( $field_html = self::$instance->render_custom_field( $field ) ) {
				$fields_html .= $field_html;
			}

			switch ( true ) {
				// Section.
				case array_key_exists( 'fields', $field ):
					// Set default values.
					foreach ( $field['fields'] as $section_field_index => $section_field ) {
						$section_field['name'] = empty( $section_field['name'] )
							? $section_field_index
							: $section_field['name'];

						$field['fields'][ $section_field_index ] = self::$instance->set_default_values( $section_field );
					}

					$fields_html .= self::$instance->render_section( $field, $form );

					break;

				// Field
				default:
					$fields_html .= self::render_tag( $field, $form );
			}
		}

		$form_html = str_replace( '{{form_fields}}', $fields_html, $form_html );

		return $form_html;
	}


	/**
	 * Render section.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param array $section
	 * @param array $form
	 *
	 * @return string
	 */
	public static function render_section( $section, $form = null ) {
		ob_start();
		?>
		<fieldset <?php echo self::$instance->get_field_attributes( $section ); ?>>
			<?php
			// Legend.
			if ( ! empty( $section['label'] ) ) {
				echo "<legend>{$section['label']}</legend>";
			};

			// Fields.
			foreach ( $section['fields'] as $key => $field ) {
				$field['name'] = empty( $field['name'] ) ? $key : $field['name'];
				echo self::render_tag( $field, $form );
			}
			?>
		</fieldset>
		<?php
		return ob_get_clean();
	}


	/**
	 * Render text field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_text_field( $field ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();
		?>
		<input
				type="<?php echo $field['type']; ?>"
				name="<?php echo $field['name']; ?>"
				value="<?php echo $field ['value']; ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_field_attributes( $field ); ?>
		>
		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render submit field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_submit_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render checkbox field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_checkbox_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render email field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_email_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render number field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_number_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render password field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_password_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render textarea field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_textarea_field( $field ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();
		?>
		<textarea
				name="<?php echo $field['name']; ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_field_attributes( $field ); ?>
		><?php echo $field ['value']; ?></textarea>


		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render select field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_select_field( $field ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();

		$options_html = '';
		foreach ( $field['options'] as $key => $option ) {
			$options_html .= "<option value=\"{$key}\">{$option}</option>";
		}
		?>

		<select
				name="<?php echo $field['name']; ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_field_attributes( $field ); ?>
		><?php echo $options_html; ?></select>
		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render multi select field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_multi_select_field( $field ) {
		$field['field_attributes'] = array_merge( $field['field_attributes'], array( 'multiple' => 'multiple' ) );
		$field['name']             = "{$field['name']}[]";

		return self::$instance->render_select_field( $field );
	}

	/**
	 * Render text field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_radio_field( $field ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();
		foreach ( $field['options'] as $key => $option ) :
			// @todo id issue.
			?>
			<input
			type="<?php echo $field['type']; ?>"
			name="<?php echo $field['name']; ?>"
			value="<?php echo $key; ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_field_attributes( $field ); ?>
			><?php echo $option; ?>
			<?php
		endforeach;

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}


	/**
	 * Render wrapper
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function render_field_wrapper( $field ) {
		ob_start();

		echo $field['before_field_wrapper'];
		?>
		<p <?php echo self::$instance->get_row_attributes( $field ); ?>>
			<?php
			// Label: before field.
			if ( 'before' === $field['label_position'] ) {
				echo self::$instance->render_label( $field );
			}

			echo $field['before_field'];
			?>

			{{form_field}}

			<?php
			echo $field['before_field'];

			// Label: before field.
			if ( 'after' === $field['label_position'] ) {
				echo self::$instance->render_label( $field );
			}
			?>
		</p>
		<?php
		echo $field['after_field_wrapper'];

		return ob_get_clean();
	}


	/**
	 * Render label
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function render_label( $field ) {
		ob_start();
		?>
		<?php if ( ! empty( $field['label'] ) ) : ?>
			<?php echo $field['before_label']; ?>
			<label class="give-label" for="<?php echo $field['field_attributes']['id']; ?>">

				<?php echo $field['label']; ?>

				<?php if ( $field['required'] ) : ?>
					<span class="give-required-indicator">*</span>
				<?php endif; ?>

				<?php if ( $field['tooltip'] ) : ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo $field['tooltip'] ?>"></span>
				<?php endif; ?>
			</label>
			<?php echo $field['after_label']; ?>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get field attribute string from field arguments.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param array $attributes
	 *
	 * @return array|string
	 */
	private function get_attributes( $attributes ) {
		$field_attributes_val = '';

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute_name => $attribute_val ) {
				$field_attributes_val[] = "{$attribute_name}=\"{$attribute_val}\"";
			}
		}

		if ( ! empty( $field_attributes_val ) ) {
			$field_attributes_val = implode( ' ', $field_attributes_val );
		}

		return $field_attributes_val;
	}

	/**
	 * Get field attribute string from field arguments.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return array|string
	 */
	private function get_field_attributes( $field ) {
		return self::$instance->get_attributes( $field['field_attributes'] );
	}

	/**
	 * Get row attribute string from field arguments.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return array|string
	 */
	private function get_row_attributes( $field ) {
		return self::$instance->get_attributes( $field['wrapper_attributes'] );;
	}

	/**
	 * Set default values for fields
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $field
	 * @param array $form
	 *
	 * @return array
	 */
	private function set_default_values( $field, $form = null ) {
		$is_field = array_key_exists( 'fields', $field ) ? false : true;

		// Get default values for section or field.
		$default_values = ! $is_field
			? self::$section_defaults
			: self::$field_defaults;

		// Default field classes.
		$default_class = ! $is_field ? 'give-form-section js-give-form-section give-clearfix' : "give-field give-field-js give-field-type-{$field['type']}";

		// Set default values for field or section.
		$field = wp_parse_args( $field, $default_values );

		// Set ID.
		$field['field_attributes']['id'] = empty( $field['field_attributes']['id'] )
			? ( $is_field ? "give-{$field['name']}-field" : "give-{$field['name']}-section" )
			: $field['field_attributes']['id'];

		// Set class.
		$field['field_attributes']['class'] = empty( $field['field_attributes']['class'] )
			? $default_class
			: "{$default_class} {$field['field_attributes']['class']}";

		// Set wrapper class.
		$field['wrapper_attributes']['class'] = empty( $field['wrapper_attributes']['class'] )
			? 'give-field-row'
			: "give-field-row {$field['wrapper_attributes']['class']}";

		return apply_filters( 'give_field_api_set_default_values', $field, $form );
	}


	/**
	 * Set responsive fields.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $form
	 *
	 * @return mixed
	 */
	private function set_responsive_field( &$form ) {

		foreach ( $form['fields'] as $key => $field ) {
			switch ( true ) {
				case array_key_exists( 'fields', $field ):
					foreach ( $field['fields'] as $section_field_index => $section_field ) {
						if ( ! self::$instance->is_sub_section( $section_field ) ) {
							continue;
						}

						$form['fields'][ $key ]['fields'][ $section_field_index ]['wrapper_attributes']['class'] = 'give-form-col';

						if ( array_key_exists( 'sub_section_end', $section_field ) ) {
							$form['fields'][ $key ]['fields'][ $section_field_index ]['wrapper_attributes']['class'] = 'give-form-col give-form-col-end';

							// Clear float left for next field.
							$fields_keys = array_keys( $form['fields'][ $key ]['fields'] );

							if ( $next_field_key = array_search( $section_field_index, $fields_keys ) ) {
								$next_field = $form['fields'][ $key ]['fields'][ $fields_keys[ $next_field_key + 1 ] ];

								$next_field['wrapper_attributes']['class'] = isset( $next_field['wrapper_attributes']['class'] )
									? $next_field['wrapper_attributes']['class'] . ' give-clearfix'
									: 'give-clearfix';

								$form['fields'][ $key ]['fields'][ $fields_keys[ $next_field_key + 1 ] ] = $next_field;
							}
						}
					}

					break;

				default:
					if ( ! self::$instance->is_sub_section( $field ) ) {
						continue;
					}

					$form['fields'][ $key ]['wrapper_attributes']['class'] = 'give-form-col';

					if ( array_key_exists( 'sub_section_end', $field ) ) {
						$form['fields'][ $key ]['wrapper_attributes']['class'] = 'give-form-col give-form-col-end';

						// Clear float left for next field.
						$fields_keys = array_keys( $form['fields'] );

						if ( $next_field_key = array_search( $key, $fields_keys ) ) {
							$form['fields'][ $fields_keys[ $next_field_key + 1 ] ]['wrapper_attributes']['class'] = 'give-clearfix';
						}
					}
			}
		}
	}


	/**
	 * Check if current feld is part of sub section or not.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	private function is_sub_section( $field ) {
		$is_sub_section = false;
		if (
			array_key_exists( 'sub_section_start', $field )
			|| array_key_exists( 'sub_section_end', $field )
		) {
			$is_sub_section = true;
		}

		return $is_sub_section;
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
}
// @todo auto fill field values.