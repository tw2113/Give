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
	 * Instance.
	 *
	 * @since  1.9
	 * @access private
	 * @var Give_Form_API
	 */
	static private $instance;

	/**
	 * Array of forms.
	 *
	 * @since  1.9
	 * @access private
	 * @var array
	 */
	private static $forms;

	/**
	 * The defaults for all elements
	 *
	 * @since  1.9
	 * @access static
	 */
	static $field_defaults = array(
		'name'          => '',
		'method'        => 'post',
		'action'        => '',
		'fields'        => array(),

		// Add custom attributes.
		'attributes'    => array(),

		// Supported form layout: simple, stepper, reveal, modal, button.
		'display_style' => 'simple',

		// Manually render form.
		'callback'      => ''

		// @todo: Add html edit params.
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
		self::$forms = apply_filters( 'give_form_api_register_form', self::$forms );

		self::$field_defaults['_template'] = include GIVE_PLUGIN_DIR . 'includes/forms/api/view/simple-form-template.php';
		self::$field_defaults['action']    = esc_url( $_SERVER['REQUEST_URI'] );
		self::$field_defaults              = apply_filters( 'give_form_api_form_default_values', self::$field_defaults );

		// Load fields API
		require_once GIVE_PLUGIN_DIR . 'includes/forms/api/class-give-fields-api.php';
		Give_Fields_API::get_instance()->init();

		// Load form api filters
		require_once GIVE_PLUGIN_DIR . 'includes/forms/api/filters.php';

		// Add give_form_api shortcode.
		add_shortcode( 'give_form_api', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'form_api_enqueue' ) );
	}


	/**
	 * Render form by shortcode.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param array $attrs
	 */
	public function render_shortcode( $attrs ) {
		$attrs = shortcode_atts( array( 'id' => '', ), $attrs, 'give_form_api' );

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
	 * @since  1.9
	 * @access static
	 *
	 * @param string $form_slug Form name.
	 *
	 * @return string
	 */
	static function render_form( $form_slug ) {
		$form_html = '';

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
		 * @since 1.9
		 *
		 * @param string $form_html
		 * @param array  $form
		 */
		return apply_filters( 'give_form_api_render_form', $form_html, $form );
	}


	/**
	 * Set default values form form.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $form
	 *
	 * @return array
	 */
	private static function set_default_values( $form ) {
		$form = wp_parse_args( $form, self::$field_defaults );

		// Set template.
		$form['_template'] = 'stepper' === $form['display_style']
			? include GIVE_PLUGIN_DIR . 'includes/forms/api/view/stepper-form-template.php'
			: $form['_template'];

		// Set ID.
		$form['attributes']['id'] = empty( $form['attributes']['id'] )
			? $form['name']
			: $form['attributes']['id'];

		return $form;
	}


	/**
	 * Process a form, filling in $values with what's been posted
	 *
	 * @since  1.9
	 * @access static
	 */
	static function process_form() {
	}

	/**
	 * Recursively process a meta form element, filling in $values accordingly
	 *
	 * @since  1.9
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
					$form_args['name'] = empty( $form_args['name'] ) ? $form_slug : $form_args['name'];
					$form              = self::$instance->set_default_values( $form_args );
					break;
				}
			}
		}

		/**
		 * Filter the result form.
		 *
		 * @since 1.9
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
		 * @since 1.9
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
		 * @since 1.9
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
	 * @since  1.9
	 * @access public
	 */
	public function form_api_enqueue() {
		$js_plugins     = GIVE_PLUGIN_URL . 'assets/js/plugins/';
		$scripts_footer = ( give_is_setting_enabled( give_get_option( 'scripts_footer' ) ) ) ? true : false;

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Register form api script.
		wp_register( 'give-form-api-js', $js_plugins . "give-form-api{$suffix}.js", array( 'jquery' ), GIVE_VERSION, $scripts_footer );
	}
}

// Initialize field API.
function give_init_forms_api() {
	Give_Form_API::get_instance()->init();
}

add_action( 'init', 'give_init_forms_api', 9999 );
