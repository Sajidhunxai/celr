<?php
/**
 * Divider Field.
 *
 * @package     weLaunchFramework/Fields
 * @author      Dovy Paukstys & Kevin Provance (kprovance)
 * @version     4.0.0
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'weLaunch_Divide', false ) ) {

	/**
	 * Main weLaunch_divide class
	 *
	 * @since       1.0.0
	 */
	class weLaunch_Divide extends weLaunch_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since         1.0.0
		 * @access        public
		 * @return        void
		 */
		public function render() {
			echo '</td></tr></table>';
			echo '<div data-id="' . esc_attr( $this->field['id'] ) . '" id="divide-' . esc_attr( $this->field['id'] ) . '" class="divide ' . esc_attr( $this->field['class'] ) . '"><div class="inner"><span>&nbsp;</span></div></div>';
			echo '<table class="form-table no-border"><tbody><tr><th></th><td>';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'welaunch-field-divide',
					weLaunch_Core::$url . 'inc/fields/divide/welaunch-divide.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}
	}
}

class_alias( 'weLaunch_Divide', 'weLaunchFramework_Divide' );