<?php
/* Authorize.net AIM Payment Gateway Class */
class SPYR_AuthorizeNet_AIM extends WC_Payment_Gateway {

  // Setup our Gateway's id, description and other values
  function __construct() {

  	// The global ID for this Payment method
  	$this->id = "genie_cashbox";

  	// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
  	$this->method_title = __( "Genie Cashbox", 'genie_cashbox' );

  	// The description for this Payment Gateway, shown on the actual Payment options page on the backend
  	$this->method_description = __( "Genie Cashbox Payment Gateway Plug-in for WooCommerce", 'genie_cashbox' );

  	// The title to be used for the vertical tabs that can be ordered top to bottom
  	$this->title = __( "Genie Cashbox", 'genie_cashbox' );

  	// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
  	$this->icon = null;

  	// Bool. Can be set to true if you want payment fields to show on the checkout
  	// if doing a direct integration, which we are doing in this case
  	$this->has_fields = true;

  	// Supports the default credit card form
  	$this->supports = array( 'default_credit_card_form' );

  	// This basically defines your settings which are then loaded with init_settings()
  	$this->init_form_fields();

  	// After init_settings() is called, you can get the settings and load them into variables, e.g:
  	// $this->title = $this->get_option( 'title' );
  	$this->init_settings();

  	// Turn these settings into variables we can use
  	foreach ( $this->settings as $setting_key => $value ) {
  		$this->$setting_key = $value;
  	}

  	// Lets check for SSL
  	add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );

  	// Save settings
  	if ( is_admin() ) {
  		// Versions over 2.0
  		// Save our administration options. Since we are not going to be doing anything special
  		// we have not defined 'process_admin_options' in this class so the method in the parent
  		// class will be used instead
  		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
  	}
  } // End __construct()


  // Build the administration fields for this specific Gateway
  public function init_form_fields() {
  	$this->form_fields = array(
  		'enabled' => array(
  			'title'		=> __( 'Enable / Disable', 'genie_cashbox' ),
  			'label'		=> __( 'Enable this payment gateway', 'genie_cashbox' ),
  			'type'		=> 'checkbox',
  			'default'	=> 'no',
  		),
  		'title' => array(
  			'title'		=> __( 'Title', 'genie_cashbox' ),
  			'type'		=> 'text',
  			'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'genie_cashbox' ),
  			'default'	=> __( 'Genie Cashbox', 'genie_cashbox' ),
  		),
  		'description' => array(
  			'title'		=> __( 'Description', 'genie_cashbox' ),
  			'type'		=> 'textarea',
  			'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'genie_cashbox' ),
  			'default'	=> __( 'Pay securely using your Genie Cashbox account.', 'genie_cashbox' ),
  			'css'		=> 'max-width:350px;'
  		),
  		'genie_login' => array(
  			'title'		=> __( 'Genie Login', 'genie_cashbox' ),
  			'type'		=> 'text',
  			'desc_tip'	=> __( 'This is the API Login provided by Genie Cashbox when you signed up for an account.', 'genie_cashbox' ),
  		),
  		'genie_passcode' => array(
  			'title'		=> __( 'Genie Passcode', 'genie_cashbox' ),
  			'type'		=> 'text',
  			'desc_tip'	=> __( 'This is the Transaction Key provided by Genie Cashbox when you signed up for an account.', 'genie_cashbox' ),
  		),
      'genie_number' => array(
  			'title'		=> __( 'Genie Number', 'genie_cashbox' ),
  			'type'		=> 'text',
  			'desc_tip'	=> __( 'This is the Genie Number provided by Genie Cashbox when you signed up for an account.', 'genie_cashbox' ),
  		// ),
      // 'genie_group_code' => array(
  		// 	'title'		=> __( 'Group Code', 'genie_cashbox' ),
  		// 	'type'		=> 'text',
  		// 	'desc_tip'	=> __( 'This is the Group Code provided by Genie Cashbox when you signed up for an account.', 'genie_cashbox' ),
  		)
  	);
  }


  // Submit payment and handle response
  public function process_payment( $order_id ) {
  	global $woocommerce;

  	// Get this Order's information so that we know
  	// who to charge and how much
  	$customer_order = new WC_Order( $order_id );

  	// Are we testing right now or is it a real transaction
  	// $environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';

  	// Decide which URL to post to
  	// $environment_url = ( "FALSE" == $environment )
  	// 				   ? 'https://secure.authorize.net/gateway/transact.dll'
  	// 				   : 'https://test.authorize.net/gateway/transact.dll';

  	// This is where the fun stuff begins
  	$payload = array(
  		// Genie Cashbox Credentials and API Info
      //<authenication>?
  		"login"               => $this->genie_login,
  		"passCode"           	=> $this->genie_passcode,
  		"action"            	=> "requestpayment",

      //<requestPaymentData>?

      "fundingAccount"     	=> str_replace( array(' ', '-' ), '', $_POST['spyr_authorizenet_aim-card-number'] ),
      "toNumber"           	=> $this->genie_number,

  		// Order total
  		"amount"             	=> $customer_order->order_total,
      "referenceCode"      	=> $customer_order->id,
      "orderNumber"        	=> $customer_order->get_order_number(),
      "description"        	=> "" //$customer_order->order_total,

  		// // Billing Information
  		// "x_first_name"         	=> $customer_order->billing_first_name,
  		// "x_last_name"          	=> $customer_order->billing_last_name,
  		// "x_address"            	=> $customer_order->billing_address_1,
  		// "x_city"              	=> $customer_order->billing_city,
  		// "x_state"              	=> $customer_order->billing_state,
  		// "x_zip"                	=> $customer_order->billing_postcode,
  		// "x_country"            	=> $customer_order->billing_country,
  		// "x_phone"              	=> $customer_order->billing_phone,
  		// "x_email"              	=> $customer_order->billing_email,
      //
  		// // Shipping Information
  		// "x_ship_to_first_name" 	=> $customer_order->shipping_first_name,
  		// "x_ship_to_last_name"  	=> $customer_order->shipping_last_name,
  		// "x_ship_to_company"    	=> $customer_order->shipping_company,
  		// "x_ship_to_address"    	=> $customer_order->shipping_address_1,
  		// "x_ship_to_city"       	=> $customer_order->shipping_city,
  		// "x_ship_to_country"    	=> $customer_order->shipping_country,
  		// "x_ship_to_state"      	=> $customer_order->shipping_state,
  		// "x_ship_to_zip"        	=> $customer_order->shipping_postcode,
  	);

    trace("Query: " + http_build_queyr($payload));

  	// // Send this payload to Genie Cashbox for processing
  	// $response = wp_remote_post( $environment_url, array(
  	// 	'method'    => 'POST',
  	// 	'body'      => http_build_query( $payload ),
  	// 	'timeout'   => 90,
  	// 	'sslverify' => false,
  	// ) );
    //
  	// if ( is_wp_error( $response ) )
  	// 	throw new Exception( __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'genie_cashbox' ) );
    //
  	// if ( empty( $response['body'] ) )
  	// 	throw new Exception( __( 'Genie Cashbox\'s Response was empty.', 'genie_cashbox' ) );
    //
  	// // Retrieve the body's resopnse if no errors found
  	// $response_body = wp_remote_retrieve_body( $response );
    //
  	// // Parse the response into something we can read
  	// foreach ( preg_split( "/\r?\n/", $response_body ) as $line ) {
  	// 	$resp = explode( "|", $line );
  	// }
    //
  	// // Get the values we need
  	// $r['response_code']             = $resp[0];
  	// $r['response_sub_code']         = $resp[1];
  	// $r['response_reason_code']      = $resp[2];
  	// $r['response_reason_text']      = $resp[3];
    //
  	// // Test the code to know if the transaction went through or not.
  	// // 1 or 4 means the transaction was a success
  	// if ( ( $r['response_code'] == 1 ) || ( $r['response_code'] == 4 ) ) {
  	// 	// Payment has been successful
  	// 	$customer_order->add_order_note( __( 'Genie Cashbox payment completed.', 'genie_cashbox' ) );
    //
  	// 	// Mark order as Paid
  	// 	$customer_order->payment_complete();
    //
  	// 	// Empty the cart (Very important step)
  	// 	$woocommerce->cart->empty_cart();
    //
  	// 	// Redirect to thank you page
  	// 	return array(
  	// 		'result'   => 'success',
  	// 		'redirect' => $this->get_return_url( $customer_order ),
  	// 	);
  	// } else {
  	// 	// Transaction was not succesful
  	// 	// Add notice to the cart
  	// 	wc_add_notice( $r['response_reason_text'], 'error' );
  	// 	// Add note to the order for your reference
  	// 	$customer_order->add_order_note( 'Error: '. $r['response_reason_text'] );
  	// }

  }
