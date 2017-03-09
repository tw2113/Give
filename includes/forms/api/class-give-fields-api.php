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

		// Set default value to field.
		'default'              => '',

		// Add label, before and after field.
		'label'                => '',
		'label_position'       => 'before',

		// Add description to field as tooltip.
		'tooltip'              => '',

		// Add custom attributes.
		'attributes'           => array(),

		// Params to edit field html.
		// @todo: Implement these params.
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
		'label'      => '',
		'name'       => '',
		'attributes' => array(),
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
			$field_html .= apply_filters( "give_fields_api_render_{$field['type']}_field", '', $field, $form );
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

		foreach ( $form['fields'] as $key => $field ) {
			$field['name'] = empty( $field['name'] ) ? $key : $field['name'];
			$field         = self::get_instance()->set_default_values( $field );

			// Render custom form with callback.
			if ( $field_html = self::$instance->render_custom_field( $field ) ) {
				$fields_html .= $field_html;
			}

			switch ( true ) {
				// Section.
				case array_key_exists( 'fields', $field ):
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
		<fieldset <?php echo self::$instance->get_attributes( $section ); ?>>
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
		ob_start();
		?>
		<p class="give-field-row">
			<?php
			// Label: before field.
			if ( 'before' === $field['label_position'] ) {
				echo self::$instance->render_label( $field );
			}
			?>

			<input
					type="<?php echo $field['type']; ?>"
					name="<?php echo $field['name']; ?>"
					value="<?php echo $field ['value']; ?>"
				<?php echo( $field['required'] ? 'required=""' : '' ); ?>
				<?php echo self::$instance->get_attributes( $field ); ?>
			>

			<?php
			// Label: before field.
			if ( 'after' === $field['label_position'] ) {
				echo self::$instance->render_label( $field );
			}
			?>
		</p>
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
	public static function render_submit_field( $field ) {
		return self::$instance->render_text_field( $field );
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
	public static function render_checkbox_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render label
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public static function render_label( $field ) {
		ob_start();
		?>
		<?php if ( ! empty( $field['label'] ) ) : ?>
			<label class="give-label" for="<?php echo $field['attributes']['id']; ?>">

				<?php echo $field['label']; ?>

				<?php if ( $field['required'] ) : ?>
					<span class="give-required-indicator">*</span>
				<?php endif; ?>

				<?php if ( $field['tooltip'] ) : ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo $field['tooltip'] ?>"></span>
				<?php endif; ?>
			</label>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get attribute string from field arguments.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return array|string
	 */
	private function get_attributes( $field ) {
		$field_attributes_val = '';

		if ( ! empty( $field['attributes'] ) ) {
			foreach ( $field['attributes'] as $attribute_name => $attribute_val ) {
				$field_attributes_val[] = "{$attribute_name}=\"{$attribute_val}\"";
			}
		}

		if ( ! empty( $field_attributes_val ) ) {
			$field_attributes_val = implode( ' ', $field_attributes_val );
		}

		return $field_attributes_val;
	}

	/**
	 * Set default values for fields
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	private function set_default_values( $field ) {
		$is_field = array_key_exists( 'fields', $field ) ? false : true;

		// Get default values for section or field.
		$default_values = ! $is_field
			? self::$section_defaults
			: self::$field_defaults;

		// Default field classes.
		$default_class = ! $is_field ? 'give-form-section give-form-section-js give-clearfix' : 'give-form-field give-form-field-js';

		// Set default values for field or section.
		$field = wp_parse_args( $field, $default_values );

		// Set ID.
		$field['attributes']['id'] = empty( $field['attributes']['id'] )
			? ( $is_field ? "give-{$field['name']}-field" : "give-{$field['name']}-section" )
			: $field['attributes']['id'];

		// Set class.
		$field['attributes']['class'] = empty( $field['attributes']['class'] )
			? $default_class
			: implode( ' ', $field['attributes']['class'] ) . $default_class;

		return $field;
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
