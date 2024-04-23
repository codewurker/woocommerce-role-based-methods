<?php
/**
 * Represents the view for the Role Based Methods Shipping Method Settings Tabs
 *
 *
 * @package   WC_Role_Methods
 * @author    Bryan Purcell <support@wpbackoffice.com>
 * @license   GPL-2.0+
 * @link      http://woothemes.com/woocommerce
 * @copyright 2015 WPBackOffice
 */
?>
<h1 class="role-methods-main-title">
	<?php _e('WooCommerce Role Based Methods', 'woocommerce-role-based-methods'); ?>
</h1>

<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
	<a href="<?php echo get_admin_url(); ?>admin.php?page=woocommerce-role-based-methods-settings&amp;tab=pay-role-gateways" class="nav-tab <?php echo ((isset($_GET['tab']) && $_GET['tab'] == 'pay-role-gateways') || !isset($_GET['tab'])) ? 'nav-tab-active' : ''; ?>"><?php _e('Payment Gateways', 'woocommerce-role-based-methods'); ?></a>
	<a href="<?php echo get_admin_url(); ?>admin.php?page=woocommerce-role-based-methods-settings&amp;tab=ship-role-methods" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'ship-role-methods') ? 'nav-tab-active' : ''; ?>"><?php _e('Shipping Methods', 'woocommerce-role-based-methods'); ?></a>
</h2>