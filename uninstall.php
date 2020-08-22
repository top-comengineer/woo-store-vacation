<?php
/**
 * Unistall Woo Store Vacation.
 * Fired when the plugin is uninstalled.
 *
 * @package     Woo Store Vacation
 * @since       1.3.8
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_transient( 'woo_store_vacation_upsell' );
$woo_store_vacation_option_name = 'woo_store_vacation_options';
delete_option( $woo_store_vacation_option_name );
// For site options in Multisite.
delete_site_option( $woo_store_vacation_option_name );
