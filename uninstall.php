<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WC_Role_Methods
 * @author    Bryan Purcell <support@wpbackoffice.com>
 * @license   GPL-2.0+
 * @link      http://woothemes.com/woocommerce
 * @copyright 2014 WPBackOffice
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if (is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	delete_option('OPTION_NAME');

	if ($blogs) {
		foreach($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			delete_option('woocommerce_payment_roles');
			delete_option('woocommerce_shipping_roles');
			delete_option('woocommerce_group_shipping_roles');
			delete_option('woocommerce_group_payment_roles');
		}
	}
}
else
{
	delete_option('woocommerce_payment_roles');
	delete_option('woocommerce_shipping_roles');
	delete_option('woocommerce_group_shipping_roles');
	delete_option('woocommerce_group_payment_roles');
}