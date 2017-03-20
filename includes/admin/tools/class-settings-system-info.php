<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_System_Info
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_System_Info' ) ) :

	/**
	 * Give_Settings_System_Info.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_System_Info {

		/**
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'system-info';
			$this->label = esc_html__( 'System Info', 'give' );

			add_filter( 'give-tools_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give-tools_settings_{$this->id}_page", array( $this, 'output' ) );

			// Do not use main form for this tab.
			if( give_get_current_setting_tab() === $this->id ) {
				add_action( "give-tools_open_form", '__return_empty_string' );
				add_action( "give-tools_close_form", '__return_empty_string' );
			}

			// Render system info field.
			add_action( 'give_admin_field_system_info', array( $this, 'render_system_info_field' ) );
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$GLOBALS['give_hide_save_button'] = true;
			include_once( 'views/html-admin-page-system-info.php' );
		}


		/**
		 * Render system info field.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param $field
		 */
		public function render_system_info_field( $field ) {
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo self::get_field_title( $field ); ?></label>
				</th>
				<td class="give-forminp">
					<?php give_system_info_callback(); ?>
					<?php echo Give_Admin_Settings::get_field_description( $field ); ?>
				</td>
			</tr>
			<?php
		}
	}

endif;

return new Give_Settings_System_Info();
