<?php
/*
Plugin Name: Genie Cash Box - WooCommerce Gateway
Plugin URI: http://www.geniecashbox.com/
Description: Extends WooCommerce by Adding the Genie Cash Box Gateway.
Version: 1
Author: John Lane
Author URI: http://www.giftedowlstudios.com/
*/

// Include our Gateway Class and Register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'genie_cashbox_gateway_init', 0 );
function genie_cashbox_gateway_init() {
  // If the parent WC_Payment_Gateway class doesn't exist
  // it means WooCommerce is not installed on the site
  // so do nothing
  if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

  // If we made it this far, then include our Gateway Class
  include_once( 'woocommerce-genie-cashbox.php' );

  // Now that we have successfully included our class,
  // Lets add it too WooCommerce
  add_filter( 'woocommerce_payment_gateways', 'add_genie_cashbox_gateway' );
  function add_genie_cashbox_gateway( $methods ) {
    $methods[] = 'Genie_Cashbox';
    return $methods;
  }
}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'genie_cashbox_gateway_links' );
function genie_cashbox_gateway_links( $links ) {
  $plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'genie_cashbox_gateway' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );
}
