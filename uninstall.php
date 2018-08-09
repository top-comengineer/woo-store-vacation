<?php
/**
 * Unistall Woo Store Vacation.
 *
 * @author      Mahdi Yazdani
 * @package     Woo Store Vacation
 * @since       1.0
 */
if (!defined( 'WP_UNINSTALL_PLUGIN')):
    exit();
endif;
$option_name = 'woo_store_vacation_options';
delete_option($option_name);
// For site options in Multisite
delete_site_option($option_name);