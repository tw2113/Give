<?php

/**
 * Form API
 *
 * @package     Give
 * @subpackage  Classes/Give_Form_API
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */
class Give_Form_API {
	/**
	 * Instance.
	 *
	 * @since  2.0
	 * @access private
	 * @var Give_Form_API
	 */
	static private $instance;

	/**
	 * Array of forms.
	 *
	 * @since  2.0
	 * @access private
	 * @var array
	 */
	private static $forms = array();

	/**
	 * The defaults for all elements
	 *
	 * @since  2.0
	 * @access static
	 */
	static $field_defaults = array(
		'id'                    => '',
		'method'                => 'post',
		'action'                => '',
		'fields'                => array(),

		// Sort field by priority.
		// If this param set to true then define priority for each field.
		'sort_by_priority'      => false,

		// Add custom attributes.
		'form_attributes'       => array(),

		// Supported form layout: simple, stepper, reveal, modal, button.
		'display_style'         => 'simple',
		'continue_button_html'  => '',
		'continue_button_title' => '',

		// Manually render form.
		'callback'              => '',
	);

	/**
	 * Display styles.
	 *
	 * @since  2.0
	 * @access private
	 * @var array
	 */
	private $display_styles = array(
		'simple'  => 'includes/forms/api/view/simple-form-template.php',
		'stepper' => 'includes/forms/api/view/stepper-form-template.php',
		'reveal'  => 'includes/forms/api/view/reveal-form-template.php',
		'modal'   => 'includes/forms/api/view/modal-form-template.php',
		'button'  => 'includes/forms/api/view/button-form-template.php',
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
	 * @since  2.0
	 * @access static
	 */
	public function init() {
		self::$forms = apply_filters( 'give_form_api_register_form', self::$forms );

		self::$field_defaults['_template']             = include GIVE_PLUGIN_DIR . self::$instance->display_styles['simple'];
		self::$field_defaults['continue_button_title'] = __( 'Show Form', 'give' );
		self::$field_defaults['action']                = esc_url( $_SERVER['REQUEST_URI'] );
		self::$field_defaults                          = apply_filters( 'give_form_api_form_default_values', self::$field_defaults );

		// Load fields API
		require_once GIVE_PLUGIN_DIR . 'includes/forms/api/class-give-fields-api.php';
		Give_Fields_API::get_instance()->init();

		// Load form api filters
		require_once GIVE_PLUGIN_DIR . 'includes/forms/api/filters.php';

		// Add give_form_api shortcode.
		add_shortcode( 'give_form_api', array( $this, 'render_shortcode' ) );
		add_action( 'give_wp_enqueue_scripts', array( $this, 'register_form_api_scripts' ) );
		add_action( 'give_admin_enqueue_scripts', array( $this, 'register_form_api_scripts' ) );
	}


	/**
	 * Render form by shortcode.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $attrs
	 */
	public function render_shortcode( $attrs ) {
		$attrs = shortcode_atts( array( 'id' => '' ), $attrs, 'give_form_api' );

		echo self::$instance->render_form( $attrs['id'] );
	}


	/**
	 * Render custom form.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $form
	 *
	 * @return bool
	 */
	private function render_custom_form( $form ) {
		$form_html = '';

		if ( empty( $form['callback'] ) ) {
			$callback = $form['callback'];

			// Process callback to get form html.
			if ( is_string( $callback ) && function_exists( "$callback" ) ) {
				$form_html = $callback( $form );
			} elseif ( is_array( $callback ) && method_exists( $callback[0], "$callback[1]" ) ) {
				$form_html = $callback[0]->$callback[1]( $form );
			}
		}

		return $form_html;
	}

	/**
	 * Render forms.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param string $form_slug Form name.
	 *
	 * @return string
	 */
	static function render_form( $form_slug ) {
		$form_html = '';

		if( is_array( $form_slug ) ) {
			$form = self::set_default_values( $form_slug );
		} else {
			// Handle exception.
			try {
				if (
					empty( $form_slug )
					|| ! is_string( $form_slug )
					|| ! ( $form = self::get_form( $form_slug ) )
				) {
					throw new Exception( __( 'Pass valid form slug to render form.', 'give' ) );
				}
			} catch ( Exception $e ) {
				give_output_error( $e->getMessage(), true, 'error' );

				return $form_html;
			}
		}

		// Enqueue Form API js.
		self::$instance->enqueue_scripts();

		// Render custom form with callback.
		if ( $form_html = self::$instance->render_custom_form( $form ) ) {
			return $form_html;
		}

		// Get all form tags from form template.
		preg_match_all( '/\{\{form_(.+?)?\}\}/', $form['_template'], $form_tags );

		// Render form tags.
		if ( 0 < count( $form_tags ) && ! empty( $form_tags[0] ) ) {
			$form_html = self::render_form_tags( $form_tags[0], $form );
		}

		/**
		 * Filter the form html.
		 *
		 * @since 2.0
		 *
		 * @param string $form_html
		 * @param array  $form
		 */
		return apply_filters( 'give_form_api_render_form', $form_html, $form );
	}


	/**
	 * Set default values form form.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param $form
	 *
	 * @return array
	 */
	private static function set_default_values( $form ) {
		/**
		 * Filter the form values before set default values.
		 *
		 * @since 2.0
		 *
		 * @param array  $form
		 */
		$form = apply_filters( 'give_form_api_pre_set_default_values', $form );

		$form = wp_parse_args( $form, self::$field_defaults );

		// Set template.
		$form['_template'] = array_key_exists( $form['display_style'], self::$instance->display_styles )
			? include GIVE_PLUGIN_DIR . self::$instance->display_styles[ $form['display_style'] ]
			: $form['_template'];

		// Set ID.
		$form['form_attributes']['id'] = empty( $form['form_attributes']['id'] )
			? $form['id']
			: $form['form_attributes']['id'];

		/**
		 * Filter the default values after set form default values.
		 *
		 * @since 2.0
		 *
		 * @param array  $form
		 */
		return apply_filters( 'give_form_api_post_set_default_values', $form );
	}


	/**
	 * Process a form, filling in $values with what's been posted
	 *
	 * @since  2.0
	 * @access static
	 */
	static function process_form() {
	}

	/**
	 * Recursively process a meta form element, filling in $values accordingly
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param string $form_slug
	 *
	 * @return array
	 */
	static function get_form( $form_slug ) {
		$form = array();

		if ( ! empty( self::$forms ) ) {
			foreach ( self::$forms as $index => $form_args ) {
				if ( $form_slug === $index ) {
					$form_args['id'] = empty( $form_args['id'] ) ? $form_slug : $form_args['id'];
					$form              = self::$instance->set_default_values( $form_args );
					break;
				}
			}
		}

		/**
		 * Filter the result form.
		 *
		 * @since 2.0
		 *
		 * @param array  $form
		 * @param string $form_slug
		 * @param        array self::$forms
		 */
		return apply_filters( 'give_form_api_get_form', $form, $form_slug, self::$forms );
	}


	/**
	 * Get forms.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_forms() {
		return self::$forms;
	}

	/**
	 * Get forms.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form_tags
	 * @param array $form
	 *
	 * @return string
	 */
	private function render_form_tags( $form_tags, $form ) {
		$form_html = $form['_template'];

		/**
		 *  Filter the for tags which you want to handle manually.
		 *
		 * @since 2.0
		 *
		 * @param       array
		 * @param array $form
		 * @param array $form_tag
		 */
		$custom_handler_for_form_tags = apply_filters(
			'give_form_api_manually_render_form_tags',
			array( '{{form_attributes}}', '{{form_fields}}' ),
			$form,
			$form_tags
		);

		// Replace form tags.
		foreach ( $form_tags as $form_tag ) {
			$form_param = str_replace( array( '{{form_', '}}' ), '', $form_tag );

			// Process form tags which:
			// 1. Has a value in form arguments.
			// 2. Only has scalar value.
			// 3. Developer do not want to handle them manually.
			if (
				! isset( $form[ $form_param ] )
				|| ! is_scalar( $form[ $form_param ] )
				|| in_array( $form_tag, $custom_handler_for_form_tags )
			) {
				continue;
			}

			$form_html = str_replace( $form_tag, $form[ $form_param ], $form_html );
		}

		/**
		 *  Filters the form tags.
		 *
		 * @since 2.0
		 *
		 * @param string $form_html
		 * @param array  $form
		 * @param array  $form_tags
		 */
		$form_html = apply_filters(
			'give_form_api_render_form_tags',
			$form_html,
			$form,
			$form_tags
		);

		return $form_html;
	}

	/**
	 * Enqueue form api scripts.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function register_form_api_scripts() {
		$js_plugins     = GIVE_PLUGIN_URL . 'assets/js/plugins/';

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( 'give-repeatable-fields', $js_plugins . 'repeatable-fields' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		wp_register_script( 'give-form-api-js', $js_plugins . "give-form-api{$suffix}.js", array( 'jquery', 'give-repeatable-fields', 'jquery-ui-sortable' ), GIVE_VERSION, false );

		/**
		 * Filter the js var.
		 *
		 * @since 2.0
		 */
		$give_form_api_var = apply_filters( 'give_form_api_js_vars', array(
			'metabox_fields' => array(
				'media' => array(
					'button_title' => esc_html__( 'Choose Attachment', 'give' ),
				)
			),
			/* translators : %s: Donation form options metabox */
			'confirm_before_remove_row_text' => __( 'Do you want to delete this level?', 'give' ),
		));


		if ( is_admin() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
			wp_localize_script( 'give-form-api-js', 'give_form_api_var', $give_form_api_var );
		} else {
			wp_localize_script( 'give', 'give_form_api_var', $give_form_api_var );
		}

	}

	/**
	 * Load Form API js var.
	 *
	 * @since  2.0
	 * @access public
	 */
	public static function enqueue_scripts() {
		if ( is_admin() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
			wp_enqueue_script('give-repeatable-fields');
			wp_enqueue_script('give-form-api-js');
		}
	}
}

/**
 * Initialize field API.
 *
 * @since 2.0
 */
function give_init_forms_api() {
	Give_Form_API::get_instance()->init();

	/**
	 * Fire the action when form api loaded.
	 *
	 * @since 2.0
	 */
	do_action( 'give_forms_api_loaded' );
}

add_action( 'init', 'give_init_forms_api', 99 );