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
		'id'                   => '',
		'data_type'            => '',
		'value'                => '',
		'required'             => false,
		'options'              => array(),

		// Set default value to field.
		'default'              => '',

		// Set checkbox value.
		'cbvalue'              => 'on',

		// Field with wrapper.
		'wrapper'              => true,
		'wrapper_type'         => 'p',

		// Add label, before and after field.
		'label'                => '',
		'label_position'       => 'before',
		'label_tooltip'        => '',

		// Show multiple fields in same row with in sub section.
		'sub_section_start'    => false,
		'sub_section_end'      => false,

		// Sortable.
		'sortable'             => false,
		'sortable-icon'        => false,

		// Add custom attributes.
		'label_attributes'     => array(),
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
		'before_field_label'   => '',
		'after_field_label'    => '',

		// Manually render field.
		'callback'             => '',

	);

	/**
	 * The defaults for all sections.
	 *
	 * @since  1.9
	 * @access static
	 */
	static $section_defaults = array(
		'type'               => 'section',
		'label'              => '',
		'id'               => '',
		'section_attributes' => array(),

		// Manually render section.
		'callback'           => '',
	);

	/**
	 * The defaults for all blocks.
	 *
	 * @since  1.9
	 * @access static
	 */
	static $block_defaults = array(
		'type'             => 'block',
		'label'            => '',
		'id'             => '',
		'block_attributes' => array(),

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
	 * @param array $form
	 *
	 * @return bool
	 */
	private function render_custom_field( $field, $form = null ) {
		$field = self::$instance->set_default_values( $field, $form );

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
			$field['id'] = empty( $field['id'] ) ? $key : $field['id'];

			// Render custom form with callback.
			if ( $field_html = self::$instance->render_custom_field( $field, $form ) ) {
				$fields_html .= $field_html;
			}

			switch ( true ) {
				// Block.
				case ( 'block' === self::get_field_type( $field ) ):
					$fields_html .= self::$instance->render_block( $field, $form );
					break;

				// Section.
				case ( 'section' === self::get_field_type( $field ) ):
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
	 * @param array $args Helper argument to render section.
	 *
	 * @return string
	 */
	public static function render_section( $section, $form = null, $args = array() ) {
		// Set default values if necessary.
		if ( ! isset( $args['set_default'] ) || (bool) $args['set_default'] ) {
			$section = self::$instance->set_default_values( $section, $form );
		}

		ob_start();
		?>
		<fieldset <?php echo self::$instance->get_attributes( $section['section_attributes'] ); ?>>
			<?php
			// Legend.
			if ( ! empty( $section['label'] ) ) {
				echo "<legend>{$section['label']}</legend>";
			};

			// Fields.
			foreach ( $section['fields'] as $key => $field ) {
				echo self::render_tag( $field, $form, array( 'set_default' => false ) );
			}
			?>
		</fieldset>
		<?php
		return ob_get_clean();
	}


	/**
	 * Render block.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param array $block
	 * @param array $form
	 * @param array $args Helper argument to render section.
	 *
	 * @return string
	 */
	public static function render_block( $block, $form = null, $args = array() ) {
		// Set default values if necessary.
		if ( ! isset( $args['set_default'] ) || (bool) $args['set_default'] ) {
			$block = self::$instance->set_default_values( $block, $form );
		}

		ob_start();
		?>
		<div <?php echo self::$instance->get_attributes( $block['block_attributes'] ); ?>>
			<?php
			// Fields.
			foreach ( $block['fields'] as $key => $field ) {
				echo array_key_exists( 'fields', $field )
					? self::render_section( $field, $form, array( 'set_default' => false ) )
					: self::render_tag( $field, $form, array( 'set_default' => false ) );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render tag
	 *
	 * @since   1.9
	 * @access  public
	 *
	 * @param array $field
	 * @param array $form
	 * @param array $args Helper argument to render section.
	 *
	 * @return string
	 */
	public static function render_tag( $field, $form = null, $args = array() ) {
		// Enqueue scripts.
		Give_Form_API::enqueue_scripts();

		if( ! empty( $field['sortable'] ) ) {
			wp_enqueue_script('jquery-ui-sortable');
		}

		// Set default values if necessary.
		if ( ! isset( $args['set_default'] ) || (bool) $args['set_default'] ) {
			$field = self::$instance->set_default_values( $field, $form );
		}

		$field_html     = '';
		$functions_name = "render_{$field['type']}_field";

		if ( 'section' === self::$instance->get_field_type( $field ) ) {
			$field_html = self::$instance->render_section( $field, $form, array( 'set_default' => false ) );

		} elseif ( method_exists( self::$instance, $functions_name ) ) {
			$field_html = self::$instance->{$functions_name}( $field, $form, $args );

		}

		/**
		 * Filter the custom field type html.
		 * Developer can use this hook to render custom field type.
		 *
		 * @since 1.9
		 *
		 * @param string $field_html
		 * @param array  $field
		 * @param array  $form
		 */
		$field_html = apply_filters(
			"give_field_api_render_{$field['type']}_field",
			$field_html,
			$field,
			$form,
			$args
		);

		/**
		 * Filter the all field type html.
		 *
		 * @since 1.9
		 *
		 * @param string $field_html
		 * @param array  $field
		 * @param array  $form
		 */
		$field_html = apply_filters(
			"give_field_api_render_field",
			$field_html,
			$field,
			$form,
			$args
		);

		return $field_html;
	}


	/**
	 * Render text field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_text_field( $field, $form = null, $args = array() ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		$field['field_attributes']['name'] = self::get_field_name( $field );
		$field['field_attributes']['type'] = $field['type'];

		if( ! empty( $field['required'] ) ) {
			$field['field_attributes']['required'] = 'required';
			$field['field_attributes']['aria-required'] = 'true';
		}

		ob_start();

		echo '<input ' . self::$instance->get_attributes( $field['field_attributes'] ) . '>';

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render submit field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_submit_field( $field, $form = null, $args = array() ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render checkbox field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_checkbox_field( $field, $form = null, $args = array() ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();
		?>
		<input
				type="checkbox"
				name="<?php echo self::get_field_name( $field ); ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_attributes( $field['field_attributes'] ); ?>
		>
		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render email field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_email_field( $field, $form = null, $args = array() ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render number field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_number_field( $field, $form = null, $args = array() ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render password field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_password_field( $field, $form = null, $args = array() ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render button field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_button_field( $field, $form = null, $args = array() ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render hidden field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_hidden_field( $field, $form = null, $args = array() ) {
		$field['wrapper'] = false;

		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render textarea field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_textarea_field( $field, $form = null, $args = array() ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();
		?>
		<textarea
				name="<?php echo self::get_field_name( $field ); ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_attributes( $field['field_attributes'] ); ?>
		><?php echo $field ['value']; ?></textarea>


		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render select field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_select_field( $field, $form = null, $args = array() ) {
		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();

		$options_html = '';
		foreach ( $field['options'] as $key => $option ) {
			$selected = '';

			if ( is_array( $field['value'] ) ) {
				$selected = in_array( $key, $field['value'] )
					? 'selected="selected"'
					: '';

			} else {
				$selected = selected( $key, $field['value'], false );
			}

			$options_html .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
		}
		?>

		<select
				name="<?php echo self::get_field_name( $field ); ?>"
			<?php echo( $field['required'] ? 'required=""' : '' ); ?>
			<?php echo self::$instance->get_attributes( $field['field_attributes'] ); ?>
		><?php echo $options_html; ?></select>
		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render multi select field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_multi_select_field( $field, $form = null, $args = array() ) {
		$field['field_attributes'] = array_merge( $field['field_attributes'], array( 'multiple' => 'multiple' ) );
		$field['id']             = "{$field['id']}[]";

		return self::$instance->render_select_field( $field );
	}

	/**
	 * Render radio field.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_radio_field( $field, $form = null, $args = array() ) {
		$field['wrapper_type'] = 'p' === $field['wrapper_type']
			? 'fieldset'
			: $field['wrapper_type'];


		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();

		$id_base = $field['field_attributes']['id'];
		unset( $field['field_attributes']['id'] );

		echo '<ul>';
		foreach ( $field['options'] as $key => $option ) :
			?>
			<li>
				<label class="give-label" for="<?php echo "{$id_base}-{$key}" ?>">
					<input
							type="<?php echo $field['type']; ?>"
							name="<?php echo self::get_field_name( $field ); ?>"
							value="<?php echo $key; ?>"
							id="<?php echo "{$id_base}-{$key}"; ?>"
						<?php checked( $key, $field['value'] ) ?>
						<?php echo( $field['required'] ? 'required=""' : '' ); ?>
						<?php echo self::$instance->get_attributes( $field['field_attributes'] ); ?>
					><?php echo $option; ?>
				</label>
			</li>
			<?php
		endforeach;
		echo '</ul>';

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}

	/**
	 * Render multi checkbox field.
	 *
	 * Note:
	 * You can reorder checklist if sortable with help of give_reorder_array function.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_multi_checkbox_field( $field, $form = null, $args = array() ) {
		// Field wrapper.
		$field['wrapper_type'] = 'p' === $field['wrapper_type']
			? 'fieldset'
			: $field['wrapper_type'];

		// Field value.
		$field['value'] = is_array( $field['value'] )
			? $field['value']
			: array();

		// Field type.
		$field['field_attributes']['type'] = 'checkbox';

		// Field name.
		$field['field_attributes']['name'] = self::get_field_name( $field ) . '[]';

		$field_wrapper = self::$instance->render_field_wrapper( $field );
		ob_start();

		$id_base = $field['field_attributes']['id'];
		unset( $field['field_attributes']['id'] );
		
		echo '<ul class="give-checklist-fields" data-give-sortable-list="' . absint( $field['sortable'] ) . '" data-give-sortable-icon="' . absint( $field['sortable-icon'] ) . '">';

		foreach ( $field['options'] as $key => $option ) :
			// Set basic values for field.
			$option = is_array( $option ) ? $option : array( 'label' => $option );
			$option['field_attributes']['id'] = "give-{$id_base}-{$key}";
			$option['field_attributes']['data-give-required'] = ( $field['required'] ? 1 : 0 );
			$option['field_attributes']['value']         = empty( $option['field_attributes']['value'] )
				? $key
				: $option['field_attributes']['value'];

			// Check if field checked or not.
			if (
				! empty( $field['value'] ) && in_array( $key, $field['value'] )
				|| (
					( ! empty( $field['repeater_default_template'] ) || ! empty( $field['repeater_template'] ) )
					&& is_array( $option )
					&& ! empty( $option['checked'] )
				)
			) {
				$option['field_attributes']['checked'] = 'checked';
			}

			// Add extra attribute per checkbox.
			$option['field_attributes'] = is_array( $option ) && ! empty( $option['field_attributes'] )
				? array_merge( $field['field_attributes'], $option['field_attributes'] )
				: $field['field_attributes'];

			// Add field specific class.
			$option['field_attributes']['class'] = trim( "{$option['field_attributes']['class']} give-{$key}" );
			?>
			<li>
				<label class="give-label" for="<?php echo $option['field_attributes']['id']; ?>">
					<input <?php echo self::$instance->get_attributes( $option['field_attributes'] ); ?>><?php echo( ! is_array( $option ) ? $option : ( isset( $option['label'] ) ? $option['label'] : '' ) ); ?>
				</label>
			</li>
			<?php
		endforeach;

		echo '</ul>';

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}


	/**
	 * Render group field
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param  array $fields
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_group_field( $fields, $form = null, $args = array() ) {
		// Bailout.
		if ( ! isset( $fields['fields'] ) || empty( $fields['fields'] ) ) {
			return '';
		}

		$group_numbering       = isset( $fields['options']['group_numbering'] ) ? (int) $fields['options']['group_numbering'] : 0;
		$close_tabs            = isset( $fields['options']['close_tabs'] ) ? (int) $fields['options']['close_tabs'] : 0;
		$repeater_field_values = $fields['value'];
		$header_title          = isset( $fields['options']['header_title'] )
			? $fields['options']['header_title']
			: esc_attr__( 'Group', 'give' );

		$add_default_donation_field = false;

		// Check if level is not created or we have to add default level.
		if ( is_array( $repeater_field_values ) && ( $fields_count = count( $repeater_field_values ) ) ) {
			$repeater_field_values = array_values( $repeater_field_values );
		} else {
			$fields_count               = 1;
			$add_default_donation_field = true;
		}

		$field_wrapper = self::$instance->render_field_wrapper( $fields );

		ob_start();
		?>
		<div class="give-repeatable-field-section" id="<?php echo "{$fields['id']}_field"; ?>"
			 data-group-numbering="<?php echo $group_numbering; ?>" data-close-tabs="<?php echo $close_tabs; ?>">
			<?php if ( ! empty( $fields['label'] ) ) : ?>
				<p class="give-repeater-field-name"><?php echo $fields['label']; ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $fields['description'] ) ) : ?>
				<p class="give-repeater-field-description"><?php echo $fields['description']; ?></p>
			<?php endif; ?>

			<table class="give-repeatable-fields-section-wrapper" cellspacing="0">
				<tbody class="container"<?php echo " data-rf-row-count=\"{$fields_count}\""; ?>>
					<!--Repeater field group template-->
					<tr class="give-template give-row">
						<td class="give-repeater-field-wrap give-column" colspan="2">
							<div class="give-row-head give-move">
								<button type="button" class="give-toggle-btn">
									<span class="give-toggle-indicator"></span>
								</button>
								<span class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-</span>
								<h2>
									<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
								</h2>
							</div>
							<div class="give-row-body">
								<?php
								foreach ( $fields['fields'] as $field_id => $field ) :
									$field['id']     = ! empty( $field['id'] ) ? $field['id'] : $field_id;
									$field['repeat'] = true;
									$field['repeater_template'] = true;

									$field['repeater_field_name'] = self::get_repeater_field_name( $field, $fields );
									$field['field_attributes']['id'] = str_replace( array( '[', ']' ), array( '_', '', ), $field['repeater_field_name'] );

									echo self::render_tag( $field, $form );
								endforeach;
								?>
							</div>
						</td>
					</tr>

					<?php if ( ! empty( $repeater_field_values ) ) : ?>
						<!--Stored repeater field group-->
						<?php foreach ( $repeater_field_values as $index => $field_group ) : ?>
							<tr class="give-row">
								<td class="give-repeater-field-wrap give-column" colspan="2">
									<div class="give-row-head give-move">
										<button type="button" class="give-toggle-btn">
											<span class="give-toggle-indicator"></span>
										</button>
										<sapn class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
										</sapn>
										<h2>
											<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
										</h2>
									</div>
									<div class="give-row-body">
										<?php
										foreach ( $fields['fields'] as $field_id => $field ) :
											$field['id']     = ! empty( $field['id'] ) ? $field['id'] : $field_id;
											$field['repeat'] = true;

											$field['repeater_field_name'] = self::get_repeater_field_name( $field, $fields, $index );
											$field['value'] = self::get_repeater_field_value( $field, $field_group, $fields );
											$field['field_attributes']['id']  = str_replace( array( '[', ']' ), array( '_', '', ), $field['repeater_field_name'] );

											echo self::render_tag( $field, $form );
										endforeach;
										?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>

					<?php elseif ( $add_default_donation_field ) : ?>
						<!--Default repeater field group-->
						<tr class="give-row">
							<td class="give-repeater-field-wrap give-column" colspan="2">
								<div class="give-row-head give-move">
									<button type="button" class="give-toggle-btn">
										<span class="give-toggle-indicator"></span>
									</button>
									<sapn class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
									</sapn>
									<h2>
										<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
									</h2>
								</div>
								<div class="give-row-body">
									<?php
									foreach ( $fields['fields'] as $field_id => $field ) :
										$field['id']     = ! empty( $field['id'] ) ? $field['id'] : $field_id;
										$field['repeat'] = true;
										$field['repeater_default_template'] = true;

										$field['repeater_field_name'] = self::get_repeater_field_name( $field, $fields, 0 );
										$field['field_attributes']['id']  = str_replace( array( '[', ']' ), array( '_', '', ), $field['repeater_field_name'] );

										echo self::render_tag( $field, $form );
									endforeach;
									?>
								</div>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<tr>
						<?php
						$add_row_btn_title = isset( $fields['options']['add_button'] )
							? $add_row_btn_title = $fields['options']['add_button']
							: esc_html__( 'Add Row', 'give' );
						?>
						<td colspan="2" class="give-add-repeater-field-section-row-wrap">
							<button class="button button-primary give-add-repeater-field-section-row"><?php echo $add_row_btn_title; ?></button>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php

		return str_replace( '{{form_field}}', ob_get_clean(), $field_wrapper );
	}


	/**
	 * Render wrapper
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function render_field_wrapper( $field, $form = null, $args = array() ) {
		ob_start();

		// Label: before field.
		if ( 'before' === $field['label_position'] ) {
			echo self::$instance->render_label( $field );
		}

		echo "{$field['before_field']}{{form_field}}{$field['after_field']}";

		// Label: before field.
		if ( 'after' === $field['label_position'] ) {
			echo self::$instance->render_label( $field );
		}

		$field_with_label = ob_get_clean();

		ob_start();

		if ( $field['wrapper'] ) :

			echo $field['before_field_wrapper'];

			echo '<' . $field['wrapper_type'] . ' ' . self::$instance->get_attributes( $field['wrapper_attributes'] ) . '>';
				echo $field_with_label;
			echo "</{$field['wrapper_type']}>";

			echo $field['after_field_wrapper'];
		else :
			echo $field_with_label;
		endif;

		return ob_get_clean();
	}


	/**
	 * Render label
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	private function render_label( $field, $form = null, $args = array() ) {
		ob_start();
		?>
		<?php if ( ! empty( $field['label'] ) ) : ?>
			<?php echo $field['before_field_label']; ?>
			<label for="<?php echo $field['field_attributes']['id']; ?>" <?php echo self::get_attributes( $field['label_attributes'] ); ?>>

				<?php echo $field['label']; ?>

				<?php if ( $field['required'] ) : ?>
					<span class="give-required-indicator">*</span>
				<?php endif; ?>

				<?php if ( $field['label_tooltip'] ) : ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo $field['label_tooltip'] ?>"></span>
				<?php endif; ?>
			</label>
			<?php echo $field['after_field_label']; ?>
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
	 * Set default values for fields
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $field
	 * @param array $form
	 * @param array $args
	 *
	 * @return array
	 */
	public static function set_default_values( $field, $form = null, $args = array() ) {
		/**
		 * Filter the field before set default values.
		 *
		 * @since 1.9
		 *
		 * @param array $field
		 * @param array $form
		 */
		$field = ! empty( $args['fire_filter'] )
			? apply_filters( 'give_field_api_pre_set_default_values', $field, $form )
			: $field;

		switch ( self::$instance->get_field_type( $field ) ) {
			case 'block':
				// Set default values for block.
				$field = wp_parse_args( $field, self::$block_defaults );

				// Set wrapper class.
				$field['block_attributes']['class'] = empty( $field['block_attributes']['class'] )
					? "give-block-wrap js-give-block-wrap give-block-{$field['id']}"
					: trim( "give-block-wrap js-give-block-wrap give-block-{$field['id']} {$field['block_attributes']['class']}" );

				foreach ( $field['fields'] as $key => $single_field ) {
					$single_field['id']    = ! empty( $single_field['id'] )
						? $single_field['id']
						: $key;
					$field['fields'][ $key ] = self::$instance->set_default_values( $single_field, $form, array( 'fire_filter' => false ) );
				}

				break;

			case 'section':
				// Set default values for block.
				$field = wp_parse_args( $field, self::$section_defaults );

				// Set wrapper class.
				$field['section_attributes']['class'] = empty( $field['section_attributes']['class'] )
					? 'give-section-wrap'
					: trim( "give-section-wrap {$field['section_attributes']['class']}" );

				foreach ( $field['fields'] as $key => $single_field ) {
					$single_field['id']    = ! empty( $single_field['id'] )
						? $single_field['id']
						: $key;
					$field['fields'][ $key ] = self::$instance->set_default_values( $single_field, $form, array( 'fire_filter' => false ) );
				}

				break;

			default:
				// Set default values for field or section.
				$field = wp_parse_args( $field, self::$field_defaults );

				// Set default class for label.
				$field['label_attributes']['class'] = empty( $field['label_attributes']['class'] )
					? 'give-label'
					: trim( "give-label {$field['label_attributes']['class']}" );

				// Set field id.
				$field['field_attributes']['id'] = ! isset( $field['field_attributes']['id'] )
					? "give-{$field['id']}-field"
					: $field['field_attributes']['id'];

				// Set field class.
				$field['field_attributes']['class'] = empty( $field['field_attributes']['class'] )
					? "give-field js-give-field"
					: trim( "give-field js-give-field {$field['field_attributes']['class']}" );

				// Set wrapper class.
				$field['wrapper_attributes']['class'] = empty( $field['wrapper_attributes']['class'] )
					? "give-field-wrap {$field['id']}_field"
					: trim( "give-field-wrap {$field['id']}_field {$field['wrapper_attributes']['class']}"  );

				// if( 'group' === $field['type'] && ! empty( $field['fields'] ) ) {
				// 	foreach ( $field['fields'] as $key => $single_field ) {
				// 		$single_field['id']    = ! empty( $single_field['id'] )
				// 			? $single_field['id']
				// 			: $key;
				// 		$single_field['repeat'] = true;
				// 		$field['fields'][ $key ] = self::$instance->set_default_values( $single_field, $form, false );
				// 	}
				// }

				/**
				 * Filter the field values.
				 *
				 * @since 1.9
				 *
				 * @param array $field
				 * @param array $form
				 */
				$field = apply_filters( 'give_field_api_set_values', $field, $form );
		}

		/**
		 * Filter the field after set default values.
		 *
		 * @since 1.9
		 *
		 * @param array $field
		 * @param array $form
		 */
		$field = ! empty( $args['fire_filter'] )
			? apply_filters( 'give_field_api_post_set_default_values', $field, $form )
			: $field;

		return $field;
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
								if (
									! isset( $fields_keys[ $next_field_key + 1 ] )
									|| ! isset( $form['fields'][ $key ]['fields'][ $fields_keys[ $next_field_key + 1 ] ] )
								) {
									continue;
								}

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
	 * Check if current field is part of sub section or not.
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
		if ( array_key_exists( 'sub_section_start', $field ) || array_key_exists( 'sub_section_end', $field ) ) {
			$is_sub_section = true;
		}

		return $is_sub_section;
	}


	/**
	 * Get field type.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return bool
	 */
	public static function get_field_type( $field, $form = null, $args = array() ) {
		$field_type = 'field';

		if (
			isset( $field['type'] )
			&& 'block' === $field['type']
		) {
			$field_type = 'block';

		} elseif (
			array_key_exists( 'fields', $field )
			&& 'section' === $field['type']
		) {
			$field_type = 'section';

		}

		return $field_type;
	}

	/**
	 * Get field name.
	 *
	 * @since  1.9
	 *
	 * @param  array $field
	 * @param  array $form
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function get_field_name( $field, $form = null, $args = array() ) {
		$field_name = esc_attr( empty( $field['repeat'] ) ? $field['id'] : $field['repeater_field_name'] );

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
	 * Get repeater field id.
	 *
	 * @since  1.9
	 *
	 * @param array    $field
	 * @param array    $fields
	 * @param int|bool $default
	 *
	 * @return string
	 */
	public static function get_repeater_field_name( $field, $fields , $default = false ) {
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
		$field_id = apply_filters( "give_get_repeater_field_{$field['id']}_name", $field_id, $field, $fields, $default );

		/**
		 * Filter the repeater field id
		 *
		 * @since 1.8
		 *
		 * @param string $field_id
		 */
		$field_id = apply_filters( 'give_get_repeater_field_name', $field_id, $field, $fields, $default );

		return $field_id;
	}


	/**
	 * Get repeater field value.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $field
	 * @param array $field_value_group
	 * @param array $fields
	 *
	 * @return mixed
	 */
	public static function get_repeater_field_value( $field, $field_value_group, $fields ) {
		$field_value = ( isset( $field_value_group[ $field['id'] ] ) ? $field_value_group[ $field['id'] ] : '' );

		/**
		 * Filter the specific repeater field value
		 *
		 * @since 1.8
		 *
		 * @param string $field_id
		 */
		$field_value = apply_filters( "give_get_repeater_field_{$field['id']}_value", $field_value, $field, $field_value_group, $fields );

		/**
		 * Filter the repeater field value
		 *
		 * @since 1.8
		 *
		 * @param string $field_id
		 */
		$field_value = apply_filters( 'give_get_repeater_field_value', $field_value, $field, $field_value_group, $fields );

		return $field_value;
	}
}

// @todo implement required and aria-required for all form fields. required should be custom tag because we want custom validation for form instead html5.
