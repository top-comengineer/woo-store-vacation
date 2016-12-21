<?php
/**
 * Woo Store Vacation Class.
 *
 * @author      Mahdi Yazdani
 * @package     Woo Store Vacation
 * @since       1.0
 */
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) exit;
if ( !class_exists( 'WooStoreVacation' ) ) :
	class WooStoreVacation {
		private $file;
		private $dir;
		private $admin_assets_url;
		private $woo_store_vacation_options;
		public function __construct($file) {
			$this->file = $file;
			$this->dir = dirname( $this->file );
			$this->admin_assets_url = esc_url( trailingslashit( plugins_url( 'admin/', $this->file ) ) );
			add_action( 'admin_menu', array( $this, 'woo_store_vacation_add_plugin_page' ), 99 );
			add_action( 'admin_init', array( $this, 'woo_store_vacation_page_init' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_store_vacation_scripts' ), 10 );
			add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'woo_store_vacation_settings_link' ), 10 );
		}
		public function woo_store_vacation_add_plugin_page() {
			add_submenu_page(
				'woocommerce',
				__('Woo Store Vacation', 'woo-store-vacation'), // page_title
				__('Store Vacation', 'woo-store-vacation'), // menu_title
				'manage_options', // capability
				'woo-store-vacation', // menu_slug
				array( $this, 'woo_store_vacation_create_admin_page' ) // function
			);
		}
		public function woo_store_vacation_create_admin_page() {
			$this->woo_store_vacation_options = get_option( 'woo_store_vacation_option_name' ); ?>
			<div class="wrap">
				<h2><?php _e('Woo Store Vacation', 'woo-store-vacation'); ?></h2>
				<p><?php _e('Put WooCommerce store in vacation or pause mode with custom notice.', 'woo-store-vacation'); ?></p>
				<?php settings_errors(); ?>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'woo_store_vacation_option_group' );
						do_settings_sections( 'woo-store-vacation-admin' );
						submit_button();
					?>
				</form>
			</div>
		<?php }
		public function woo_store_vacation_page_init() {
			register_setting(
				'woo_store_vacation_option_group', // option_group
				'woo_store_vacation_option_name', // option_name
				array( $this, 'woo_store_vacation_sanitize' ) // sanitize_callback
			);
			add_settings_section(
				'woo_store_vacation_setting_section', // id
				'', // title
				array( $this, 'woo_store_vacation_section_info' ), // callback
				'woo-store-vacation-admin' // page
			);
			add_settings_field(
				'enable_vacation_mode', // id
				__('Enable Vacation Mode', 'woo-store-vacation'), // title
				array( $this, 'enable_vacation_mode_callback' ), // callback
				'woo-store-vacation-admin', // page
				'woo_store_vacation_setting_section' // section
			);
			add_settings_field(
				'disable_purchase', // id
				__('Disable Purchase', 'woo-store-vacation'), // title
				array( $this, 'disable_purchase_callback' ), // callback
				'woo-store-vacation-admin', // page
				'woo_store_vacation_setting_section' // section
			);
			add_settings_field(
				'end_date', // id
				__('End Date', 'woo-store-vacation'), // title
				array( $this, 'end_date_callback' ), // callback
				'woo-store-vacation-admin', // page
				'woo_store_vacation_setting_section' // section
			);
			add_settings_field(
				'notice_style', // id
				__('Notice Style', 'woo-store-vacation'), // title
				array( $this, 'notice_style_callback' ), // callback
				'woo-store-vacation-admin', // page
				'woo_store_vacation_setting_section' // section
			);
			add_settings_field(
				'vacation_notice', // id
				__('Vacation Notice', 'woo-store-vacation'), // title
				array( $this, 'vacation_notice_callback' ), // callback
				'woo-store-vacation-admin', // page
				'woo_store_vacation_setting_section' // section
			);
		}
		public function woo_store_vacation_sanitize($input) {
			$sanitary_values = array();
			if ( isset( $input['enable_vacation_mode'] ) ) :
				$sanitary_values['enable_vacation_mode'] = $input['enable_vacation_mode'];
			endif;
			if ( isset( $input['disable_purchase'] ) ) :
				$sanitary_values['disable_purchase'] = $input['disable_purchase'];
			endif;
			if ( isset( $input['end_date'] ) ) :
				$sanitary_values['end_date'] = sanitize_text_field( $input['end_date'] );
			endif;
			if ( isset( $input['notice_style'] ) ) {
				$sanitary_values['notice_style'] = $input['notice_style'];
			}
			if ( isset( $input['vacation_notice'] ) ) :
				$sanitary_values['vacation_notice'] = esc_textarea( $input['vacation_notice'] );
			endif;
			return $sanitary_values;
		}
		public function woo_store_vacation_section_info() {
			
		}
		public function enable_vacation_mode_callback() {
			printf(
				'<input type="checkbox" name="woo_store_vacation_option_name[enable_vacation_mode]" id="enable_vacation_mode" value="enable_vacation_mode" %s />',
				( isset( $this->woo_store_vacation_options['enable_vacation_mode'] ) && $this->woo_store_vacation_options['enable_vacation_mode'] === 'enable_vacation_mode' ) ? 'checked' : ''
			);
		}
		public function disable_purchase_callback() {
			printf(
				'<input type="checkbox" name="woo_store_vacation_option_name[disable_purchase]" id="disable_purchase" value="disable_purchase" %s /> <label for="disable_purchase"><em><small>' . esc_html__( 'Warning: With checking this setting customers won\'t be able to place an order.', 'woo-store-vacation') . '</small></em></label>',
				( isset( $this->woo_store_vacation_options['disable_purchase'] ) && $this->woo_store_vacation_options['disable_purchase'] === 'disable_purchase' ) ? 'checked' : ''
			);
		}
		public function end_date_callback() {
			printf(
				'<input class="regular-text woo-store-vacation-datepicker" type="text" name="woo_store_vacation_option_name[end_date]" id="end_date" value="%s" readonly="readonly" />',
				isset( $this->woo_store_vacation_options['end_date'] ) ? esc_attr( $this->woo_store_vacation_options['end_date']) : ''
			);
		}
		public function notice_style_callback() {
			?> <fieldset><?php $checked = ( isset( $this->woo_store_vacation_options['notice_style'] ) && $this->woo_store_vacation_options['notice_style'] === 'notice' ) ? 'checked' : '' ; ?>
			<label for="notice_style-0"><input type="radio" name="woo_store_vacation_option_name[notice_style]" id="notice_style-0" value="notice" <?php echo $checked; ?>> <?php _e('Info', 'woo-store-vacation'); ?></label>&nbsp;&nbsp;
			<?php $checked = ( isset( $this->woo_store_vacation_options['notice_style'] ) && $this->woo_store_vacation_options['notice_style'] === 'error' ) ? 'checked' : '' ; ?>
			<label for="notice_style-1"><input type="radio" name="woo_store_vacation_option_name[notice_style]" id="notice_style-1" value="error" <?php echo $checked; ?>> <?php _e('Error', 'woo-store-vacation'); ?></label>&nbsp;&nbsp;
			<?php $checked = ( isset( $this->woo_store_vacation_options['notice_style'] ) && $this->woo_store_vacation_options['notice_style'] === 'success' ) ? 'checked' : '' ; ?>
			<label for="notice_style-2"><input type="radio" name="woo_store_vacation_option_name[notice_style]" id="notice_style-2" value="success" <?php echo $checked; ?>> <?php _e('Success', 'woo-store-vacation'); ?></label></fieldset> <?php
		}
		public function vacation_notice_callback() {
			printf(
				'<textarea class="large-text" rows="5" name="woo_store_vacation_option_name[vacation_notice]" id="vacation_notice" />%s</textarea>',
				isset( $this->woo_store_vacation_options['vacation_notice'] ) ? esc_attr( $this->woo_store_vacation_options['vacation_notice']) : ''
			);
		}
		public function woo_store_vacation_scripts() {
			//jQuery UI theme css file
			wp_enqueue_style('woo-store-vacation-jquery-ui-datepicker-css', $this->admin_assets_url . 'css/jquery.ui.datepicker.css', false, '1.0', false);
			//jQuery UI date picker file
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script( 'woo-store-vacation-init-datepicker', $this->admin_assets_url . 'js/custom.js', array('jquery', 'jquery-ui-datepicker'), '1.0', true );
		}
		public function woo_store_vacation_settings_link($links) {
			// Add settings link to plugin list table
			$settings_link = '<a href="admin.php?page=woo-store-vacation">' . __( 'Settings', 'woo-store-vacation' ) . '</a>';
  			array_push( $links, $settings_link );
  			return $links;
		}
	}
endif;