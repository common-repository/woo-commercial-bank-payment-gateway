<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: WooCommerce Commercial Bank Payment Gateway
Plugin URI: http://www.redevoke.com/services/payment-gateways/commercial-bank-payment-gateway-integration
Description: Commercial Bank Payment Gateway from RedEvoke Solutions.
Version: 1.0
Author: RedEvoke Solutions
Author URI: http://www.redevoke.com
*/

add_action('plugins_loaded', 'woocommerce_re_cb_commercial_payment_gateway', 0);

function woocommerce_re_cb_commercial_payment_gateway(){

	if(!class_exists('WC_Payment_Gateway')) return;

	class WC_ReCbCommercialIpg extends WC_Payment_Gateway{

		public function __construct(){
    	
			$plugin_dir 		= plugin_dir_url(__FILE__);
			$this->id 			= 'recommercialipg';	  
			$this->icon 		= $plugin_dir . 'commercial_bank_payment_gateway_logo_redevoke_solutions.png';
			$this->method_title = __( "Commercial IPG", 'recommercialipg' );
			$this->has_fields 	= false;

			$this->init_form_fields();
			$this->init_settings();

		  	$this->title 				= $this -> settings['title'];
		  	$this->description 			= $this -> settings['description'];
		  	$this->vpc_Merchant 		= $this -> settings['vpc_Merchant'];
		  	$this->vpc_AccessCode 		= $this -> settings['vpc_AccessCode'];  	  
		  	$this->vpc_SecureHash 		= $this -> settings['vpc_SecureHash'];	  
		  	$this->checkout_msg			= $this -> settings['checkout_msg'];	
		  	$this->party				= "PHP VPC 3-Party";

		  	$this->contact_link			= 'http://www.redevoke.com/contact-us?plugin=wordpress_commercial_bank_ipg';

			$this->msg['message'] 	= "";
			$this->msg['class'] 		= "";

			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
				add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array( &$this, 'process_admin_options' ) );
			} else {
				add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
			}
			add_action('woocommerce_receipt_'.$this->id, array(&$this, 'receipt_page'));
	   	}

   	  	function init_form_fields(){
 
		   	$this -> form_fields = array(
				'enabled' => array(
					'title' 		=> __('Enable/Disable', 'recommercialipg'),
					'type' 			=> 'checkbox',
					'label' 		=> __('Enable Commercial Bank Payment Module.', 'recommercialipg'),
					'default' 		=> 'no'),
					
				'title' => array(
					'title' 		=> __('Title:', 'recommercialipg'),
					'type'			=> 'text',
					'description' 	=> __('This controls the title which the user sees during checkout.', 'recommercialipg'),
					'default' 		=> __('Visa / Master ( Commercial IPG )', 'recommercialipg')),
				
				'description'=> array(
                    'title' 		=> __('Description:', 'recommercialipg'),
                    'type'			=> 'textarea',
                    'description' 	=> __('This controls the description which the user sees during checkout.', 'recommercialipg'),
                    'default' 		=> __('Pay using Visa or Master card ( Commercial IPG )', 'recommercialipg')),
					
				'vpc_Merchant' => array(
					'title' 		=> __('Merchant ID:', 'recommercialipg'),
					'type'			=> 'text',
					'description' 	=> __('Merchant ID given by Commercial bank.', 'recommercialipg'),
					'default' 		=> __('', 'recommercialipg')),
				
				'vpc_AccessCode' => array(
					'title' 		=> __('Merchant Access Code:', 'recommercialipg'),
					'type'			=> 'text',
					'description' 	=> __('Access Code given by Commercial bank.', 'recommercialipg'),
					'default' 		=> __('', 'recommercialipg')),
					
				'vpc_SecureHash' => array(
					'title' 		=> __('Hash Key:', 'recommercialipg'),
					'type'			=> 'text',
					'description' 	=> __('Hash Key given by Commercial bank.', 'recommercialipg'),
					'default' 		=> __('', 'recommercialipg')),
								
				'checkout_msg' => array(
					'title' 		=> __('Checkout Message:', 'recommercialipg'),
					'type'			=> 'textarea',
					'description' 	=> __('Message display when checkout'),
					'default' 		=> __('Thank you for your order, please click the button below to pay with the secured Commercial Bank payment gateway.', 'recommercialipg')),
			);
		}

		public function admin_options(){

			$plugin_dir 		= plugin_dir_url(__FILE__);
			echo '<h3>'.__('Commercial Bank Payment Gateway', 'recommercialipg').'</h3>';
			echo '<p>'.__('Commercial Bank Payment Gateway allows you to accept payments from customers using Visa / MasterCard').'</p>';
			echo '<a href="'.$this->contact_link.'" ><img src="'.$plugin_dir.'/images/cover_admin.jpg" style="max-width:100%;min-width:100%" ></a>';
			echo '<table class="form-table">';        
					$this->generate_settings_html();
			echo '</table>'; 
			echo '<h4 style="text-align:center;"> Payment gateway developed by <a href="http://www.redevoke.com/">RedEvoke Solutions</a></h4>';
		}

		function payment_fields(){	
			if($this -> description) echo wpautop(wptexturize($this -> description));
		}

		function receipt_page($order){      

			global $woocommerce;
			$order_details = new WC_Order($order);
			echo '<br>'.$this->checkout_msg.'</b>';
			echo $this->generate_payment_gateway_form($order);			
		}

		public function generate_payment_gateway_form($order_id){
			
			$plugin_dir 		= plugin_dir_url(__FILE__);
			wc_add_notice('Please contact <a href="'.$this->contact_link.'">RedEvoke Solutions</a> to get the complete version of the plugin', 'success');
			echo '<a href="'.$this->contact_link.'" ><img src="'.$plugin_dir.'/images/cover_checkout.jpg" style="max-width:100%;min-width:100%" ></a> ';
	        return;
		}

		function process_payment($order_id){
		
			$order = new WC_Order($order_id);
			return array('result' => 'success', 'redirect' => add_query_arg('order',           
			   $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
			);
		}

		function get_pages($title = false, $indent = true) {
	        $wp_pages = get_pages('sort_column=menu_order');
	        $page_list = array();
	        if ($title) $page_list[] = $title;
	        foreach ($wp_pages as $page) {
	            $prefix = '';            
	            if ($indent) {
	                $has_parent = $page->post_parent;
	                while($has_parent) {
	                    $prefix .=  ' - ';
	                    $next_page = get_page($has_parent);
	                    $has_parent = $next_page->post_parent;
	                }
	            }            
	            $page_list[$page->ID] = $prefix . $page->post_title;
	        }
	        return $page_list;
	    }
	}

	function woocommerce_add_re_cb_commercial_payment_gateway($methods) {
		$methods[] = 'WC_ReCbCommercialIpg';
		return $methods;
	}
	 	
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_re_cb_commercial_payment_gateway' );
}