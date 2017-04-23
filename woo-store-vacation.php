<?php
/*
Plugin Name: 	Woo Store Vacation
Plugin URI:  	https://www.mypreview.one
Description: 	Put your WooCommerce store in vacation or pause mode with custom notice.
Version:     	1.0.4
Author:      	Mahdi Yazdani
Author URI:  	https://www.mypreview.one
Text Domain: 	woo-store-vacation
Domain Path: 	/languages
License:     	GPL2
License URI: 	https://www.gnu.org/licenses/gpl-2.0.html
 
Woo Store Vacation is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Woo Store Vacation is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Woo Store Vacation. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
// Prevent direct file access
if (!defined('ABSPATH')) exit;
define('WOO_STORE_VACATION_VERSION', '1.0');
if (!class_exists('Woo_Store_Vacation')):
	/**
	 * The Woo Store Vacation - Class
	 */
	final class Woo_Store_Vacation

	{
		private $file;
		private $dir;
		private $admin_assets_url;
		private $options;
		private static $_instance = null;
		/**
		 * Main Woo_Product_Suggest instance
		 *
		 * Ensures only one instance of Woo_Product_Suggest is loaded or can be loaded.
		 *
		 * @since 1.0.4
		 */
		public static function instance()

		{
			if (is_null(self::$_instance)) self::$_instance = new self();
			return self::$_instance;
		}
		/**
		 * Setup class.
		 *
		 * @since 1.0.4
		 */
		protected function __construct()

		{
			$this->file = plugin_basename(__FILE__);
			$this->dir = dirname($this->file);
			$this->admin_assets_url = esc_url(trailingslashit(plugins_url('admin/', $this->file)));
			add_action('init', array(
				$this,
				'textdomain'
			) , 10);
			add_action('admin_notices', array(
				$this,
				'activation'
			) , 10);
			add_action('admin_menu', array(
				$this,
				'add_plugin_page'
			) , 99);
			add_action('admin_init', array(
				$this,
				'register_settings'
			) , 10);
			add_action('admin_enqueue_scripts', array(
				$this,
				'admin_enqueue'
			) , 10);
			add_action('init', array(
				$this,
				'vacation_mode'
			) , 10);
		}
		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since 1.0.4
		 */
		protected function __clone()

		{
			_doing_it_wrong(__FUNCTION__, __('Cloning instances of this class is forbidden.', 'woo-store-vacation') , WOO_STORE_VACATION_VERSION);
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.4
		 */
		public function __wakeup()

		{
			_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'woo-store-vacation') , WOO_STORE_VACATION_VERSION);
		}
		/**
		 * Load languages file and text domains.
		 *
		 * @since 1.0.4
		 */
		public function textdomain()

		{
			$domain = 'woo-store-vacation';
			$locale = apply_filters('woo_store_vacation_textdoamin', get_locale() , $domain);
			load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
			load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
		}
		/**
		 * Query WooCommerce activation.
		 *
		 * @since 1.0.4
		 */
		public function activation()

		{
			if (!class_exists('woocommerce')):
				$message = esc_html__('Woo Store Vacation is enabled but not effective. It requires WooCommerce in order to work.', 'woo-product-suggest');
				printf('<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message);
			endif;
		}
		/**
		 * Create plugin options page.
		 *
		 * @since 1.0.4
		 */
		public function add_plugin_page() 

		{
			add_submenu_page(
				'woocommerce',
				__('Woo Store Vacation', 'woo-store-vacation'),
				__('Store Vacation', 'woo-store-vacation'),
				'manage_woocommerce',
				'woo-store-vacation',
				array($this, 'render_plugin_page')
			);
		}
		/**
		 * Render and display plugin options page.
		 *
		 * @since 1.0.4
		 */
		public function render_plugin_page() 

		{
			$this->options = get_option('woo_store_vacation_options'); ?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h1><?php esc_html_e('Woo Store Vacation', 'woo-store-vacation'); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<?php settings_errors(); ?>
						<!-- main content -->
						<div id="post-body-content">
							<form method="POST" id="woo-store-vacation" autocomplete="off" action="options.php">
								<div class="meta-box-sortables ui-sortable">
									<div class="postbox">
										<div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'woo-store-vacation'); ?>"><br></div>
										<!-- Toggle -->
										<h2 class="hndle">
											<span>
												<?php esc_attr_e('Global Settings', 'woo-store-vacation'); ?>
											</span>
										</h2>
										<div class="inside">
											<?php
												// Get settings fields
												settings_fields('woo_store_vacation_settings_fields');
												do_settings_sections('woo_store_vacation_settings_sections');
											?>
										</div>
										<!-- .inside -->
									</div>
									<!-- .postbox -->
								</div>
								<!-- .meta-box-sortables .ui-sortable -->
								<?php submit_button(); ?>
							</form>
						</div>
						<!-- post-body-content -->
						<!-- sidebar -->
						<div id="postbox-container-1" class="postbox-container">
							<div class="meta-box-sortables">
								<div class="postbox">
									<div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'woo-store-vacation'); ?>"><br></div>
									<!-- Toggle -->
									<h2 class="hndle">
										<span><?php esc_attr_e( 'Looking for a stylish theme?', 'woo-store-vacation'); ?>
										</span>
									</h2>
									<div class="inside">
										<p>
											<a href="https://wp.me/p8930x-8q" target="_blank">
												<img src="https://i.imgsafe.org/6a52b7b71e.jpg" style="max-width:100%;height:auto;" />
											</a>
										</p>
										<p>
											<?php 
												printf( esc_html__('In case you want to start an e-commerce project, the %s is one of the first things you need. The whole design of Hypermarket is ultra-responsive and Retina ready, offering you a site that can be accessed from any device, no matter the size or technology of its screen.' , 'woo-store-vacation'), '<a href="https://wp.me/p8930x-8q" target="_blank">Hypermarket WordPress Theme</a>' ); 
											?>
										</p>
									</div>
									<!-- .inside -->
								</div>
								<!-- .postbox -->
							</div>
							<!-- .meta-box-sortables -->
						</div>
						<!-- #postbox-container-1 .postbox-container -->
					</div>
					<!-- #post-body .metabox-holder .columns-2 -->
					<br class="clear">
				</div>
				<!-- #poststuff -->
			</div> <!-- .wrap -->
		<?php }
		/**
		 * Register plugin settings
		 *
		 * @since 1.0.4
		 */
		public function register_settings()

		{
			register_setting('woo_store_vacation_settings_fields', 'woo_store_vacation_options', array(
				$this,
				'sanitize'
			));
			add_settings_section('woo_store_vacation_settings_section', '', '', 'woo_store_vacation_settings_sections');
			add_settings_field('vacation_mode', esc_html__('Set Vacation Mode', 'woo-store-vacation') , array(
				$this,
				'vacation_mode_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('disable_purchase', esc_html__('Disable Purchase', 'woo-store-vacation') , array(
				$this,
				'disable_purchase_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('start_date', esc_html__('Start Date', 'woo-store-vacation') . ' <abbr class="required" title="required">*</abbr>', array(
				$this,
				'start_date_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('end_date', esc_html__('End Date', 'woo-store-vacation') . ' <abbr class="required" title="required">*</abbr>', array(
				$this,
				'end_date_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('text_color', esc_html__('Text Color', 'woo-store-vacation') . ' <abbr class="required" title="required">*</abbr>' , array(
				$this,
				'text_color_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('background_color', esc_html__('Background Color', 'woo-store-vacation') . ' <abbr class="required" title="required">*</abbr>' , array(
				$this,
				'background_color_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
			add_settings_field('vacation_notice', esc_html__('Vacation Notice', 'woo-store-vacation') . ' <abbr class="required" title="required">*</abbr>', array(
				$this,
				'vacation_notice_callback'
			) , 'woo_store_vacation_settings_sections', 'woo_store_vacation_settings_section');
		}
		/**
		 * Sanitization Callback.
		 *
		 * @since 1.0.4
		 */
		private function sanitize($input)

		{
			$sanitary_values = array();
			if (isset($input['vacation_mode'])):
				$sanitary_values['vacation_mode'] = $input['vacation_mode'];
			endif;
			if (isset($input['disable_purchase'])):
				$sanitary_values['disable_purchase'] = $input['disable_purchase'];
			endif;
			if (isset($input['start_date'])):
				$sanitary_values['start_date'] = sanitize_text_field($input['start_date']);
			endif;
			if (isset($input['end_date'])):
				$sanitary_values['end_date'] = sanitize_text_field($input['end_date']);
			endif;
			if (isset($input['text_color'])) :
               $sanitary_values['text_color'] = sanitize_hex_color($input['text_color']);
            endif;
			if (isset($input['background_color'])) :
               $sanitary_values['background_color'] = sanitize_hex_color($input['background_color']);
            endif;
			if (isset($input['vacation_notice'])):
				$sanitary_values['vacation_notice'] = esc_textarea($input['vacation_notice']);
			endif;
			return $sanitary_values;
		}
		/**
		 * Vacation Mode.
		 *
		 * @since 1.0.4
		 */
		public function vacation_mode_callback()

		{
			$vacation_mode = (isset($this->options['vacation_mode']) && $this->options['vacation_mode'] === 'vacation_mode') ? 'checked' : '';
			printf('<input type="checkbox" name="woo_store_vacation_options[vacation_mode]" id="vacation_mode" value="vacation_mode" %s /> <label for="vacation_mode"><em><small>' . esc_html__('Want to go vacation by closing my store publically.', 'woo-store-vacation') . '</small></em></label>', $vacation_mode);
		}
		/**
		 * Disable Purchase.
		 *
		 * @since 1.0.4
		 */
		public function disable_purchase_callback()

		{
			$disable_purchase = (isset($this->options['disable_purchase']) && $this->options['disable_purchase'] === 'disable_purchase') ? 'checked' : '';
			printf('<input type="checkbox" name="woo_store_vacation_options[disable_purchase]" id="disable_purchase" value="disable_purchase" %s /> <label for="disable_purchase"><em><small style="color:red;">' . esc_html__('Warning: With checking this setting customers won\'t be able to place an order.', 'woo-store-vacation') . '</small></em></label>', $disable_purchase);
		}
		/**
		 * Start Date.
		 *
		 * @since 1.0.4
		 */
		public function start_date_callback()

		{
			$today = strtotime(current_time('d-m-Y', $gmt = 0));
			$start_date = isset($this->options['start_date']) ? esc_attr($this->options['start_date']) : '';
			printf('<input class="regular-text woo-store-vacation-start-datepicker" type="text" name="woo_store_vacation_options[start_date]" id="start_date" value="%s" readonly="readonly" />', $start_date);
		}
		/**
		 * End Date.
		 *
		 * @since 1.0.4
		 */
		public function end_date_callback()

		{
			$today = strtotime(current_time('d-m-Y', $gmt = 0));
			$end_date = isset($this->options['end_date']) ? esc_attr($this->options['end_date']) : '';
			$invalid_date_style = '';
			$date_passed = '';
			if ($today > strtotime($end_date) && isset($end_date)):
				$invalid_date_style = 'style="border:1px solid red;"';
				$date_passed = '<small style="color:red;"><em> ' . esc_html__('The date has already passed.', 'woo-store-vacation') . '</em></small>';
			endif;
			printf('<input class="regular-text woo-store-vacation-end-datepicker" type="text" name="woo_store_vacation_options[end_date]" ' . $invalid_date_style . ' id="end_date" value="%s" readonly="readonly" />' . $date_passed, $end_date);
		}
		/**
		 * Text Color.
		 *
		 * @since 1.0.4
		 */
		public function text_color_callback() 

		{
            $text_color = isset($this->options['text_color']) ? esc_attr($this->options['text_color']) : '#ffffff';
			printf('<input class="woo-store-vacation-text-color-field" type="text" name="woo_store_vacation_options[text_color]" id="text_color" value="%s" />', $text_color);
        }
		/**
		 * Background Color.
		 *
		 * @since 1.0.4
		 */
		public function background_color_callback() 

		{
            $background_color = isset($this->options['background_color']) ? esc_attr($this->options['background_color']) : '#e2401c';
			printf('<input class="woo-store-vacation-background-color-field" type="text" name="woo_store_vacation_options[background_color]" id="background_color" value="%s" />', $background_color);
        }
        /**
		 * Notice Content.
		 *
		 * @since 1.0.4
		 */
        public function vacation_notice_callback() 

        {
			$notice_placeholder = esc_attr__('I am currently on vacation and products from my shop will be unavailable for next few days. Thank you for your patience and apologize for any inconvenience.', 'woo-store-vacation');
			$notice = isset($this->options['vacation_notice']) ? esc_attr($this->options['vacation_notice']) : '';
			printf('<textarea class="large-text" rows="5" name="woo_store_vacation_options[vacation_notice]" id="vacation_notice" placeholder="%s">%s</textarea>', $notice_placeholder, $notice);
		}
		/**
		 * Enqueue scripts and styles.
		 * 
		 * @since 1.0.4
		 */
		public function admin_enqueue()

		{
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('woo-store-vacation-script', $this->admin_assets_url . 'js/woo-store-vacation.js', array('jquery', 'jquery-ui-datepicker', 'wp-color-picker'), WOO_STORE_VACATION_VERSION, true );
		}
		/**
		 * Retrieve plugin option value(s).
		 *
		 * @since 1.0.4
		 */
		public function vacation_mode()

		{
			$options = get_option('woo_store_vacation_options');
			$vacation_mode = (isset($options['vacation_mode'])) ? esc_attr($options['vacation_mode']) : '';
			$disable_purchase = (isset($options['disable_purchase'])) ? esc_attr($options['disable_purchase']) : '';
			$start_date = (isset($options['start_date'])) ? esc_attr(strtotime($options['start_date'])) : '';
			$end_date = (isset($options['end_date'])) ? esc_attr(strtotime($options['end_date'])) : '';
			$today = strtotime(current_time('d-m-Y', $gmt = 0));
			if (isset($vacation_mode, $start_date, $end_date) && !empty($vacation_mode) && !empty($start_date) && !empty($end_date)):
				if ($today >= $start_date && $today < $end_date):
					if (isset($disable_purchase) && !empty($disable_purchase)):
						remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
						remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
						remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
						remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
					endif;
					add_action('woocommerce_archive_description', array(
						$this,
						'vacation_notice'
					) , 5);
					add_action('woocommerce_before_single_product', array(
						$this,
						'vacation_notice'
					) , 10);
					add_action('woocommerce_before_cart', array(
						$this,
						'vacation_notice'
					) , 5);
					add_action('woocommerce_before_checkout_form', array(
						$this,
						'vacation_notice'
					) , 5);
					add_action('wp_head', array(
						$this,
						'vacation_style'
					) , 999);
				endif;
			endif;
		}
		/**
		 * Display vacation custom notice.
		 *
		 * @since 1.0.4
		 */
		public function vacation_notice()

		{
			$options = get_option('woo_store_vacation_options');
			$vacation_notice = (isset($options['vacation_notice'])) ? wp_kses_post(nl2br($options['vacation_notice'])) : '';
			if (isset($vacation_notice) && !empty($vacation_notice)):
				echo '<div id="woo-store-vacation-wrapper">';
				wc_print_notice($vacation_notice, 'error');
				echo '</div>';
			endif;
		}
		/**
		 * Print inline stylesheet before closing </head> tag.
		 *
		 * @since 1.0.4
		 */
		public function vacation_style()

		{
			$options = get_option('woo_store_vacation_options');
			$text_color = (isset($options['text_color'])) ? sanitize_hex_color($options['text_color']) : '#ffffff';
			$background_color = (isset($options['background_color'])) ? sanitize_hex_color($options['background_color']) : '#e2401c';
			echo "<style id=\"woo-store-vacation-styles\" type=\"text/css\">
					#woo-store-vacation-wrapper .woocommerce-error {
						background-color: {$background_color} !important;
						color: {$text_color} !important;
						list-style: none;
						border-left: .6180469716em solid rgba(0, 0, 0, .15);
						border-radius: 2px;
						padding: 1em 1.618em;
						margin-left: 0;
						margin-top: 1.617924em;
						margin-bottom: 2.617924em;
					}
				  </style>";
		}
	}
endif;
/**
 * Returns the main instance of Woo_Store_Vacation to prevent the need to use globals.
 *
 * @since 1.0.4
 */
if (!function_exists('woo_store_vacation_initialization')):
	function woo_store_vacation_initialization()
	{
		return Woo_Store_Vacation::instance();
	}
	woo_store_vacation_initialization();
endif;