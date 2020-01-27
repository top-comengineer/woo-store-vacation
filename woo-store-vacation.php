<?php
/**
 * The `Woo Store Vacation` bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 * 
 * Woo Store Vacation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * @link              		https://www.mypreview.one
 * @since             		1.3.1
 * @package           		woo-store-vacation
 * @author     		  		MyPreview (Github: @mahdiyazdani, @mypreview)
 * @copyright 		  		© 2015 - 2019 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name:       		Woo Store Vacation
 * Plugin URI:        		https://mypreview.github.io/woo-store-vacation
 * Description:       		Put your WooCommerce store in vacation or pause mode with custom notice.
 * Version:           		1.3.1
 * Author:            		MyPreview
 * Author URI:        		https://www.mypreview.one
 * License:           		GPL-2.0
 * License URI:       		http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       		woo-store-vacation
 * Domain Path:       		/languages
 * WC requires at least: 	3.4.0
 * WC tested up to: 		3.9.0
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    wp_die();
} // End If Statement

/**
 * Gets the path to a plugin file or directory.
 * @see 	https://codex.wordpress.org/Function_Reference/plugin_basename
 * @see 	http://php.net/manual/en/language.constants.predefined.php
 */
define( 'WOO_STORE_VACATION_VERSION', '1.3.1' );
define( 'WOO_STORE_VACATION_FILE', __FILE__ );
define( 'WOO_STORE_VACATION_NAME', get_file_data( WOO_STORE_VACATION_FILE, array( 'name' => 'Plugin Name' ) )['name'] );
define( 'WOO_STORE_VACATION_URI', get_file_data( WOO_STORE_VACATION_FILE, array( 'uri' => 'Plugin URI' ) )['uri'] );
define( 'WOO_STORE_VACATION_BASENAME', basename( WOO_STORE_VACATION_FILE ) );
define( 'WOO_STORE_VACATION_PLUGIN_BASENAME', plugin_basename( WOO_STORE_VACATION_FILE ) );
define( 'WOO_STORE_VACATION_DIR_URL', plugin_dir_url( WOO_STORE_VACATION_FILE ) );
define( 'WOO_STORE_VACATION_DIR_PATH', plugin_dir_path( WOO_STORE_VACATION_FILE ) ); 

if ( ! class_exists( 'Woo_Store_Vacation' ) ) :

	/**
	 * The Woo Store Vacation - Class
	 */
	final class Woo_Store_Vacation {

		/**
         * Plugin options.
         * 
         * @var  array   $options
         */
		private $options;
		/**
         * Instance of the class.
         * 
         * @var  object   $_instance
         */
		private static $_instance = NULL;

		/**
		 * Main `Woo_Store_Vacation` instance
		 * Ensures only one instance of `Woo_Store_Vacation` is loaded or can be loaded.
		 *
		 * @access 	public
		 * @return  instance
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			} // End If Statement

			return self::$_instance;

		}

		/**
		 * Setup class.
		 *
		 * @access 	protected
		 * @return  void
		 */
		protected function __construct() {

			add_action( 'init', 																		array( $this, 'textdomain' ), 				10 );
			add_action( 'admin_notices', 																array( $this, 'admin_notices' ), 			10 );
			add_action( 'wp_ajax_woo_store_vacation_dismiss_upsell', 									array( $this, 'dismiss_upsell' ), 			10 );
			add_action( 'admin_menu', 																	array( $this, 'add_submenu_page' ), 	   999 );
			add_action( 'admin_init', 																	array( $this, 'register_settings' ), 		10 );
			add_action( 'admin_enqueue_scripts', 														array( $this, 'enqueue' ), 					10 );
			add_action( 'woocommerce_loaded', 															array( $this, 'close_the_shop' ), 			10 );
			add_filter( 'plugin_row_meta', 																array( $this, 'meta_links' ), 			 10, 2 );
			add_filter( sprintf( 'plugin_action_links_%s', WOO_STORE_VACATION_PLUGIN_BASENAME ), 		array( $this, 'action_links' ), 	 	 10, 1 );

		}

		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @access 	protected
		 * @return  void
		 */
		protected function __clone() {

			_doing_it_wrong( __FUNCTION__, _x( 'Cloning instances of this class is forbidden.', 'clone', 'woo-store-vacation' ) , WOO_STORE_VACATION_VERSION );

		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, _x( 'Unserializing instances of this class is forbidden.', 'wakeup', 'woo-store-vacation' ) , WOO_STORE_VACATION_VERSION );

		}

		/**
		 * Load languages file and text domains.
		 * Define the internationalization functionality.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function textdomain() {

			load_plugin_textdomain( 'woo-store-vacation', FALSE, dirname( dirname( WOO_STORE_VACATION_PLUGIN_BASENAME ) ) . '/languages/' );

		}

		/**
		 * Query WooCommerce activation.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function admin_notices() {

			// Query WooCommerce activation.
			if ( ! $this->_is_woocommerce() ) {
				$message = sprintf( esc_html_x( '%s requires the following plugin: %sWooCommerce%s', 'admin notice', 'woo-store-vacation' ), sprintf( '<i class="dashicons dashicons-admin-plugins" style="vertical-align:sub"></i> <strong>%s</strong>', WOO_STORE_VACATION_NAME ), '<a href="https://wordpress.org/plugins/woocommerce" target="_blank" rel="noopener noreferrer nofollow"><em>', '</em></a>' );
				printf( '<div class="notice notice-error notice-alt"><p>%s</p></div>', wp_kses_post( $message ) );
				return;
			} // End If Statement

			if ( ! get_transient( 'woo_store_vacation_upsell' ) ) {
				$message = sprintf( esc_html_x( '%s Looking to schedule your shop for vacation based on different dates, times (hours) and even weekdays? &#8594; %sUpgrade to PRO%s', 'admin notice', 'woo-store-vacation' ), '<i class="dashicons dashicons-calendar-alt" style="vertical-align:sub"></i>', sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow"><em>', esc_url( WOO_STORE_VACATION_URI ) ), '</em></a>' );
				printf( '<div class="notice notice-error woocommerce-message notice-alt is-dismissible"><p>%s</p></div>', $message );
			} // End If Statement

		}

		/**
		 * AJAX dismiss up-sell admin notice.
		 * 
		 * @access 	public
		 * @return 	void
		 */
		public function dismiss_upsell() {

			check_ajax_referer( 'woo-store-vacation-upsell-nonce' );
			set_transient( 'woo_store_vacation_upsell', TRUE, WEEK_IN_SECONDS );
			wp_die();

		}

		/**
		 * Create plugin options page.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function add_submenu_page() {

			add_submenu_page( 'woocommerce', _x( 'Woo Store Vacation', 'page title', 'woo-store-vacation' ), _x( 'Store Vacation', 'menu title', 'woo-store-vacation' ), 'manage_woocommerce', 'woo-store-vacation', array( $this, 'render_plugin_page' ) );

		}

		/**
		 * Render and display plugin options page.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function render_plugin_page() {

			$this->options = (array) get_option( 'woo_store_vacation_options' ); ?>

			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h1><?php 
					esc_html_e( 'Woo Store Vacation', 'woo-store-vacation' ); 
				?></h1>
				<div id="poststuff" class="woo-store-vacation">
					<div id="post-body" class="metabox-holder columns-2"><?php 
						settings_errors(); 
						?><div id="post-body-content">
							<form method="POST" id="woo-store-vacation" autocomplete="off" action="options.php">
								<div class="meta-box-sortables ui-sortable">
									<div class="postbox">
										<div class="handlediv"></div>
										<h2 class="hndle">
											<span><?php 
												esc_attr_e( 'Settings', 'woo-store-vacation' ); 
											?></span>
										</h2>
										<div class="inside"><?php
											// Get settings fields
											settings_fields( 'woo_store_vacation_settings_fields' );
											do_settings_sections( 'woo_store_vacation_settings_sections' );
										?></div>
									</div>
								</div><?php
								// Echoes a submit button, with provided text and appropriate class(es). 
								submit_button(); 
							?></form>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="meta-box-sortables">
								<div class="postbox">
									<div class="handlediv"></div>
									<h2 class="hndle">
										<span><?php 
											_ex( 'Schedule your shop’s vacations', 'upsell', 'woo-store-vacation' ); 
										?></span>
									</h2>
									<div class="inside">
										<h4><?php
											_ex( 'Key features:', 'upsell', 'woo-store-vacation' );
										?></h4>
										<ul>
											<li><?php 
												_ex( '&#x2714; One-click store close', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Exclude list of user roles', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Unlimited date-time ranges', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Unlimited weekday hours', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Unlimited notifications', 'upsell', 'woo-store-vacation' );
											?></li>
										</ul>
										<br/>
										<p align="center"><?php 
											printf( esc_html_x( '%sUpgrade to PRO &#8594;%s', 'upsell', 'woo-store-vacation' ), sprintf( '<a href="%s" class="button-secondary button-link-delete" target="_blank" rel="noopener noreferrer nofollow" style="width:100%%">', esc_url( WOO_STORE_VACATION_URI ) ), '</a>' ); 
										?></p>
									</div>
								</div>
								<div class="postbox">
									<div class="handlediv"></div>
									<h2 class="hndle">
										<span><?php 
											_ex( 'Looking for help? Hire Me!', 'upsell', 'woo-store-vacation' ); 
										?></span>
									</h2>
									<div class="inside">
										<p><?php 
											_ex( 'I am a full-stack developer with over five years of experience in WordPress theme and plugin development and customization.', 'upsell', 'woo-store-vacation' );
										?></p>
										<p><?php 
											_ex( 'I would love to have the opportunity to discuss your project with you.', 'upsell', 'woo-store-vacation' );
										?></p>
										<h4><?php
											_ex( 'My services include:', 'upsell', 'woo-store-vacation' );
										?></h4>
										<ul>
											<li><?php 
												_ex( '&#x2714; Theme creation from scratch', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Plugin development & customization', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Gutenberg custom block development', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; HTML/CSS to WordPress', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; PSD/Sketch/Figma to WordPress', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Troubleshooting/Error fix', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#x2714; Transferring WordPress websites', 'upsell', 'woo-store-vacation' );
											?></li>
										</ul>
										<h4><?php
											_ex( 'Why clients choose me?', 'upsell', 'woo-store-vacation' );
										?></h4>
										<ul>
											<li><?php 
												_ex( '&#9733; Verified Freelancer of UpWork', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#9733; Rated 5.0 out of 5.0 for services', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#9733; Guaranteeing 100% satisfaction', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#9733; Competitive pricing & support', 'upsell', 'woo-store-vacation' );
											?></li>
											<li><?php 
												_ex( '&#9733; Expert in WooCommerce', 'upsell', 'woo-store-vacation' );
											?></li>
										</ul>
										<br/>
										<p align="center">
											<a href="https://www.upwork.com/o/profiles/users/_~016ad17ad3fc5cce94/" class="button-primary" target="_blank" rel="noopener noreferrer nofollow" style="width:100%"><?php 
												_ex( 'Hire me &#8594;', 'upsell', 'woo-store-vacation' ); 
										?></a>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div><?php 
		}

		/**
		 * Register plugin settings
		 *
		 * @access 	public
		 * @return  void
		 */
		public function register_settings() {

			// Register a setting and its data.
			register_setting( 'woo_store_vacation_settings_fields', 'woo_store_vacation_options', array( $this, 'sanitize' ) );
			// Add a new section to a settings page.
			add_settings_section( 'woo_store_vacation_settings_section', '', '', 'woo_store_vacation_settings_sections' );
			add_settings_field( 'vacation_mode', sprintf( esc_html_x( 'Set Vacation Mode %s', 'settings field label', 'woo-store-vacation' ), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'vacation_mode_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'disable_purchase', esc_html_x( 'Disable Purchase', 'settings field label', 'woo-store-vacation' ) , array( $this, 'disable_purchase_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field( 'start_date', sprintf( esc_html_x( 'Start Date %s', 'settings field label', 'woo-store-vacation'), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'start_date_callback' ) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'end_date', sprintf( esc_html_x( 'End Date %s', 'settings field label', 'woo-store-vacation' ), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'end_date_callback' ) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'text_color', esc_html_x( 'Text Color', 'settings field label', 'woo-store-vacation' ), array( $this, 'text_color_callback' ) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'background_color', esc_html_x( 'Background Color', 'settings field label', 'woo-store-vacation' ), array( $this, 'background_color_callback' ) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'btn_txt', esc_html_x( 'Button Text', 'settings field label', 'woo-store-vacation' ), array( $this, 'btn_txt_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'btn_url', esc_html_x( 'Button URL', 'settings field label', 'woo-store-vacation' ), array( $this, 'btn_url_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'vacation_notice', esc_html_x( 'Vacation Notice', 'settings field label', 'woo-store-vacation' ), array( $this, 'vacation_notice_callback' ) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );


		}

		/**
		 * Sanitization Callback.
		 *
		 * @access 	public
		 * @param  	array 		$input
		 * @return  array 		$sanitary_values
		 */ 
		private function sanitize( $input ) {

			$sanitary_values = array();

			// Set Vacation Mode
			if ( isset( $input['vacation_mode'] ) ) {
				$sanitary_values['vacation_mode'] = $input['vacation_mode'];
			} // End If Statement

			// Disable Purchase
			if ( isset( $input['disable_purchase'] ) ) {
				$sanitary_values['disable_purchase'] = $input['disable_purchase'];
			} // End If Statement

			// Start Date
			if ( isset( $input['start_date'] ) ) {
				$sanitary_values['start_date'] = sanitize_text_field( $input['start_date'] );
			} // End If Statement

			// End Date
			if ( isset( $input['end_date'] ) ) {
				$sanitary_values['end_date'] = sanitize_text_field( $input['end_date'] );
			} // End If Statement

			// Text Color
			if ( isset( $input['text_color'] ) ) {
               $sanitary_values['text_color'] = sanitize_hex_color( $input['text_color'] );
            } // End If Statement

            // Background Color
			if ( isset( $input['background_color'] ) ) {
               $sanitary_values['background_color'] = sanitize_hex_color( $input['background_color'] );
            } // End If Statement

            // Button Text
            if ( isset( $input['btn_txt'] ) ) {
				$sanitary_values['btn_txt'] = sanitize_text_field( $input['btn_txt'] );
			} // End If Statement

			// Button URL
			if ( isset( $input['btn_url'] ) ) {
				$sanitary_values['btn_url'] = esc_url_raw( $input['btn_url'] );
			} // End If Statement

			// Vacation Notice
			if ( isset( $input['vacation_notice'] ) ) {
				$sanitary_values['vacation_notice'] = sanitize_textarea_field( $input['vacation_notice'] );
			} // End If Statement

			return $sanitary_values;

		}

		/**
		 * Vacation Mode.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function vacation_mode_callback() {

			$value = ( isset( $this->options['vacation_mode'] ) ) ? $this->options['vacation_mode'] : NULL;
			printf( 
				'<input 
					type="checkbox" 
					name="woo_store_vacation_options[vacation_mode]" 
					id="vacation_mode" 
					value="true" 
					%s 
				/>',
				checked( $value, 'true', FALSE ) );
			printf( esc_html_x( '%sWant to go vacation by closing my store publically.%s', 'settings field help', 'woo-store-vacation' ), '<label for="vacation_mode"><em><small>', '</small></em></label>' );

		}

		/**
		 * Disable Purchase.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function disable_purchase_callback() {

			$value = ( isset( $this->options['disable_purchase'] ) ) ? $this->options['disable_purchase'] : NULL;
			printf( 
				'<input 
					type="checkbox" 
					name="woo_store_vacation_options[disable_purchase]" 
					id="disable_purchase" 
					value="true" 
					%s 
				/>',
				checked( $value, 'true', FALSE ) );
			printf( esc_html_x( '%sThis will disable eCommerce functionality and takes out the cart, checkout process and add to cart buttons.%s', 'settings field help', 'woo-store-vacation' ), '<label for="disable_purchase"><em><small style="color:red;">', '</small></em></label>' );

		}

		/**
		 * Start Date.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function start_date_callback() {

			$value = isset( $this->options['start_date'] ) ? $this->options['start_date'] : NULL;
			printf('
				<input 
					type="text" 
					class="regular-text woo-store-vacation-start-datepicker" 
					name="woo_store_vacation_options[start_date]" 
					id="start_date" 
					value="%s" 
					readonly="readonly" 
				/>', esc_attr( $value ) );
		}

		/**
		 * End Date.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function end_date_callback() {

			$today = strtotime( current_time( 'Y-m-d', $gmt = 0 ) );
			$value = isset( $this->options['end_date'] ) ? $this->options['end_date'] : NULL;
			$is_date_passed = NULL;
			$invalid_date_style = NULL;

			if ( $today > strtotime( $value ) && isset( $value ) && ! empty( $value ) ) {
				$invalid_date_style = 'style="border:1px solid red;"';
				$is_date_passed = sprintf( esc_html_x( '%sThe date has already passed.%s', 'woo-store-vacation' ), '<small style="color:red;"><em>', '</em></small>' );
			} // End If Statement

			printf( 
				'<input 
					type="text" 
					class="regular-text woo-store-vacation-end-datepicker" 
					name="woo_store_vacation_options[end_date]" 
					id="end_date" 
					value="%s" 
					readonly="readonly" 
					%s
				/> %s', esc_attr( $value ), esc_attr( $invalid_date_style ), $is_date_passed );

		}

		/**
		 * Text Color.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function text_color_callback() {

            $value = isset( $this->options['text_color'] ) ? $this->options['text_color'] : '#FFFFFF';
			printf(
				'<input 
					type="text" 
					class="woo-store-vacation-text-color-field" 
					name="woo_store_vacation_options[text_color]"
					data-default-color="#FFFFFF"
					id="text_color" 
					value="%s" 
				/>', sanitize_hex_color( $value ) );

        }

		/**
		 * Background Color.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function background_color_callback() {

			$value = isset( $this->options['background_color'] ) ? $this->options['background_color'] : '#E2401C';
			printf(
				'<input 
					type="text" 
					class="woo-store-vacation-background-color-field" 
					name="woo_store_vacation_options[background_color]" 
					data-default-color="#E2401C"
					id="background_color" 
					value="%s" 
				/>', sanitize_hex_color( $value ) );

        }

        /**
		 * Notice Button Text.
		 *
		 * @access 	public
		 * @return  void
		 */
        public function btn_txt_callback() {

        	$placeholder = _x( 'Contact me &#8594;', 'settings field placeholder', 'woo-store-vacation' );
			$value = isset( $this->options['btn_txt'] ) ? $this->options['btn_txt'] : NULL;
			printf( 
				'<input 
					type="text" 
					class="regular-text" 
					name="woo_store_vacation_options[btn_txt]" 
					id="btn_txt" 
					placeholder="%s" 
					value="%s" 
				/>', esc_attr( $placeholder ), esc_html( $value ) );

		}

		/**
		 * Notice Button URL.
		 *
		 * @access 	public
		 * @return  void
		 */
        public function btn_url_callback() {

        	$placeholder = _x( 'https://www.example.com', 'settings field placeholder', 'woo-store-vacation' );
			$value = isset( $this->options['btn_url'] ) ? $this->options['btn_url'] : NULL;
			printf(
				'<input 
					type="url" 
					class="regular-text" 
					name="woo_store_vacation_options[btn_url]" 
					id="btn_url" 
					placeholder="%s" 
					value="%s" 
				/>', esc_attr( $placeholder ), esc_url( $value ) );

		}

        /**
		 * Notice Content.
		 *
		 * @access 	public
		 * @return  void
		 */
        public function vacation_notice_callback() {

			$placeholder = _x( 'I am currently on vacation and products from my shop will be unavailable for next few days. Thank you for your patience and apologize for any inconvenience.', 'woo-store-vacation' );
			$value = isset( $this->options['vacation_notice'] ) ? esc_attr( $this->options['vacation_notice'] ) : NULL;
			printf(
				'<textarea 
					class="large-text" 
					rows="5" 
					name="woo_store_vacation_options[vacation_notice]" 
					id="vacation_notice" 
					placeholder="%s"
				>%s</textarea>', esc_attr( $placeholder ), esc_html( $value ) );

		}

		/**
		 * Enqueue scripts and styles.
		 * 
		 * @access 	public
		 * @return  void
		 */
		public function enqueue() {

			global $pagenow;

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Make sure that the WooCommerce plugin is active.
			if ( $this->_is_woocommerce() ) {
				wp_register_style( 'jquery-ui-style', sprintf( '%s/assets/css/jquery-ui/jquery-ui.min.css', WC()->plugin_url() ), array( 'wp-color-picker' ), WC_VERSION, 'screen' );
			} // End If Statement
			
			// Enqueue a script.
			wp_register_script( 'woo-store-vacation-script', sprintf( '%sadmin/js/script%s.js', WOO_STORE_VACATION_DIR_URL, $min ), array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), WOO_STORE_VACATION_VERSION, TRUE );
			wp_enqueue_script( 'woo-store-vacation-upsell-script', sprintf( '%sadmin/js/upsell%s.js', WOO_STORE_VACATION_DIR_URL, $min ), array( 'jquery' ), WOO_STORE_VACATION_VERSION, TRUE );
			wp_localize_script( 'woo-store-vacation-upsell-script', 'wsv_vars', array( 'dismiss_nonce' => wp_create_nonce( 'woo-store-vacation-upsell-nonce' ) ) );

			// Make sure the current screen displays plugin’s settings page.
			if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'woo-store-vacation' === $_GET['page'] ) {
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_script( 'woo-store-vacation-script' );
			} // End If Statement

		}

		/**
		 * Determine whether the shop should be closed or not!
		 *
		 * @access 	public
		 * @return  void
		 */
		public function close_the_shop() {

			// Get today’s date and timestamp.
            $today_date = gmdate( 'Y-m-d' );
            $today_timestamp = (int) strtotime( $today_date );
            $timezone = new DateTimeZone( 'UTC' );

			$get_options = (array) get_option( 'woo_store_vacation_options' );
			$vacation_mode = isset( $get_options['vacation_mode'] ) ? $get_options['vacation_mode'] : NULL;
			$disable_purchase = isset( $get_options['disable_purchase'] ) ? $get_options['disable_purchase'] : NULL;
			$start_date = isset( $get_options['start_date'] ) ? $get_options['start_date'] : NULL;
			$end_date = isset( $get_options['end_date'] ) ? $get_options['end_date'] : NULL;
			
			if ( isset( $vacation_mode, $start_date, $end_date ) && wc_string_to_bool( $vacation_mode ) && ! empty( $start_date ) && ! empty( $end_date ) ) {
				// Parses a time string according to a specified format
				$start_date = DateTime::createFromFormat( 'Y-m-d', $start_date, $timezone );
                $end_date = DateTime::createFromFormat( 'Y-m-d', $end_date, $timezone );
                $start_date_formatted = ( is_object( $start_date ) && ! empty( $start_date ) ) ? $start_date->format( 'Y-m-d' ) : NULL;
                $end_date_formatted = ( is_object( $end_date ) && ! empty( $end_date ) ) ? $end_date->format( 'Y-m-d' ) : NULL;
                $start_date_timestamp = ( ! empty( $start_date_formatted ) ) ? strtotime( $start_date_formatted ) : NULL;
                $end_date_timestamp = ( ! empty( $end_date_formatted ) ) ? strtotime( $end_date_formatted ) : NULL;

				if ( $today_timestamp >= $start_date_timestamp && $today_timestamp <= $end_date_timestamp ) {
					if ( isset( $disable_purchase ) && wc_string_to_bool( $disable_purchase ) ) {
						// Make all products unpurchasable.
						add_filter( 'woocommerce_is_purchasable',          	'__return_false',                       -1 );
					} // End If Statement

					add_action( 'woocommerce_before_shop_loop',             array( $this, 'vacation_notice' ), 		 5 );
					add_action( 'woocommerce_before_single_product', 		array( $this, 'vacation_notice' ), 		10 );
					add_action( 'woocommerce_before_cart', 					array( $this, 'vacation_notice' ), 		 5 );
					add_action( 'woocommerce_before_checkout_form', 		array( $this, 'vacation_notice' ), 		 5 );
					add_action( 'wp_print_styles', 							array( $this, 'inline_css' ), 		    99 );
				} // End If Statement
			} // End If Statement
		}

		/**
		 * Adds and store a notice.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function vacation_notice() {

			$get_options = (array) get_option( 'woo_store_vacation_options' );
			$btn_txt = isset( $get_options['btn_txt'] ) ? $get_options['btn_txt'] : NULL;
			$btn_url = isset( $get_options['btn_url'] ) ? $get_options['btn_url'] : NULL;
			$notice = isset( $get_options['vacation_notice'] ) ? $get_options['vacation_notice'] : NULL;

			if ( isset( $notice ) && ! empty( $notice ) ) :
				?><div id="woo-store-vacation"><?php
					if ( empty( $btn_txt ) || empty( $btn_url ) || '#' === $btn_url ) {
						$message = wp_kses_post( nl2br( $notice ) );
					} else {
						$message = sprintf( '<a href="%s" class="woo-store-vacation__btn" target="_self">%s</a> <span class="woo-store-vacation__msg">%s</span>', esc_url( $btn_url ), esc_html( $btn_txt ), wp_kses_post( nl2br( $notice ) ) );
					} // End If Statement
					wc_print_notice( $message, apply_filters( 'woo_store_vacation_notice_type', 'notice' ) );
				?></div><?php
			endif; // End If Statement

		}

		/**
		 * Print inline stylesheet before closing </head> tag.
		 * Specific to `Store vacation` notice message.
		 *
		 * @access 	public
		 * @return  void
		 */
		public function inline_css() {

			// Bailout, if any of the pages below are not displaying at the moment.
			if ( ! is_cart() && ! is_checkout() && ! is_product() && ! is_woocommerce() ) {
				return;
			} // End If Statement

			$get_options = (array) get_option( 'woo_store_vacation_options' );
			$text_color = isset( $get_options['text_color'] ) ? $get_options['text_color'] : '#FFFFFF';
			$background_color = isset( $get_options['background_color'] ) ? $get_options['background_color'] : '#E2401C';

			echo "<style id='woo-store-vacation' type='text/css'>
					#woo-store-vacation .woocommerce-info {
						background-color:{$background_color}!important;
						color:{$text_color} !important;
						z-index:2;
						height:100%;
						text-align:left;
						list-style:none;
						border-top:0;
						border-right:0;
						border-bottom:0;
						border-left:.6180469716em solid rgba(0,0,0,.15);
						border-radius:2px;
						padding:1em 1.618em;
						margin:1.617924em 0 2.617924em 0;
					}
					#woo-store-vacation .woocommerce-info::before {
						content:none;
					}
					.woo-store-vacation__msg {
						display:table-cell;
					}
					.woo-store-vacation__btn {
						color:{$text_color}!important;
						background-color:{$background_color}!important;
						display:table-cell;
						float:right;
						padding:0 0 0 1em;
						background:0 0;
						line-height:1.618;
						margin-left:2em;
						border-width:0;
						border-top:0;
						border-right:0;
						border-bottom:0;
						border-left-width:1px;
						border-left-style:solid;
						border-left-color:rgba(255,255,255,.25)!important;
						border-radius:0;
						box-shadow:none!important;
						text-decoration:none;
					}
				  </style>";
		}

		/**
		 * Display additional links in plugins table page.
		 * Filters the array of row meta for each plugin in the Plugins list table.
		 *
		 * @access 	public
		 * @param   array 		$links
		 * @param 	string 		$plugin_file_name
		 * @return  array 		$links
		 */
		public function meta_links( $links, $plugin_file_name ) {

			$plugin_links = array();

			if ( strpos( $plugin_file_name, WOO_STORE_VACATION_BASENAME ) ) {
				$plugin_links[] = sprintf( _x( '%sUpgrade to PRO%s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow" class="button-link-delete">', esc_url( WOO_STORE_VACATION_URI ) ), '</a>' );
			} // End If Statement

			return array_merge( $links, $plugin_links );

		}
		
		/**
		 * Display additional links in plugins table page.
		 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
		 *
		 * @access 	public
		 * @param   array 		$links
		 * @return  array 		$links
		 */
		public function action_links( $links ) {

			$plugin_links = array();
			$plugin_links[] = sprintf( _x( '%sHire Me!%s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="https://www.upwork.com/o/profiles/users/_~016ad17ad3fc5cce94/" class="button-link-delete" target="_blank" rel="noopener noreferrer nofollow" title="%s">', esc_attr_x( 'Looking for help? Hire Me!', 'upsell', 'woo-additional-terms' ) ), '</a>' );
			$plugin_links[] = sprintf( _x( '%sSupport%s', 'plugin link', 'woo-store-vacation' ), '<a href="https://wordpress.org/support/plugin/woo-store-vacation" target="_blank" rel="noopener noreferrer nofollow">', '</a>' );

			if ( $this->_is_woocommerce() ) {
				$settings_url = add_query_arg( 'page', 'woo-store-vacation', admin_url( 'admin.php' ) );
				$plugin_links[] = sprintf( _x( '%sSettings%s', 'plugin link', 'woo-store-vacation') , sprintf( '<a href="%s" target="_self">', esc_url( $settings_url ) ), '</a>' );
			} // End If Statement

			return array_merge( $plugin_links, $links );

		}

		/**
		 * Query WooCommerce activation
		 *
		 * @access 	private
		 * @return  void
		 */
		private function _is_woocommerce() {

			if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				return FALSE;
			} // End If Statement

			return TRUE;

		}

	}
endif;

/**
 * Returns the main instance of Woo_Store_Vacation to prevent the need to use globals.
 *
 * @return  object(class) 	Woo_Store_Vacation::instance
 */
if ( ! function_exists( 'woo_store_vacation_init' ) ) :
	
	function woo_store_vacation_init() {
		return Woo_Store_Vacation::instance();
	}

	woo_store_vacation_init();
endif;