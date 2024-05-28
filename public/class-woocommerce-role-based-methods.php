<?php
/**
 * WooCommerce Role Based Methods
 *
 * @package   WC_Role_Methods
 * @author    Bryan Purcell <support@wpbackoffice.com>
 * @license   GPL-2.0+
 * @link      http://woothemes.com/woocommerce
 * @copyright 2014 WPBackOffice
 */

/**
 * Main Role Based Methods class
 *
 * @package WC_Role_Methods
 * @author  WPBackOffice <support@wpbackoffice.com>
 */
class WC_Role_Methods {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const VERSION = '2.0.7';
	/**
	 * Instance of this class.
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	protected static $instance = null;
	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    2.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'woocommerce-role-based-methods';

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		if ( is_woocommerce_active() ) {
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'get_available_payment_gateways' ) );

			// General Options.
			$this->options = get_option( 'woocommerce_role_methods_options' );

			$this->shipping_options = get_option( 'woocommerce_shipping_roles' );
			$this->payment_options  = get_option( 'woocommerce_payment_roles' );

			// Set up Shipping group options.
			$this->allowed_payment_groups  = get_option( 'woocommerce_group_payment_roles' );
			$this->allowed_shipping_groups = get_option( 'woocommerce_group_shipping_roles' );

			// Check for 2.1.
			if ( function_exists( 'WC' ) ) {
				add_filter( 'woocommerce_package_rates', array( $this, 'get_available_shipping_methods' ), 9, 2 );
			} else {
				add_filter( 'woocommerce_available_shipping_methods', array(
					$this,
					'get_available_shipping_methods'
				) );
			}
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object    A single instance of this class.
	 * @since 2.0.0
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Return the slug.
	 *
	 * @return Plugin slug variable.
	 * @since 2.0.0
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get a list of all shipping methods globally available for use.
	 *
	 * @param array $rates shipping rates for the package.
	 *
	 * @return array $avail_methods only shipping methods deemed permitted by the Role-Based Plugin
	 */
	public function get_available_shipping_methods( $rates ) {
		$current_user_roles = $this->get_user_roles();

		unset( $avail_methods );
		$avail_methods = array();

		foreach ( $rates as $id => $rate ) {
			if ( 'table_rate' === $rate->method_id ) {
				$rate_id = implode(
					':',
					array(
						$rate->method_id,
						$rate->instance_id,
					)
				);
			} elseif ( 'flexible_shipping' === $rate->method_id ) {
				$rate_id = implode(
					':',
					array(
						$rate->method_id,
						$rate->instance_id,
					)
				);
			} elseif ( 'wbs' === $rate->method_id ) {
				// WBS features it's own logic for handling when a method is shown
				// so we just let it through.
				$avail_methods[ $id ] = $rate;
			} elseif ( method_exists( $rate, 'get_instance_id' ) ) {
				$rate_id = implode(
					':',
					array(
						$rate->get_method_id(),
						$rate->get_instance_id(),
					)
				);
			} else {
				$rate_id = $id;
			}

			foreach ( $current_user_roles as $user_role ) {
				if ( $this->check_rolea_methods( $user_role, $rate_id ) ) {
					$avail_methods[ $id ] = $rate;
				}
			}
		}

		return $avail_methods;
	}

	/**
	 * Accept a user role and a shipping method, return true or false depending on whether it's allowed.
	 *
	 * @param string $the_role The current user role as-per the logged in user.
	 * @param string $rate_id ID of the shipping method to be checked.
	 *
	 * @return bool true if allowed, false if not allowed
	 */
	public function check_rolea_methods( $the_role, $rate_id ) {
		$rate_id = apply_filters( 'wc_role_based_rate_id', $rate_id );

		if ( ! $this->shipping_options || ! isset( $this->shipping_options[ $the_role ] ) ) {
			return true;
		}

		$role_options = $this->shipping_options[$the_role];

		// Check if user is in one of the allowed groups, but only if groups plugin is installed.
		$active_in_groups = false;
		if ( function_exists( '_groups_get_tablename' ) && $this->allowed_shipping_groups ) {
			foreach ( $this->allowed_shipping_groups as $group_id => $group_allowed_methods ) {
				if ( Groups_User_Group::read( get_current_user_id(), $group_id ) && isset( $group_allowed_methods[ $rate_id ] ) && 'on' == $group_allowed_methods[ $rate_id ] ) {
					$active_in_groups = true;
				}
			}
		}

		if ( ( isset( $role_options[ $rate_id ] ) && 'on' === $role_options[ $rate_id ] ) ) {
			$active_in_roles = true;
		} else {
			$active_in_roles = false;
		}

		// Guests aren't in groups, so only check the role settings for guests.
		if ( 'Guest' === $the_role && $active_in_roles ) {
			return true;
		}

		// Check the operator - either AND or OR.
		if ( $this->using_ship_role_based_groups() && isset( $this->options['shipping_operator'] ) ) {
			if ( 'and' === $this->options['shipping_operator'] ) {
				return $active_in_groups && $active_in_roles;
			} else {
				return $active_in_groups || $active_in_roles;
			}
		} else {
			return $active_in_roles;
		}
	}

	/**
	 * Checks user's roles against payment gateways
	 *
	 * @param string $current_user_role The current user role as-per the logged in user.
	 * @param string $gateway_id ID of the payment gateway.
	 */
	public function check_rolea( $current_user_role, $gateway_id ) {
		global $current_user;

		// Check if user is in one of the allowed groups, but only if groups plugin is installed.
		$active_in_groups = false;

		if ( function_exists( '_groups_get_tablename' ) && $this->allowed_payment_groups ) {
			foreach ( $this->allowed_payment_groups as $group_id => $group_allowed_gateways ) {
				if ( Groups_User_Group::read( get_current_user_id(), $group_id ) && isset( $group_allowed_gateways[ $gateway_id ] ) && 'on' == $group_allowed_gateways[ $gateway_id ] ) {
					$active_in_groups = true;
				}
			}
		}

		$active_in_roles = false;
		if (
			(
				isset( $this->payment_options[ $current_user_role ][ $gateway_id ] ) &&
				'on' == $this->payment_options[ $current_user_role ][ $gateway_id ]
			) ||
			false == $this->payment_options
		) {
			$active_in_roles = true;
		} elseif ( ! is_user_logged_in() && isset( $this->payment_options['Guest'][ $gateway_id ] ) && 'on' == $this->payment_options['Guest'][ $gateway_id ] ) {
			$active_in_roles = true;
		} else {
			$active_in_roles = false;
		}

		// Guests aren't in groups, so only check the role settings for guests.
		if ( 'Guest' == $current_user_role && $active_in_roles ) {
			return true;
		}

		// Check the operator - either AND or OR.
		if ( $this->using_pay_role_based_groups() && isset( $this->options['payment_operator'] ) ) {
			if ( 'and' === $this->options['payment_operator'] ) {
				return $active_in_groups && $active_in_roles;
			} else {
				return $active_in_groups || $active_in_roles;
			}
		} else {
			return $active_in_roles;
		}
	}

	/**
	 * Gets any and all roles for the current user
	 *
	 * @return array roles of the current user
	 */
	public function get_user_roles() {
		global $current_user;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$the_roles          = $wp_roles->roles;
		$current_user_roles = array();

		if ( is_user_logged_in() ) {
			$user = new WP_User( $current_user->ID );
			if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
				foreach ( $user->roles as $role ) {
					$current_user_roles[] = strtolower( $the_roles[ $role ]['name'] );
				}
			}
		} else {
			$current_user_roles[] = 'Guest';
		}

		return $current_user_roles;
	}

	/**
	 * Get a list of all gateway globally available for use.
	 *
	 * @param array $gateways loop through all enabled payment gateways, but remove any gateways that are not permitted through Role-Based Methods.
	 *
	 * @return array Array of permitted gateways
	 */
	public function get_available_payment_gateways( $gateways ) {
		$current_user_roles = $this->get_user_roles();

		$avail_gateways = array();
		foreach ( $gateways as $gateway ) {
			foreach ( $current_user_roles as $user_role ) {
				if ( $this->check_rolea( $user_role, $gateway->id ) ) {
					$avail_gateways[ $gateway->id ] = $gateway;
				}
			}
		}

		return $avail_gateways;
	}

	/**
	 * Check if we're in bewteen woo versions
	 *
	 * @param float $start_version minimum version.
	 * @param float $end_version maximum version.
	 *
	 * @deprecated
	 */
	private function is_version_between( $start_version, $end_version ) {
		if ( version_compare( WOOCOMMERCE_VERSION, $start_version, '>=' ) && version_compare( WOOCOMMERCE_VERSION, $end_version, '<' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if using shipping groups
	 *
	 * @return boolean
	 */
	private function using_ship_role_based_groups() {
		if ( isset( $this->options['ship-groups-enable'] ) && 'Yes' == $this->options['ship-groups-enable'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if using pay role based groups
	 *
	 * @return boolean
	 */
	private function using_pay_role_based_groups() {
		if ( isset( $this->options['pay-groups-enable'] ) && 'Yes' == $this->options['pay-groups-enable'] ) {
			return true;
		} else {
			return false;
		}
	}
}
