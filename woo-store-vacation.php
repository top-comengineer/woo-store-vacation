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
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * @link                    https://www.mypreview.one
 * @since                   1.4.0
 * @package                 woo-store-vacation
 *
 * @wordpress-plugin
 * Plugin Name:             Woo Store Vacation
 * Plugin URI:              https://mypreview.github.io/woo-store-vacation
 * Description:             Pause your store temporarily with scheduling your vacation dates and displaying a user-friendly notice at the top of your shop page.
 * Version:                 1.4.1
 * Author:                  MyPreview
 * Author URI:              https://mahdiyazdani.com
 * License:                 GPL-3.0
 * Requires at least:       WordPress 5.0
 * Requires PHP:            7.2.0
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             woo-store-vacation
 * Domain Path:             /languages
 * WC requires at least:    3.4.0
 * WC tested up to:         5.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Gets the path to a plugin file or directory.
 *
 * @see     https://codex.wordpress.org/Function_Reference/plugin_basename
 * @see     http://php.net/manual/en/language.constants.predefined.php
 */
define( 'WOO_STORE_VACATION_FILE', __FILE__ );
$woo_store_vacation_plugin_data = get_file_data(
	__FILE__,
	array(
		'name'       => 'Plugin Name',
		'uri'        => 'Plugin URI',
		'author_uri' => 'Author URI',
		'version'    => 'Version',
	),
	'plugin'
);
define( 'WOO_STORE_VACATION_NAME', $woo_store_vacation_plugin_data['name'] );
define( 'WOO_STORE_VACATION_URI', $woo_store_vacation_plugin_data['uri'] );
define( 'WOO_STORE_VACATION_AUTHOR_URI', $woo_store_vacation_plugin_data['author_uri'] );
define( 'WOO_STORE_VACATION_VERSION', $woo_store_vacation_plugin_data['version'] );
define( 'WOO_STORE_VACATION_SLUG', 'woo-store-vacation' );
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
		 * @var  object   $instance
		 */
		private static $instance = null;

		/**
		 * Main `Woo_Store_Vacation` instance
		 * Ensures only one instance of `Woo_Store_Vacation` is loaded or can be loaded.
		 *
		 * @since   1.0.0
		 * @return  instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Setup class.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		protected function __construct() {
			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'wp_ajax_woo_store_vacation_dismiss_upsell', array( $this, 'dismiss_upsell' ) );
			add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 999 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'woocommerce_loaded', array( $this, 'close_the_shop' ) );
			add_filter( 'plugin_row_meta', array( $this, 'meta_links' ), 10, 2 );
			add_filter( sprintf( 'plugin_action_links_%s', WOO_STORE_VACATION_PLUGIN_BASENAME ), array( $this, 'action_links' ) );
			register_activation_hook( WOO_STORE_VACATION_FILE, array( $this, 'activation' ) );
			register_deactivation_hook( WOO_STORE_VACATION_FILE, array( $this, 'deactivation' ) );
		}

		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since   1.0.0
		 * @return  void
		 */
		protected function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Cloning instances of this class is forbidden.', 'clone', 'woo-store-vacation' ), esc_html( WOO_STORE_VACATION_VERSION ) );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since   1.0.0
		 * @return  void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Unserializing instances of this class is forbidden.', 'wakeup', 'woo-store-vacation' ), esc_html( WOO_STORE_VACATION_VERSION ) );
		}

		/**
		 * Load languages file and text domains.
		 * Define the internationalization functionality.
		 *
		 * @since   1.0.0
		 * @return  void
		 */
		public function textdomain() {
			load_plugin_textdomain( 'woo-store-vacation', false, dirname( dirname( WOO_STORE_VACATION_PLUGIN_BASENAME ) ) . '/languages/' );
		}

		/**
		 * Query WooCommerce activation.
		 *
		 * @since   1.4.0
		 * @return  void
		 */
		public function admin_notices() {
			// Query WooCommerce activation.
			if ( ! $this->_is_woocommerce() ) {
				/* translators: 1: Dashicon, 2: Open anchor tag, 3: Close anchor tag. */
				$message = sprintf( esc_html_x( '%1$s requires the following plugin: %2$sWooCommerce%3$s', 'admin notice', 'woo-store-vacation' ), sprintf( '<i class="dashicons dashicons-admin-plugins" style="vertical-align:sub"></i> <strong>%s</strong>', WOO_STORE_VACATION_NAME ), '<a href="https://wordpress.org/plugins/woocommerce" target="_blank" rel="noopener noreferrer nofollow"><em>', '</em></a>' );
				printf( '<div class="notice notice-error notice-alt"><p>%s</p></div>', wp_kses_post( $message ) );
				return;
			} else {
				// Display a friendly admin notice upon plugin activation.
				$welcome_notice_transient = 'woo_store_vacation_welcome_notice';
				$welcome_notice           = get_transient( $welcome_notice_transient );
				if ( $welcome_notice ) {
					printf( '<div class="notice notice-info"><p>%s</p></div>', wp_kses_post( $welcome_notice ) );
					delete_transient( $welcome_notice_transient );
				} else {
					if ( ! get_transient( 'woo_store_vacation_upsell' ) ) {
						/* translators: 1: Dashicon, 2: HTML symbol, 3: Open anchor tag, 4: Close anchor tag. */
						$message = sprintf( esc_html_x( '%1$s Automate your closings by defining unlimited number of vacation dates, times (hours), and weekdays without any manual effort needed. %2$s %3$sUpgrade to PRO%4$s', 'admin notice', 'woo-store-vacation' ), '<i class="dashicons dashicons-calendar-alt" style="vertical-align:sub"></i>', '&#8594;', sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow"><button class="button-primary">', esc_url( WOO_STORE_VACATION_URI ) ), '</button></a>' );
						printf( '<div id="%s-dismiss-upsell" class="notice notice-info woocommerce-message notice-alt is-dismissible"><p>%s</p></div>', esc_attr( WOO_STORE_VACATION_SLUG ), wp_kses_post( $message ) );
					}
				}
			}
		}

		/**
		 * AJAX dismiss up-sell admin notice.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function dismiss_upsell() {
			check_ajax_referer( sprintf( '%s-upsell-nonce', WOO_STORE_VACATION_SLUG ) );
			set_transient( 'woo_store_vacation_upsell', true, WEEK_IN_SECONDS );
			wp_die();
		}

		/**
		 * Create plugin options page.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function add_submenu_page() {
			add_submenu_page( 'woocommerce', _x( 'Woo Store Vacation', 'page title', 'woo-store-vacation' ), _x( 'Store Vacation', 'menu title', 'woo-store-vacation' ), 'manage_woocommerce', WOO_STORE_VACATION_SLUG, array( $this, 'render_plugin_page' ) );
		}

		/**
		 * Render and display plugin options page.
		 *
		 * @since   1.4.0
		 * @return  void
		 */
		public function render_plugin_page() {
			$this->options = (array) get_option( 'woo_store_vacation_options' ); ?>

			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h1>
					<?php esc_html_e( 'Woo Store Vacation', 'woo-store-vacation' ); ?>
				</h1>
				<div id="poststuff" class="<?php echo esc_attr( WOO_STORE_VACATION_SLUG ); ?>">
					<div id="post-body" class="metabox-holder columns-2">
						<?php settings_errors(); ?>
						<div id="post-body-content">
							<form method="POST" id="<?php echo esc_attr( WOO_STORE_VACATION_SLUG ); ?>" autocomplete="off" action="options.php">
								<div class="meta-box-sortables ui-sortable">
									<div class="postbox">
										<div class="postbox-header">
											<h2 class="hndle">
												<?php esc_attr_e( 'Settings', 'woo-store-vacation' ); ?>
											</h2>
										</div>
										<div class="inside">
										<?php
											// Get settings fields.
											settings_fields( 'woo_store_vacation_settings_fields' );
											do_settings_sections( 'woo_store_vacation_settings_sections' );
										?>
										</div>
									</div>
								</div>
								<?php
								// Echoes a submit button, with provided text and appropriate class(es).
								submit_button();
								?>
							</form>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="meta-box-sortables">
								<div class="postbox">
									<div class="postbox-header">
										<h2 class="hndle">
											<?php echo esc_html_x( 'Schedule your shop’s vacations', 'upsell', 'woo-store-vacation' ); ?>
										</h2>
									</div>
									<div class="inside">
										<h4>
										<?php echo esc_html_x( 'Key features:', 'upsell', 'woo-store-vacation' ); ?>
										</h4>
										<ul>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s One-click store close', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Exclude list of user roles', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Exclude list of product types', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Allow Shop Managers to edit', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Unlimited date-time ranges', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Unlimited weekday hours', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Unlimited notifications', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
										</ul>
										<br/>
										<p align="center">
										<?php
											/* translators: 1: Open anchor tag, 2: Close anchor tag. */
											printf( esc_html_x( '%1$sUpgrade to PRO &#8594;%2$s', 'upsell', 'woo-store-vacation' ), sprintf( '<a href="%s" class="button-primary button-link-delete" target="_blank" rel="noopener noreferrer nofollow" style="width:100%%">', esc_url( WOO_STORE_VACATION_URI ) ), '</a>' );
										?>
										</p>
									</div>
								</div>
								<div class="postbox">
									<div class="postbox-header">
										<h2 class="hndle">
											<?php echo esc_html_x( 'Looking for help? Hire Me!', 'upsell', 'woo-store-vacation' ); ?>
										</h2>
									</div>
									<div class="inside">
										<p>
											<?php echo esc_html_x( 'I am a full-stack developer with over five years of experience in WordPress theme and plugin development and customization.', 'upsell', 'woo-store-vacation' ); ?>
										</p>
										<p>
											<?php echo esc_html_x( 'I would love to have the opportunity to discuss your project with you.', 'upsell', 'woo-store-vacation' ); ?>
										</p>
										<h4>
											<?php echo esc_html_x( 'My services include:', 'upsell', 'woo-store-vacation' ); ?>
										</h4>
										<ul>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Theme creation from scratch', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Plugin development & customization', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Gutenberg custom block development', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s HTML/CSS to WordPress', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s PSD/Sketch/Figma to WordPress', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Troubleshooting/Error fix', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Transferring WordPress websites', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
										</ul>
										<h4>
											<?php echo esc_html_x( 'Why clients choose me?', 'upsell', 'woo-store-vacation' ); ?>
										</h4>
										<ul>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Verified Top-Rated Plus freelancer', 'upsell', 'woo-store-vacation' ), '&#9733;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Rated 5.0 out of 5.0 for services', 'upsell', 'woo-store-vacation' ), '&#9733;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Guaranteeing 100%% satisfaction', 'upsell', 'woo-store-vacation' ), '&#9733;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Competitive pricing & support', 'upsell', 'woo-store-vacation' ), '&#9733;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Expert in WooCommerce', 'upsell', 'woo-store-vacation' ), '&#9733;' );
												?>
											</li>
										</ul>
										<br/>
										<p align="center">
											<a href="<?php echo esc_url( WOO_STORE_VACATION_AUTHOR_URI ); ?>" class="button-secondary" target="_blank" rel="noopener noreferrer nofollow" style="width:100%">
												<?php echo esc_html_x( 'Hire me &#8594;', 'upsell', 'woo-store-vacation' ); ?>
										</a>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php
		}

		/**
		 * Register plugin settings
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function register_settings() {
			// Register a setting and its data.
			register_setting( 'woo_store_vacation_settings_fields', 'woo_store_vacation_options', array( $this, 'sanitize' ) );
			// Add a new section to a settings page.
			add_settings_section( 'woo_store_vacation_settings_section', '', '', 'woo_store_vacation_settings_sections' );
			/* translators: %s: Abbr tag. */
			add_settings_field( 'vacation_mode', sprintf( esc_html_x( 'Set Vacation Mode %s', 'settings field label', 'woo-store-vacation' ), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'vacation_mode_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'disable_purchase', esc_html_x( 'Disable Purchase', 'settings field label', 'woo-store-vacation' ), array( $this, 'disable_purchase_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			/* translators: %s: Abbr tag. */
			add_settings_field( 'start_date', sprintf( esc_html_x( 'Start Date %s', 'settings field label', 'woo-store-vacation' ), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'start_date_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			/* translators: %s: Abbr tag. */
			add_settings_field( 'end_date', sprintf( esc_html_x( 'End Date %s', 'settings field label', 'woo-store-vacation' ), '<abbr class="required" title="required">*</abbr>' ), array( $this, 'end_date_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'text_color', esc_html_x( 'Text Color', 'settings field label', 'woo-store-vacation' ), array( $this, 'text_color_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'background_color', esc_html_x( 'Background Color', 'settings field label', 'woo-store-vacation' ), array( $this, 'background_color_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'btn_txt', esc_html_x( 'Button Text', 'settings field label', 'woo-store-vacation' ), array( $this, 'btn_txt_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'btn_url', esc_html_x( 'Button URL', 'settings field label', 'woo-store-vacation' ), array( $this, 'btn_url_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
			add_settings_field( 'vacation_notice', esc_html_x( 'Vacation Notice', 'settings field label', 'woo-store-vacation' ), array( $this, 'vacation_notice_callback' ), 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section' );
		}

		/**
		 * Sanitization Callback.
		 *
		 * @since   1.3.0
		 * @param   array $input    An array of option's value.
		 * @return  array
		 */
		private function sanitize( $input ) {
			$sanitary_values = array();

			// Set Vacation Mode.
			if ( isset( $input['vacation_mode'] ) ) {
				$sanitary_values['vacation_mode'] = $input['vacation_mode'];
			}

			// Disable Purchase.
			if ( isset( $input['disable_purchase'] ) ) {
				$sanitary_values['disable_purchase'] = $input['disable_purchase'];
			}

			// Start Date.
			if ( isset( $input['start_date'] ) ) {
				$sanitary_values['start_date'] = sanitize_text_field( $input['start_date'] );
			}

			// End Date.
			if ( isset( $input['end_date'] ) ) {
				$sanitary_values['end_date'] = sanitize_text_field( $input['end_date'] );
			}

			// Text Color.
			if ( isset( $input['text_color'] ) ) {
				$sanitary_values['text_color'] = sanitize_hex_color( $input['text_color'] );
			}

			// Background Color.
			if ( isset( $input['background_color'] ) ) {
				$sanitary_values['background_color'] = sanitize_hex_color( $input['background_color'] );
			}

			// Button Text.
			if ( isset( $input['btn_txt'] ) ) {
				$sanitary_values['btn_txt'] = sanitize_text_field( $input['btn_txt'] );
			}

			// Button URL.
			if ( isset( $input['btn_url'] ) ) {
				$sanitary_values['btn_url'] = filter_var( $input['btn_url'], FILTER_SANITIZE_URL );
			}

			// Vacation Notice.
			if ( isset( $input['vacation_notice'] ) ) {
				$sanitary_values['vacation_notice'] = sanitize_textarea_field( $input['vacation_notice'] );
			}

			return $sanitary_values;
		}

		/**
		 * Vacation Mode.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function vacation_mode_callback() {
			$value = ( isset( $this->options['vacation_mode'] ) ) ? $this->options['vacation_mode'] : null;
			printf(
				'<input 
					type="checkbox" 
					name="woo_store_vacation_options[vacation_mode]" 
					id="vacation_mode" 
					value="true" 
					%s 
				/>',
				checked( $value, 'true', false )
			);
			/* translators: 1: Open label tag, 2: Close label tag. */
			printf( esc_html_x( '%1$sTurn on Vacation mode and close my store publicly.%2$s', 'settings field help', 'woo-store-vacation' ), '<label for="vacation_mode"><em><small>', '</small></em></label>' );
		}

		/**
		 * Disable Purchase.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function disable_purchase_callback() {
			$value = ( isset( $this->options['disable_purchase'] ) ) ? $this->options['disable_purchase'] : null;
			printf(
				'<input 
					type="checkbox" 
					name="woo_store_vacation_options[disable_purchase]" 
					id="disable_purchase" 
					value="true" 
					%s 
				/>',
				checked( $value, 'true', false )
			);
			/* translators: 1: Open label tag, 2: Close label tag. */
			printf( esc_html_x( '%1$sThis will disable eCommerce functionality and takes out the cart, checkout process and add to cart buttons.%2$s', 'settings field help', 'woo-store-vacation' ), '<label for="disable_purchase"><em><small style="color:red;">', '</small></em></label>' );
		}

		/**
		 * Start Date.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function start_date_callback() {
			$value = isset( $this->options['start_date'] ) ? $this->options['start_date'] : null;
			printf(
				'<input 
					type="text" 
					class="regular-text woo-store-vacation-start-datepicker" 
					name="woo_store_vacation_options[start_date]" 
					id="start_date" 
					value="%s" 
					readonly="readonly" 
				/>',
				esc_attr( $value )
			);
		}

		/**
		 * End Date.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function end_date_callback() {
			$today              = strtotime( current_time( 'Y-m-d', $gmt = 0 ) );
			$value              = isset( $this->options['end_date'] ) ? $this->options['end_date'] : null;
			$is_date_passed     = null;
			$invalid_date_style = null;

			if ( $today > strtotime( $value ) && isset( $value ) && ! empty( $value ) ) {
				$invalid_date_style = 'style="border:1px solid red;"';
				/* translators: 1: Open small tag, 2: Close small tag. */
				$is_date_passed = sprintf( esc_html_x( '%1$sThe date has already passed.%2$s', 'error message', 'woo-store-vacation' ), '<small style="color:red;"><em>', '</em></small>' );
			}

			printf(
				'<input 
					type="text" 
					class="regular-text %s-end-datepicker" 
					name="woo_store_vacation_options[end_date]" 
					id="end_date" 
					value="%s" 
					readonly="readonly" 
					%s
				/> %s',
				esc_attr( WOO_STORE_VACATION_SLUG ),
				esc_attr( $value ),
				esc_attr( $invalid_date_style ),
				wp_kses_post( $is_date_passed )
			);
		}

		/**
		 * Text Color.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function text_color_callback() {
			$value = isset( $this->options['text_color'] ) ? $this->options['text_color'] : '#FFFFFF';
			printf(
				'<input 
					type="text" 
					class="%s-text-color-field" 
					name="woo_store_vacation_options[text_color]"
					data-default-color="#FFFFFF"
					id="text_color" 
					value="%s" 
				/>',
				esc_attr( WOO_STORE_VACATION_SLUG ),
				sanitize_hex_color( $value ) // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			);
		}

		/**
		 * Background Color.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function background_color_callback() {
			$value = isset( $this->options['background_color'] ) ? $this->options['background_color'] : '#E2401C';
			printf(
				'<input 
					type="text" 
					class="%s-background-color-field" 
					name="woo_store_vacation_options[background_color]" 
					data-default-color="#E2401C"
					id="background_color" 
					value="%s" 
				/>',
				esc_attr( WOO_STORE_VACATION_SLUG ),
				sanitize_hex_color( $value ) // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			);
		}

		/**
		 * Notice Button Text.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function btn_txt_callback() {
			$placeholder = _x( 'Contact me &#8594;', 'settings field placeholder', 'woo-store-vacation' );
			$value       = isset( $this->options['btn_txt'] ) ? $this->options['btn_txt'] : null;
			printf(
				'<input 
					type="text" 
					class="regular-text" 
					name="woo_store_vacation_options[btn_txt]" 
					id="btn_txt" 
					placeholder="%s" 
					value="%s" 
				/>',
				esc_attr( $placeholder ),
				esc_html( $value )
			);
		}

		/**
		 * Notice Button URL.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function btn_url_callback() {
			$placeholder = _x( 'https://www.example.com', 'settings field placeholder', 'woo-store-vacation' );
			$value       = isset( $this->options['btn_url'] ) ? $this->options['btn_url'] : null;
			printf(
				'<input 
					type="url" 
					class="regular-text" 
					name="woo_store_vacation_options[btn_url]" 
					id="btn_url" 
					placeholder="%s" 
					value="%s" 
				/>',
				esc_attr( $placeholder ),
				esc_url( $value )
			);
		}

		/**
		 * Notice Content.
		 *
		 * @since   1.3.0
		 * @return  void
		 */
		public function vacation_notice_callback() {
			$placeholder = _x( 'I am currently on vacation and products from my shop will be unavailable for next few days. Thank you for your patience and apologize for any inconvenience.', 'settings field placeholder', 'woo-store-vacation' );
			$value       = isset( $this->options['vacation_notice'] ) ? esc_attr( $this->options['vacation_notice'] ) : null;
			printf(
				'<textarea 
					class="large-text" 
					rows="5" 
					name="woo_store_vacation_options[vacation_notice]" 
					id="vacation_notice" 
					placeholder="%s"
				>%s</textarea>',
				esc_attr( $placeholder ),
				esc_html( $value )
			);
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function enqueue() {
			global $pagenow;
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Make sure that the WooCommerce plugin is active.
			if ( $this->_is_woocommerce() ) {
				$wc_plugin_url = WC()->plugin_url();
				wp_register_style( 'jquery-ui-style', sprintf( '%s/assets/css/jquery-ui/jquery-ui.min.css', $wc_plugin_url ), array( 'wp-color-picker' ), WC_VERSION, 'screen' );
				wp_register_style( 'woocommerce-activation', sprintf( '%s/assets/css/activation.css', $wc_plugin_url ), array(), WC_VERSION, 'screen' );
			}

			// Enqueue a script.
			wp_register_script( sprintf( '%s-script', WOO_STORE_VACATION_SLUG ), sprintf( '%sassets/js/script%s.js', WOO_STORE_VACATION_DIR_URL, $min ), array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), WOO_STORE_VACATION_VERSION, true );
			wp_enqueue_script( sprintf( '%s-upsell-script', WOO_STORE_VACATION_SLUG ), sprintf( '%sassets/js/upsell%s.js', WOO_STORE_VACATION_DIR_URL, $min ), array( 'jquery' ), WOO_STORE_VACATION_VERSION, true );
			wp_localize_script( sprintf( '%s-upsell-script', WOO_STORE_VACATION_SLUG ), 'wsvVars', array( 'dismiss_nonce' => wp_create_nonce( sprintf( '%s-upsell-nonce', WOO_STORE_VACATION_SLUG ) ) ) );

			if ( $this->_is_plugin_page() ) {
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'woocommerce-activation' );
				wp_enqueue_script( sprintf( '%s-script', WOO_STORE_VACATION_SLUG ) );
			}
		}

		/**
		 * Determine whether the shop should be closed or not!
		 *
		 * @since   1.3.9
		 * @return  void
		 */
		public function close_the_shop() {
			// Bail early, in case the current request is for an administrative interface page.
			if ( is_admin() ) {
				return;
			}

			// Get today’s date and timestamp.
			$today_date      = gmdate( 'Y-m-d' );
			$today_timestamp = (int) strtotime( $today_date );
			$timezone        = new DateTimeZone( 'UTC' );

			$get_options      = (array) get_option( 'woo_store_vacation_options' );
			$vacation_mode    = isset( $get_options['vacation_mode'] ) ? $get_options['vacation_mode'] : null;
			$disable_purchase = isset( $get_options['disable_purchase'] ) ? $get_options['disable_purchase'] : null;
			$start_date       = isset( $get_options['start_date'] ) ? $get_options['start_date'] : null;
			$end_date         = isset( $get_options['end_date'] ) ? $get_options['end_date'] : null;

			if ( isset( $vacation_mode, $start_date, $end_date ) && wc_string_to_bool( $vacation_mode ) && ! empty( $start_date ) && ! empty( $end_date ) ) {
				// Parses a time string according to a specified format.
				$start_date           = DateTime::createFromFormat( 'Y-m-d', $start_date, $timezone );
				$end_date             = DateTime::createFromFormat( 'Y-m-d', $end_date, $timezone );
				$start_date_formatted = ( is_object( $start_date ) && ! empty( $start_date ) ) ? $start_date->format( 'Y-m-d' ) : null;
				$end_date_formatted   = ( is_object( $end_date ) && ! empty( $end_date ) ) ? $end_date->format( 'Y-m-d' ) : null;
				$start_date_timestamp = ( ! empty( $start_date_formatted ) ) ? strtotime( $start_date_formatted ) : null;
				$end_date_timestamp   = ( ! empty( $end_date_formatted ) ) ? strtotime( $end_date_formatted ) : null;

				if ( $today_timestamp >= $start_date_timestamp && $today_timestamp <= $end_date_timestamp ) {
					if ( isset( $disable_purchase ) && wc_string_to_bool( $disable_purchase ) ) {
						// Make all products unpurchasable.
						add_filter( 'woocommerce_is_purchasable', '__return_false', PHP_INT_MAX );
					}

					add_action( 'woocommerce_before_shop_loop', array( $this, 'vacation_notice' ), 5 );
					add_action( 'woocommerce_before_single_product', array( $this, 'vacation_notice' ), 10 );
					add_action( 'woocommerce_before_cart', array( $this, 'vacation_notice' ), 5 );
					add_action( 'woocommerce_before_checkout_form', array( $this, 'vacation_notice' ), 5 );
					add_action( 'wp_print_styles', array( $this, 'inline_css' ), 99 );
				}
			}
		}

		/**
		 * Adds and store a notice.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function vacation_notice() {
			$get_options = (array) get_option( 'woo_store_vacation_options' );
			$btn_txt     = isset( $get_options['btn_txt'] ) ? $get_options['btn_txt'] : null;
			$btn_url     = isset( $get_options['btn_url'] ) ? $get_options['btn_url'] : null;
			$notice      = isset( $get_options['vacation_notice'] ) ? $get_options['vacation_notice'] : null;

			if ( isset( $notice ) && ! empty( $notice ) ) :
				printf( '<div id="%s">', esc_attr( WOO_STORE_VACATION_SLUG ) );
				if ( empty( $btn_txt ) || empty( $btn_url ) || '#' === $btn_url ) {
					$message = wp_kses_post( nl2br( $notice ) );
				} else {
					$message = sprintf( '<a href="%1$s" class="%2$s__btn" target="_self">%3$s</a> <span class="%2$s__msg">%4$s</span>', esc_url( $btn_url ), sanitize_html_class( WOO_STORE_VACATION_SLUG ), esc_html( $btn_txt ), wp_kses_post( nl2br( $notice ) ) );
				}

					wc_print_notice( $message, apply_filters( 'woo_store_vacation_notice_type', 'notice' ) );
				echo '</div>';
			endif;
		}

		/**
		 * Print inline stylesheet before closing </head> tag.
		 * Specific to `Store vacation` notice message.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function inline_css() {
			// Bailout, if any of the pages below are not displaying at the moment.
			if ( ! is_cart() && ! is_checkout() && ! is_product() && ! is_woocommerce() ) {
				return;
			}

			$get_options      = (array) get_option( 'woo_store_vacation_options' );
			$text_color       = (string) isset( $get_options['text_color'] ) ? $get_options['text_color'] : '#FFFFFF';
			$background_color = (string) isset( $get_options['background_color'] ) ? $get_options['background_color'] : '#E2401C';
			$css              = sprintf(
				'
				#%1$s .woocommerce-info {
					background-color:%2$s !important;
					color:%3$s !important;
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
				#%1$s .woocommerce-info::before {
					content:none;
				}
				.%1$s__msg {
					display:table-cell;
				}
				.%1$s__btn {
					color:%3$s !important;
					background-color:%2$s !important;
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
				}',
				esc_attr( WOO_STORE_VACATION_SLUG ),
				sanitize_hex_color( $background_color ),
				sanitize_hex_color( $text_color )
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<style id="%s" type="text/css">%s</style>%s', esc_attr( WOO_STORE_VACATION_SLUG ), $this->_minify_css( $css ), PHP_EOL );
		}

		/**
		 * Display additional links in plugins table page.
		 * Filters the array of row meta for each plugin in the Plugins list table.
		 *
		 * @since   1.3.8
		 * @param   array  $links               An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
		 * @param   string $plugin_file_name    Path to the plugin file relative to the plugins directory.
		 * @return  array
		 */
		public function meta_links( $links, $plugin_file_name ) {
			$plugin_links = array();

			if ( strpos( $plugin_file_name, WOO_STORE_VACATION_BASENAME ) ) {
				/* translators: 1: Open anchor tag, 2: Close anchor tag. */
				$plugin_links[] = sprintf( _x( '%1$sUpgrade to PRO%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow" class="button-link-delete"><span class="dashicons dashicons-cart" style="font-size:16px;vertical-align:middle;"></span> ', esc_url( WOO_STORE_VACATION_URI ) ), '</a>' );
			}

			return array_merge( $links, $plugin_links );
		}

		/**
		 * Display additional links in plugins table page.
		 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
		 *
		 * @since   1.3.8
		 * @param   array $links  Plugin table/item action links.
		 * @return  array
		 */
		public function action_links( $links ) {
			$plugin_links = array();
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( _x( '%1$sHire Me!%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="%s" class="button-link-delete" target="_blank" rel="noopener noreferrer nofollow" title="%s">', esc_attr( WOO_STORE_VACATION_AUTHOR_URI ), esc_attr_x( 'Looking for help? Hire Me!', 'upsell', 'woo-store-vacation' ) ), '</a>' );
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( _x( '%1$sSupport%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="https://wordpress.org/support/plugin/%s" target="_blank" rel="noopener noreferrer nofollow">', esc_attr( WOO_STORE_VACATION_SLUG ) ), '</a>' );

			if ( $this->_is_woocommerce() ) {
				$settings_url = add_query_arg( 'page', WOO_STORE_VACATION_SLUG, admin_url( 'admin.php' ) );
				/* translators: 1: Open anchor tag, 2: Close anchor tag. */
				$plugin_links[] = sprintf( _x( '%1$sSettings%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="%s" target="_self">', esc_url( $settings_url ) ), '</a>' );
			}

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Set the activation hook for a plugin.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function activation() {
			// Set up the admin notice to be displayed on activation.
			$settings_url = add_query_arg( 'page', WOO_STORE_VACATION_SLUG, admin_url( 'admin.php' ) );
			/* translators: 1: Dashicon, 2: Plugin name, 3: Open anchor tag, 4: Close anchor tag. */
			$welcome_notice = sprintf( esc_html_x( '%1$s Thanks for installing %2$s plugin! To get started, visit the %3$splugin’s settings page%4$s.', 'admin notice', 'woo-store-vacation' ), '<i class="dashicons dashicons-admin-settings" style="vertical-align:sub"></i>', sprintf( '<strong>%s</strong>', WOO_STORE_VACATION_NAME ), sprintf( '<a href="%s" target="_self">', esc_url( $settings_url ) ), '</a>' );
			set_transient( 'woo_store_vacation_welcome_notice', $welcome_notice, MINUTE_IN_SECONDS );
		}

		/**
		 * Set the deactivation hook for a plugin.
		 *
		 * @since   1.3.8
		 * @return  void
		 */
		public function deactivation() {
			delete_transient( 'woo_store_vacation_upsell' );
			delete_transient( 'woo_store_vacation_welcome_notice' );
		}

		/**
		 * Minifies the given CSS styles.
		 *
		 * @since    1.3.8
		 * @param    string $css    CSS styles.
		 * @return   void|string
		 */
		private function _minify_css( $css ) {
			// Bail early if we have no $css properties to trim and minify.
			if ( ! isset( $css ) || empty( $css ) ) {
				return;
			}

			$css = preg_replace(
				array(
					// Normalize whitespace.
					'/\s+/',
					// Remove spaces before and after comment.
					'/(\s+)(\/\*(.*?)\*\/)(\s+)/',
					// Remove comment blocks, everything between /* and */, unless.
					// preserved with /*! ... */ or /** ... */.
					'~/\*(?![\!|\*])(.*?)\*/~',
					// Remove ; before }.
					'/;(?=\s*})/',
					// Remove space after , : ; { } */ >.
					'/(,|:|;|\{|}|\*\/|>) /',
					// Remove space before , ; { } ( ) >.
					'/ (,|;|\{|}|\)|>)/',
					// Strips leading 0 on decimal values (converts 0.5px into .5px).
					'/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i',
					// Strips units if value is 0 (converts 0px to 0).
					'/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i',
					// Converts all zeros value into short-hand.
					'/0 0 0 0/',
					// Shortern 6-character hex color codes to 3-character where possible.
					'/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i',
					// Replace `(border|outline):none` with `(border|outline):0`.
					'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
					'#(background-position):0(?=[;\}])#si',
				),
				array(
					' ',
					'$2',
					'',
					'',
					'$1',
					'$1',
					'${1}.${2}${3}',
					'${1}0',
					'0',
					'#\1\2\3',
					'$1:0',
					'$1:0 0',
				),
				$css
			);
			$css = trim( $css );

			return wp_strip_all_tags( $css, false );
		}

		/**
		 * Determine whteher the current screen is displaying plugin’s settings page.
		 *
		 * @since    1.4.0
		 * @return   bool
		 */
		private function _is_plugin_page() {
			global $pagenow;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && WOO_STORE_VACATION_SLUG === $_GET['page'] ) {
				return true;
			}

			return false;
		}

		/**
		 * Query WooCommerce activation
		 *
		 * @since    1.3.8
		 * @return   bool
		 */
		private function _is_woocommerce() {
			// This statement prevents from producing fatal errors,
			// in case the WooCommerce plugin is not activated on the site.
			$woocommerce_plugin = apply_filters( 'woo_store_vacation_woocommerce_path', 'woocommerce/woocommerce.php' );
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$subsite_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$network_active_plugins = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );

			// Bail early in case the plugin is not activated on the website.
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ( empty( $subsite_active_plugins ) || ! in_array( $woocommerce_plugin, $subsite_active_plugins ) ) && ( empty( $network_active_plugins ) || ! array_key_exists( $woocommerce_plugin, $network_active_plugins ) ) ) {
				return false;
			}

			return true;
		}

	}
endif;

if ( ! function_exists( 'woo_store_vacation_init' ) ) :

	/**
	 * Returns the main instance of Woo_Store_Vacation to prevent the need to use globals.
	 *
	 * @return  object(class)   Woo_Store_Vacation::instance
	 */
	function woo_store_vacation_init() {
		return Woo_Store_Vacation::instance();
	}

	woo_store_vacation_init();
endif;
