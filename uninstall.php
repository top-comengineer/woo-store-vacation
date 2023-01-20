<?php
/**
 * Unistall Woo Store Vacation.
 * Fired when the plugin is uninstalled.
 *
 * @link       https://mypreview.one/woo-store-vacation
 * @author     MyPreview (Github: @mahdiyazdani, @gooklani, @mypreview)
 * @since      1.0.0
 *
 * @package    woo-store-vacation
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit; // If uninstall not called from WordPress, then exit.

// Reset the activation timestamp as the user already decided to delete the plugin.
delete_site_option( 'woo_store_vacation_activation_timestamp' );

/*
 * Only perform uninstall if WC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {
	delete_option( 'woo_store_vacation_options' );
	// For site options in Multisite.
	delete_site_option( 'woo_store_vacation_options' );
}
