<?php
/*
Plugin Name: ELEX WooCommerce Bulk Edit Products, Prices & Attributes (Basic)
Plugin URI: https://elextensions.com/plugin/elex-bulk-edit-products-prices-attributes-for-woocommerce-free-version/
Description: Bulk Edit Products, Prices & Attributes for WooCommerce allows you to edit products' prices and attributes in bulk.
Version: 1.5.2
WC requires at least: 2.6.0
WC tested up to: 10
Author: ELEXtensions
Author URI: https://elextensions.com/
Text Domain: eh_bulk_edit
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'ELEX_BEP_DIR' ) ) {
	define( 'ELEX_BEP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ELEX_BEP_TEMPLATE_PATH' ) ) {
	define( 'ELEX_BEP_TEMPLATE_PATH', ELEX_BEP_DIR . 'templates' );
}

if ( ! defined( 'ELEX_BULK_EDIT_MAIN_URL_PATH' ) ) {
	define( 'ELEX_BULK_EDIT_MAIN_URL_PATH', plugin_dir_url( __FILE__ ) );
}

require ELEX_BEP_DIR . 'vendor/wp-fluent/autoload.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
// review component
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/plugin.php';
}
require_once __DIR__ . '/review_and_troubleshoot_notify/review-and-troubleshoot-notify-class.php';
$data                      = get_plugin_data( __FILE__, false, false );
$data['name']              = $data['Name'];
$data['basename']          = plugin_basename( __FILE__ );
$data['documentation_url'] = 'https://elextensions.com/knowledge-base/set-bulk-edit-products-prices-attributes-for-woocommerce-plugin/';
$data['support_url']       = 'https://wordpress.org/support/plugin/elex-bulk-edit-products-prices-attributes-for-woocommerce-basic/';
$data['rating_url']        = 'https://elextensions.com/plugin/elex-bulk-edit-products-prices-attributes-for-woocommerce-free-version/#reviews';

new \Elex_Review_Components( $data );

register_activation_hook( __FILE__, function() {
	if ( class_exists( 'Eh_Bulk_Edit_Products_Premium' ) ) {
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die(esc_html_e( 'PREMIUM Version of this Plugin is Activated. Please deactivate the PREMIUM Version before activating BASIC.', 'eh_bulk_edit' ), '', array( 'back_link' => 1 ) );
	}

	if ( class_exists( 'Eh_Bulk_Edit_Products_Woocommerce' ) ) {
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die( esc_html_e( 'WooCommerce Version of this Plugin is Activated. Please deactivate the WooCommerce Version before activating BASIC.', 'eh_bulk_edit' ), '', array( 'back_link' => 1 ) );
	}
});
/**
 * Filters the list of active plugins.
 *
 * Allows developers to modify the list of active plugins before checking for a specific one.
 *
 * @since 1.0.0
 *
 * @param array $active_plugins Array of active plugin paths.
 * @return array Modified list of active plugins.
 */
if ( ! in_array( 'astra-sites/astra-sites.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	$url = '';
	/**
	 * Filter the timeout value for HTTP requests.
	 *
	 * Allows developers to modify the timeout value (default is 30 seconds).
	 *
	 * @since 1.0.0
	 *
	 * @param int    $timeout Timeout value in seconds.
	 * @param string $url     The request URL.
	 */
	apply_filters( 'http_request_timeout', 30, $url );
}
	// WooCommerce Active Check.
/**
 * Hook into 'init' to do something only if WooCommerce is active.
 *
 * @since 1.0.0
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) || ( is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) )) {

	if (!class_exists( 'Eh_Bulk_Edit_Products_Basic' )) {
		/**
		 *  Bulk Product Edit class
		 */
		class Eh_Bulk_Edit_Products_Basic {

			public function __construct() {
				add_filter(
					'plugin_action_links_' . plugin_basename( __FILE__ ),
					array(
						$this,
						'elex_bep_action_link',
					)
				); // to add settings, doc, etc options to plugins base
				$this->elex_bep_include_lib();
			}
			public function elex_bep_include_lib() {
				include_once 'includes/elex-class-bulk-edit-init.php';
			}
			public function elex_bep_action_link( $links ) {
				$plugin_links = array(
						'<a href="' . admin_url( 'admin.php?page=eh-bulk-edit-product-attr' ) . '">' . __( 'Bulk Edit Products', 'eh_bulk_edit' ) . '</a>',
						'settings' => sprintf(
							/* translators: %s - Premium */
							esc_html__( 'Settings %s', 'eh_bulk_edit' ),
							'<span style="vertical-align: super;color:green;font-size:12px;">[Premium]</span>'
						),						
						'<a href="https://elextensions.com/support/" target="_blank">' . __( 'Support', 'eh_bulk_edit' ) . '</a>',
					);
				return array_merge( $plugin_links, $links );
			}
		}
		new Eh_Bulk_Edit_Products_Basic();
	}	
} else {

	deactivate_plugins(plugin_basename(__FILE__));
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<?php
						$allowed_html = array(
							'strong' => array(),
						);
						echo wp_kses( __( '<strong>WooCommerce</strong> plugin must be active for <strong>ELEX Bulk Edit Products, Prices & Attributes for Woocommerce- Basic</strong> to work.', 'eh_bulk_edit' ), $allowed_html );
					?>
				</p>
			</div>
				<?php
		}
	);
}	
// High performance order tables compatibility.
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/** Load Plugin Text Domain. */
if ( !function_exists( 'elex_bep_load_plugin_textdomain_basic' ) ) {
	function elex_bep_load_plugin_textdomain_basic() {
		load_plugin_textdomain( 'eh_bulk_edit', false, basename( dirname( __FILE__ ) ) . '/lang/' );
	}
}
add_action( 'plugins_loaded', 'elex_bep_load_plugin_textdomain_basic' );
