<?php
/**
 * The `Woo Store Vacation` bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * You can redistribute this plugin/software and/or modify it under
 * the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * @link                    https://www.mypreview.one
 * @since                   1.0.0
 * @package                 woo-store-vacation
 * @author                  MyPreview (Github: @mahdiyazdani, @gooklani, @mypreview)
 * @copyright               © 2015 - 2023 MyPreview. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name:             Woo Store Vacation
 * Plugin URI:              https://mypreview.one/woo-store-vacation
 * Description:             Pause your store operations for a set of fixed dates during your vacation and display a user-friendly notice on your shop.
 * Version:                 1.7.0
 * Author:                  MyPreview
 * Author URI:              https://mypreview.one/woo-store-vacation
 * Requires at least:       5.3
 * Requires PHP:            7.4
 * License:                 GPL-3.0
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             woo-store-vacation
 * Domain Path:             /languages
 * WC requires at least:    4.0
 * WC tested up to:         7.4
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Gets the path to a plugin file or directory.
 *
 * @see     https://codex.wordpress.org/Function_Reference/plugin_basename
 * @see     http://php.net/manual/en/language.constants.predefined.php
 */
$woo_store_vacation_plugin_data = get_file_data(
	__FILE__,
	array(
		'name'       => 'Plugin Name',
		'plugin_uri' => 'Plugin URI',
		'version'    => 'Version',
	),
	'plugin'
);
define( 'WOO_STORE_VACATION_NAME', $woo_store_vacation_plugin_data['name'] );
define( 'WOO_STORE_VACATION_URI', $woo_store_vacation_plugin_data['plugin_uri'] );
define( 'WOO_STORE_VACATION_VERSION', $woo_store_vacation_plugin_data['version'] );
define( 'WOO_STORE_VACATION_SLUG', 'woo-store-vacation' );
define( 'WOO_STORE_VACATION_FILE', __FILE__ );
define( 'WOO_STORE_VACATION_PLUGIN_BASENAME', plugin_basename( WOO_STORE_VACATION_FILE ) );
define( 'WOO_STORE_VACATION_DIR_URL', plugin_dir_url( WOO_STORE_VACATION_FILE ) );
define( 'WOO_STORE_VACATION_MIN_DIR', defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : trailingslashit( 'minified' ) );

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
		 * @since    1.0.0
		 * @var      object    $instance
		 */
		private static $instance = null;

		/**
		 * Date time format constant.
		 *
		 * @since    1.6.4
		 */
		const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

		/**
		 * Main `Woo_Store_Vacation` instance.
		 *
		 * Insures that only one instance of Woo_Store_Vacation exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since     1.0.0
		 * @return    null|Woo_Store_Vacation    The one true Woo_Store_Vacation
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Woo_Store_Vacation ) ) {
				self::$instance = new Woo_Store_Vacation();
				self::$instance->init();
			}

			return self::$instance;
		}


		/**
		 * Load actions.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		private function init() {
			add_action( 'init', array( self::instance(), 'textdomain' ) );
			add_action( 'admin_init', array( self::instance(), 'check_activation_timestamp' ) );
			add_action( 'admin_notices', array( self::instance(), 'admin_notices' ) );
			add_action( 'wp_ajax_woo_store_vacation_dismiss_upsell', array( self::instance(), 'dismiss_upsell' ) );
			add_action( 'wp_ajax_woo_store_vacation_dismiss_rate', array( self::instance(), 'dismiss_rate' ) );
			add_action( 'before_woocommerce_init', array( self::instance(), 'add_compatibility' ), 99 );
			add_action( 'admin_menu', array( self::instance(), 'add_submenu_page' ), 999 );
			add_action( 'admin_init', array( self::instance(), 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( self::instance(), 'admin_enqueue' ) );
			add_action( 'enqueue_block_editor_assets', array( self::instance(), 'editor_enqueue' ) );
			add_action( 'woocommerce_loaded', array( self::instance(), 'close_the_shop' ) );
			add_filter( 'admin_footer_text', array( self::instance(), 'ask_to_rate' ) );
			add_filter( 'plugin_action_links_' . WOO_STORE_VACATION_PLUGIN_BASENAME, array( self::instance(), 'add_action_links' ) );
			add_filter( 'plugin_row_meta', array( self::instance(), 'add_meta_links' ), 10, 2 );
			add_shortcode( 'woo_store_vacation', '__return_empty_string' );
			register_activation_hook( WOO_STORE_VACATION_FILE, array( self::instance(), 'activation' ) );
			register_deactivation_hook( WOO_STORE_VACATION_FILE, array( self::instance(), 'deactivation' ) );
		}

		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		protected function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Cloning instances of this class is forbidden.', 'clone', 'woo-store-vacation' ), esc_html( WOO_STORE_VACATION_VERSION ) );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html_x( 'Unserializing instances of this class is forbidden.', 'wakeup', 'woo-store-vacation' ), esc_html( WOO_STORE_VACATION_VERSION ) );
		}

		/**
		 * Load languages file and text domains.
		 * Define the internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function textdomain() {
			$domain = 'woo-store-vacation';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . "{$domain}/{$domain}-{$locale}.mo" );
			load_plugin_textdomain( $domain, false, dirname( WOO_STORE_VACATION_PLUGIN_BASENAME ) . '/languages/' );
		}

		/**
		 * Check date on admin initiation and add to admin notice if it was more than the time limit.
		 *
		 * @since     1.6.1
		 * @return    void
		 */
		public function check_activation_timestamp() {
			if ( get_transient( 'woo_store_vacation_rate' ) ) {
				return;
			}

			// If not installation date set, then add it.
			$option_name          = 'woo_store_vacation_activation_timestamp';
			$activation_timestamp = get_site_option( $option_name );

			if ( ! $activation_timestamp ) {
				add_site_option( $option_name, time() );
				$activation_timestamp = get_site_option( $option_name );
			}
		}

		/**
		 * Query WooCommerce activation.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function admin_notices() {
			// Query WooCommerce activation.
			if ( ! $this->is_woocommerce() ) {
				/* translators: 1: Dashicon, 2: Open anchor tag, 3: Close anchor tag. */
				$message = sprintf( esc_html_x( '%1$s requires the following plugin: %2$sWooCommerce%3$s', 'admin notice', 'woo-store-vacation' ), sprintf( '<i class="dashicons dashicons-admin-plugins"></i> <strong>%s</strong>', WOO_STORE_VACATION_NAME ), '<a href="https://wordpress.org/plugins/woocommerce" target="_blank" rel="noopener noreferrer nofollow"><em>', '</em></a>' );
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
					if ( ! get_transient( 'woo_store_vacation_upsell' ) && ( time() - (int) get_site_option( 'woo_store_vacation_activation_timestamp' ) ) > DAY_IN_SECONDS ) {
						/* translators: 1: Dashicon, 2: HTML symbol, 3: Open anchor tag, 4: Close anchor tag. */
						$message = sprintf( esc_html_x( '%1$s Automate your closings by defining unlimited number of vacation dates, times (hours), and weekdays without any manual effort needed. %2$s %3$sUpgrade to PRO%4$s', 'admin notice', 'woo-store-vacation' ), '<i class="dashicons dashicons-calendar-alt" style="vertical-align:sub"></i>', '&#8594;', sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow"><button class="button-primary">', esc_url( WOO_STORE_VACATION_URI ) ), '</button></a>' );
						printf( '<div id="%s-dismiss-upsell" class="notice notice-info woocommerce-message notice-alt is-dismissible"><p>%s</p></div>', esc_attr( WOO_STORE_VACATION_SLUG ), wp_kses_post( $message ) );
					} elseif ( ! get_transient( 'woo_store_vacation_rate' ) && ( time() - (int) get_site_option( 'woo_store_vacation_activation_timestamp' ) ) > WEEK_IN_SECONDS ) {
						/* translators: 1: HTML symbol, 2: Plugin name, 3: Activation duration, 4: HTML symbol, 5: Open anchor tag, 6: Close anchor tag. */
						$message = sprintf( esc_html_x( '%1$s You have been using the %2$s plugin for %3$s now, do you like it as much as we like you? %4$s %5$sRate 5-Stars%6$s', 'admin notice', 'woo-store-vacation' ), '&#9733;', esc_html( WOO_STORE_VACATION_NAME ), human_time_diff( (int) get_site_option( 'woo_store_vacation_activation_timestamp' ), time() ), '&#8594;', sprintf( '<a href="https://wordpress.org/support/plugin/%s/reviews?filter=5#new-post" class="button-primary" target="_blank" rel="noopener noreferrer nofollow">&#9733; ', esc_attr( WOO_STORE_VACATION_SLUG ) ), '</a>' );
						printf( '<div id="%s-dismiss-rate" class="notice notice-info is-dismissible"><p>%s</p></div>', esc_attr( WOO_STORE_VACATION_SLUG ), wp_kses_post( $message ) );
					}
				}
			}
		}

		/**
		 * AJAX dismiss up-sell admin notice.
		 *
		 * @since     1.3.8
		 * @return    void
		 */
		public function dismiss_upsell() {
			check_ajax_referer( WOO_STORE_VACATION_SLUG . '-dismiss' );
			set_transient( 'woo_store_vacation_upsell', true, MONTH_IN_SECONDS );
			wp_die();
		}

		/**
		 * AJAX dismiss ask-to-rate admin notice.
		 *
		 * @since     1.6.1
		 * @return    void
		 */
		public function dismiss_rate() {
			check_ajax_referer( WOO_STORE_VACATION_SLUG . '-dismiss' );
			set_transient( 'woo_store_vacation_rate', true, 3 * MONTH_IN_SECONDS );
			wp_die();
		}

		/**
		 * Declaring compatibility with HPOS.
		 *
		 * This plugin has nothing to do with "High-Performance Order Storage".
		 * However, the compatibility flag has been added to avoid WooCommerce declaring the plugin as "uncertain".
		 *
		 * @since     1.6.2
		 * @return    void
		 */
		public function add_compatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOO_STORE_VACATION_FILE, true );
			}
		}

		/**
		 * Create plugin options page.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function add_submenu_page() {
			add_submenu_page( 'woocommerce', _x( 'Woo Store Vacation', 'page title', 'woo-store-vacation' ), _x( 'Store Vacation', 'menu title', 'woo-store-vacation' ), 'manage_woocommerce', WOO_STORE_VACATION_SLUG, array( $this, 'render_plugin_page' ) );
		}

		/**
		 * Render and display plugin options page.
		 *
		 * @since     1.0.0
		 * @return    void
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
											<?php esc_html_e( 'Date time notes', 'woo-store-vacation' ); ?>
										</h2>
									</div>
									<div class="inside">
										<ul>
											<li>
												<strong>
													&#8505;
													<?php esc_html_e( 'Current time:', 'woo-store-vacation' ); ?>
												</strong>
												<?php echo esc_html( current_datetime()->format( self::DATE_TIME_FORMAT ) ); ?>
											</li>
											<li>
												<strong>
													&#8505;
													<?php echo esc_html_e( 'Time format:', 'woo-store-vacation' ); ?>
												</strong>
												<?php esc_html_e( 'The database will store a time of 00:00:00 by default.', 'woo-store-vacation' ); ?>
											</li>
											<li>
												<strong>
													&#8505;
													<?php echo esc_html_e( 'Timezone:', 'woo-store-vacation' ); ?>
												</strong>
												<?php
												/* translators: %s: WordPress timezone label/string. */
												printf( esc_html__( 'Date and time will be saved in "%s" timezone.', 'woo-store-vacation' ), esc_html( wp_timezone_string() ) );
												?>
											</li>
											<li>
												<strong>
													&#8505;
													<?php echo esc_html_e( 'Date range:', 'woo-store-vacation' ); ?>
												</strong>
												<?php esc_html_e( 'The date range is valid from midnight of the "Start Date" until the beginning of the "End Date" day.', 'woo-store-vacation' ); ?>
											</li>
										</ul>
									</div>
								</div>
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
												printf( esc_html_x( '%s Exclude products individually', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Display notice via shortcode or block', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
												?>
											</li>
											<li>
												<?php
												/* translators: %s: HTML Symbol. */
												printf( esc_html_x( '%s Localized calendar support', 'upsell', 'woo-store-vacation' ), '&#x2714;' );
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
		 * @since     1.0.0
		 * @return    void
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
		 * @since     1.0.0
		 * @param     array $input    An array of option's value.
		 * @return    array
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function vacation_mode_callback() {
			$value = $this->options['vacation_mode'] ?? null;
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function disable_purchase_callback() {
			$value = $this->options['disable_purchase'] ?? null;
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function start_date_callback() {
			$value = $this->options['start_date'] ?? null;
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function end_date_callback() {
			$today              = current_datetime()->format( self::DATE_TIME_FORMAT );
			$value              = $this->options['end_date'] ?? null;
			$end_date           = new DateTimeImmutable( $value, wp_timezone() );
			$end_date           = $end_date->setTime( 0, 0, 0 );
			$end_date_formatted = $end_date->format( self::DATE_TIME_FORMAT );
			$is_date_passed     = null;
			$invalid_date_style = null;

			if ( $today > $end_date_formatted && isset( $value ) && ! empty( $value ) ) {
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
		 * @since     1.3.0
		 * @return    void
		 */
		public function text_color_callback() {
			$value = $this->options['text_color'] ?? '#FFFFFF';
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
		 * @since     1.3.0
		 * @return    void
		 */
		public function background_color_callback() {
			$value = $this->options['background_color'] ?? '#E2401C';
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function btn_txt_callback() {
			$placeholder = _x( 'Contact me &#8594;', 'settings field placeholder', 'woo-store-vacation' );
			$value       = $this->options['btn_txt'] ?? null;
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function btn_url_callback() {
			$placeholder = _x( 'https://www.example.com', 'settings field placeholder', 'woo-store-vacation' );
			$value       = $this->options['btn_url'] ?? null;
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
		 * @since     1.0.0
		 * @return    void
		 */
		public function vacation_notice_callback() {
			$placeholder = _x( 'I am currently on vacation and products from my shop will be unavailable for next few days. Thank you for your patience and apologize for any inconvenience.', 'settings field placeholder', 'woo-store-vacation' );
			$value       = $this->options['vacation_notice'] ?? null;
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
		 * Enqueue scripts and styles for admin pages.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function admin_enqueue() {
			global $pagenow;

			// Make sure that the WooCommerce plugin is active.
			if ( $this->is_woocommerce() ) {
				wp_register_style( 'jquery-ui-style', trailingslashit( WC()->plugin_url() ) . 'assets/css/jquery-ui/jquery-ui.min.css', array(), WC_VERSION, 'screen' );
			}

			// Enqueue a script.
			wp_register_script( WOO_STORE_VACATION_SLUG, trailingslashit( WOO_STORE_VACATION_DIR_URL ) . 'assets/js/' . WOO_STORE_VACATION_MIN_DIR . 'admin.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker', 'wp-i18n' ), WOO_STORE_VACATION_VERSION, true );
			wp_register_script( WOO_STORE_VACATION_SLUG . '-upsell', trailingslashit( WOO_STORE_VACATION_DIR_URL ) . 'assets/js/' . WOO_STORE_VACATION_MIN_DIR . 'upsell.js', array( 'jquery' ), WOO_STORE_VACATION_VERSION, true );
			wp_localize_script( WOO_STORE_VACATION_SLUG . '-upsell', 'wsvVars', array( 'dismiss_nonce' => wp_create_nonce( WOO_STORE_VACATION_SLUG . '-dismiss' ) ) );

			if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && WOO_STORE_VACATION_SLUG === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( WOO_STORE_VACATION_SLUG );
			}

			if ( ! get_transient( 'woo_store_vacation_rate' ) || ! get_transient( 'woo_store_vacation_upsell' ) ) {
				wp_enqueue_script( WOO_STORE_VACATION_SLUG . '-upsell' );
			}
		}

		/**
		 * Enqueue static resources for the editor.
		 *
		 * @since     1.7.0
		 * @return    void
		 */
		public function editor_enqueue() {
			wp_enqueue_script( WOO_STORE_VACATION_SLUG, trailingslashit( WOO_STORE_VACATION_DIR_URL ) . 'assets/js/' . WOO_STORE_VACATION_MIN_DIR . 'block.js', array( 'react', 'wp-components', 'wp-element', 'wp-i18n' ), WOO_STORE_VACATION_VERSION, true );
		}

		/**
		 * Determine whether the shop should be closed or not!
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function close_the_shop() {
			// Bail early, in case the current request is for an administrative interface page.
			if ( is_admin() ) {
				return;
			}

			// Get today’s date and timestamp.
			$today            = current_datetime()->format( self::DATE_TIME_FORMAT );
			$timezone         = wp_timezone();
			$get_options      = (array) get_option( 'woo_store_vacation_options' );
			$vacation_mode    = $get_options['vacation_mode'] ?? null;
			$disable_purchase = $get_options['disable_purchase'] ?? null;
			$start_date       = $get_options['start_date'] ?? null;
			$end_date         = $get_options['end_date'] ?? null;

			if ( isset( $vacation_mode, $start_date, $end_date ) && wc_string_to_bool( $vacation_mode ) && ! empty( $start_date ) && ! empty( $end_date ) ) {
				// Parses a time string according to a specified format.
				$start_date           = new DateTimeImmutable( $start_date, $timezone );
				$start_date           = $start_date->setTime( 0, 0, 0 );
				$start_date_formatted = $start_date->format( self::DATE_TIME_FORMAT );
				$end_date             = new DateTimeImmutable( $end_date, $timezone );
				$end_date             = $end_date->setTime( 0, 0, 0 );
				$end_date_formatted   = $end_date->format( self::DATE_TIME_FORMAT );

				if ( $today >= $start_date_formatted && $today <= $end_date_formatted ) {
					if ( isset( $disable_purchase ) && wc_string_to_bool( $disable_purchase ) ) {
						// Make all products not purchasable.
						add_filter( 'woocommerce_is_purchasable', '__return_false', PHP_INT_MAX );
						add_filter( 'body_class', array( $this, 'body_classes' ) );

						/**
						 * Allow third-party plugin(s) to hook into this place and add their own functionality if needed.
						 *
						 * @since    1.6.2
						 */
						do_action( 'woo_store_vacation_shop_closed' );
					}

					remove_shortcode( 'woo_store_vacation' );
					add_action( 'woocommerce_before_shop_loop', array( self::instance(), 'vacation_notice' ), 5 );
					add_action( 'woocommerce_before_single_product', array( self::instance(), 'vacation_notice' ), 10 );
					add_action( 'woocommerce_before_cart', array( self::instance(), 'vacation_notice' ), 5 );
					add_action( 'woocommerce_before_checkout_form', array( self::instance(), 'vacation_notice' ), 5 );
					add_shortcode( 'woo_store_vacation', array( self::instance(), 'return_vacation_notice' ) );
					add_action( 'wp_print_styles', array( self::instance(), 'inline_css' ), 99 );
				}
			}
		}

		/**
		 * Adds and store a notice.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function vacation_notice() {
			$get_options = (array) get_option( 'woo_store_vacation_options' );
			$btn_txt     = $get_options['btn_txt'] ?? null;
			$btn_url     = $get_options['btn_url'] ?? null;
			$notice      = $get_options['vacation_notice'] ?? null;

			if ( ! isset( $notice ) || empty( $notice ) || ! function_exists( 'wc_print_notice' ) ) {
				return;
			}

			printf( '<div id="%s">', esc_attr( WOO_STORE_VACATION_SLUG ) );

			if ( empty( $btn_txt ) || empty( $btn_url ) || '#' === $btn_url ) {
				$message = wp_kses_post( nl2br( $notice ) );
			} else {
				$message = sprintf( '<a href="%1$s" class="%2$s__btn" target="_self">%3$s</a> <span class="%2$s__msg">%4$s</span>', esc_url( $btn_url ), sanitize_html_class( WOO_STORE_VACATION_SLUG ), esc_html( $btn_txt ), wp_kses_post( nl2br( $notice ) ) );
			}

			wc_print_notice( $message, apply_filters( 'woo_store_vacation_notice_type', 'notice' ) );

			echo '</div>';
		}

		/**
		 * Returns notice element when shortcode is found among the post content.
		 *
		 * @since     1.7.0
		 * @return    string
		 */
		public function return_vacation_notice(): string {
			// Flush (erase) the output buffer.
			if ( ob_get_length() ) {
				ob_flush();
			}

			// Start remembering everything that would normally be outputted,
			// but don't quite do anything with it yet.
			ob_start();

			// Output an arbitrary notice content if exists any.
			$this->vacation_notice();

			// Get current buffer contents and delete current output buffer.
			$output_string = ob_get_contents();
			ob_end_clean(); // Turn off output buffering.

			return $output_string;
		}

		/**
		 * Print inline stylesheet before closing </head> tag.
		 * Specific to `Store vacation` notice message.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function inline_css() {
			global $post;

			// Bailout, if any of the pages below are not displaying at the moment.
			if ( ! is_cart() && ! is_checkout() && ! is_product() && ! is_woocommerce() && ! has_shortcode( get_the_content( null, false, $post ), 'woo_store_vacation' ) ) {
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
					text-align:left;
					list-style:none;
					border:none;
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
					border:none;
					border-left:1px solid rgba(255,255,255,.25)!important;
					border-radius:0;
					box-shadow:none!important;
					text-decoration:none;
				}',
				esc_attr( WOO_STORE_VACATION_SLUG ),
				sanitize_hex_color( $background_color ),
				sanitize_hex_color( $text_color )
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<style id="%s" type="text/css">%s</style>%s', esc_attr( WOO_STORE_VACATION_SLUG ), $this->minify_css( $css ), PHP_EOL );
		}

		/**
		 * Adds custom classes to the array of body classes.
		 *
		 * @since   1.6.4
		 * @param   array $classes   Classes for the body element.
		 * @return  array
		 */
		public function body_classes( $classes ) {
			/**
			 * Append a class to the body element when shop is closed.
			 */
			$classes[] = 'woo-store-vacation-shop-closed';

			return $classes;
		}

		/**
		 * Filters the “Thank you” text displayed in the admin footer.
		 *
		 * @since     1.7.0
		 * @param     string $text    The content that will be printed.
		 * @return    string
		 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
		 */
		public function ask_to_rate( $text ) {
			global $pagenow;

			if ( 'admin.php' !== $pagenow ) {
				return $text;
			}

			if ( ! isset( $_GET['page'] ) || WOO_STORE_VACATION_SLUG !== $_GET['page'] ) {
				return $text;
			}

			return sprintf(
				/* translators: 1: Open paragraph tag, 2: Plugin name, 3: Five stars, 4: Close paragraph tag. */
				esc_html__( '%1$sIf you like %2$s please leave us a %3$s rating to help us spread the word!%4$s', 'woo-store-vacation' ),
				'<p class="alignleft">',
				sprintf( '<strong>%s</strong>', esc_html( WOO_STORE_VACATION_NAME ) ),
				'<a href="https://wordpress.org/support/plugin/' . esc_html( WOO_STORE_VACATION_SLUG ) . '/reviews?filter=5#new-post" target="_blank" rel="noopener noreferrer nofollow" aria-label="' . esc_attr__( 'five star', 'woo-store-vacation' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				'</p><style>#wpfooter{display:inline !important}</style>'
			);
		}

		/**
		 * Display additional links in plugins table page.
		 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
		 *
		 * @since     1.0.0
		 * @param     array $links    Plugin table/item action links.
		 * @return    array
		 */
		public function add_action_links( $links ) {
			$plugin_links = array();
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( esc_html_x( '%1$sGet PRO%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer nofollow" style="color:green;font-weight:bold;">&#127796; ', esc_url( WOO_STORE_VACATION_URI ) ), '</a>' );

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Add additional helpful links to the plugin’s metadata.
		 *
		 * @since     1.0.0
		 * @param     array  $links    An array of the plugin’s metadata.
		 * @param     string $file     Path to the plugin file relative to the plugins directory.
		 * @return    array
		 */
		public function add_meta_links( array $links, string $file ): array {
			if ( WOO_STORE_VACATION_PLUGIN_BASENAME !== $file ) {
				return $links;
			}

			$plugin_links = array();
			/* translators: 1: Open anchor tag, 2: Close anchor tag. */
			$plugin_links[] = sprintf( esc_html_x( '%1$sCommunity support%2$s', 'plugin link', 'woo-store-vacation' ), sprintf( '<a href="https://wordpress.org/support/plugin/%s" target="_blank" rel="noopener noreferrer nofollow">', esc_html( WOO_STORE_VACATION_SLUG ) ), '</a>' );

			if ( $this->is_woocommerce() ) {
				$settings_url = add_query_arg( 'page', WOO_STORE_VACATION_SLUG, admin_url( 'admin.php' ) );
				/* translators: 1: Open anchor tag, 2: Close anchor tag. */
				$plugin_links[] = sprintf( esc_html_x( '%1$sSettings%2$s', 'plugin settings page', 'woo-store-vacation' ), sprintf( '<a href="%s" style="font-weight:bold;">&#9881; ', esc_url( $settings_url ) ), '</a>' );
			}

			return array_merge( $links, $plugin_links );
		}

		/**
		 * Set the activation hook for a plugin.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function activation() {
			// Set up the admin notice to be displayed on activation.
			$settings_url = add_query_arg( 'page', WOO_STORE_VACATION_SLUG, admin_url( 'admin.php' ) );
			/* translators: 1: Dashicon, 2: Plugin name, 3: Open anchor tag, 4: Close anchor tag. */
			$welcome_notice = sprintf( esc_html_x( '%1$s Thanks for installing %2$s plugin! To get started, visit the %3$splugin’s settings page%4$s.', 'admin notice', 'woo-store-vacation' ), '<i class="dashicons dashicons-admin-settings"></i>', sprintf( '<strong>%s</strong>', WOO_STORE_VACATION_NAME ), sprintf( '<a href="%s" target="_self">', esc_url( $settings_url ) ), '</a>' );
			set_transient( 'woo_store_vacation_welcome_notice', $welcome_notice, MINUTE_IN_SECONDS );
		}

		/**
		 * Set the deactivation hook for a plugin.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public function deactivation() {
			delete_transient( 'woo_store_vacation_rate' );
			delete_transient( 'woo_store_vacation_upsell' );
			delete_transient( 'woo_store_vacation_welcome_notice' );
		}

		/**
		 * Minifies the given CSS styles.
		 *
		 * @since      1.3.8
		 * @param      string $css    CSS styles.
		 * @return     void|string
		 */
		private function minify_css( $css ) {
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
		 * Query WooCommerce activation
		 *
		 * @since     1.3.8
		 * @return    bool
		 */
		private function is_woocommerce() {
			// This statement prevents from producing fatal errors,
			// in case the WooCommerce plugin is not activated on the site.
			$woocommerce_plugin = apply_filters( 'woo_store_vacation_woocommerce_path', 'woocommerce/woocommerce.php' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.HookCommentWrongStyle
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
	 * Begins execution of the plugin.
	 * The main function responsible for returning the one true Woo_Store_Vacation
	 * Instance to functions everywhere.
	 *
	 * This function is meant to be used like any other global variable,
	 * except without needing to declare the global.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since     1.0.0
	 * @return    object|Woo_Store_Vacation    The one true Woo_Store_Vacation Instance.
	 */
	function woo_store_vacation_init() {
		return Woo_Store_Vacation::instance();
	}

	woo_store_vacation_init();
endif;
