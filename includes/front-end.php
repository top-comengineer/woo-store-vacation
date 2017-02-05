<?php
/**
 * Retrieve plugin option value(s).
 *
 * @author      Mahdi Yazdani
 * @package     Woo Store Vacation
 * @since       1.0.3
 */
/**
 * Display Vacation Message.
 *
 * @since 1.0.3
 */
if (!function_exists('woo_store_vacation_custom_notice')):
	function woo_store_vacation_custom_notice() {
		// Retrieve plugin option value(s)
		$woo_store_vacation = get_option( 'woo_store_vacation_option_name' );
		$notice_style = ( isset($woo_store_vacation['notice_style']) ) ? esc_attr( $woo_store_vacation['notice_style'] ) : '';
		$vacation_notice = ( isset($woo_store_vacation['vacation_notice']) ) ? esc_attr( $woo_store_vacation['vacation_notice'] ) : '';
		if(isset($vacation_notice, $notice_style) && !empty($vacation_notice) && !empty($notice_style)):
			wc_print_notice( $vacation_notice, $notice_style );
		endif;
	}
endif;
/**
 * Trigger Store Vacation Mode.
 *
 * @since 1.0.3
 */
if (!function_exists('woo_store_vacation_mode')):
	function woo_store_vacation_mode() {
		// Retrieve plugin option value(s)
		$woo_store_vacation = get_option( 'woo_store_vacation_option_name' );
		$enable_vacation_mode = ( isset($woo_store_vacation['enable_vacation_mode']) ) ? esc_attr( $woo_store_vacation['enable_vacation_mode'] ) : '';
		$disable_purchase = ( isset($woo_store_vacation['disable_purchase']) ) ? esc_attr( $woo_store_vacation['disable_purchase'] ) : '';
		$end_date = ( isset($woo_store_vacation['end_date']) ) ? esc_attr( strtotime($woo_store_vacation['end_date']) ) : '';
		$today = strtotime(current_time( 'd-m-Y', $gmt = 0 ));
		if(isset($enable_vacation_mode, $end_date) && !empty($enable_vacation_mode) && !empty($end_date) && $today < $end_date):
			if(isset($disable_purchase) && !empty($disable_purchase)):
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
			endif;
			add_action( 'woocommerce_archive_description', 'woo_store_vacation_custom_notice', 5 );
			add_action( 'woocommerce_before_single_product', 'woo_store_vacation_custom_notice', 10 );
			add_action( 'woocommerce_before_cart', 'woo_store_vacation_custom_notice', 5 );
			add_action( 'woocommerce_before_checkout_form', 'woo_store_vacation_custom_notice', 5 );
		endif;
	}
endif;
add_action('init', 'woo_store_vacation_mode', 10);