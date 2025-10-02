<?php
/**
 * CCBill Payment Gateway Integration for WooCommerce
 * 
 * Integrates CCBill payment processing with WooCommerce
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CCBill Payment Gateway Class
 */
class WC_Gateway_CCBill extends WC_Payment_Gateway {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'ccbill';
        $this->icon               = apply_filters('woocommerce_ccbill_icon', '');
        $this->method_title       = __('CCBill', 'wc-gateway-ccbill');
        $this->method_description = __('Accept payments via CCBill payment gateway', 'wc-gateway-ccbill');
        $this->has_fields         = false;
        $this->supports           = array(
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_reactivation',
            'subscription_suspension',
            'subscription_amount_changes',
            'subscription_date_changes',
        );
        
        // Load settings
        $this->init_form_fields();
        $this->init_settings();
        
        // Define user set variables
        $this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');
        $this->account_number     = $this->get_option('account_number');
        $this->sub_account        = $this->get_option('sub_account');
        $this->form_name          = $this->get_option('form_name');
        $this->currency_code      = $this->get_option('currency_code');
        $this->testmode           = 'yes' === $this->get_option('testmode');
        $this->salt               = $this->get_option('salt');
        
        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc_gateway_ccbill', array($this, 'handle_webhook'));
    }
    
    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'wc-gateway-ccbill'),
                'type'    => 'checkbox',
                'label'   => __('Enable CCBill Payment Gateway', 'wc-gateway-ccbill'),
                'default' => 'no'
            ),
            'title' => array(
                'title'       => __('Title', 'wc-gateway-ccbill'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'wc-gateway-ccbill'),
                'default'     => __('Credit Card (CCBill)', 'wc-gateway-ccbill'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'wc-gateway-ccbill'),
                'type'        => 'textarea',
                'description' => __('Payment method description that the customer will see during checkout.', 'wc-gateway-ccbill'),
                'default'     => __('Pay securely via Credit Card through CCBill.', 'wc-gateway-ccbill'),
                'desc_tip'    => true,
            ),
            'account_number' => array(
                'title'       => __('Account Number', 'wc-gateway-ccbill'),
                'type'        => 'text',
                'description' => __('Your CCBill account number.', 'wc-gateway-ccbill'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'sub_account' => array(
                'title'       => __('Sub-Account', 'wc-gateway-ccbill'),
                'type'        => 'text',
                'description' => __('Your CCBill sub-account number.', 'wc-gateway-ccbill'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'form_name' => array(
                'title'       => __('Form Name', 'wc-gateway-ccbill'),
                'type'        => 'text',
                'description' => __('CCBill form name for payment processing.', 'wc-gateway-ccbill'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'currency_code' => array(
                'title'       => __('Currency Code', 'wc-gateway-ccbill'),
                'type'        => 'text',
                'description' => __('CCBill currency code (e.g., 840 for USD).', 'wc-gateway-ccbill'),
                'default'     => '840',
                'desc_tip'    => true,
            ),
            'salt' => array(
                'title'       => __('Salt/Security Key', 'wc-gateway-ccbill'),
                'type'        => 'password',
                'description' => __('Your CCBill salt key for generating secure hashes.', 'wc-gateway-ccbill'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'testmode' => array(
                'title'       => __('Test Mode', 'wc-gateway-ccbill'),
                'type'        => 'checkbox',
                'label'       => __('Enable Test Mode', 'wc-gateway-ccbill'),
                'default'     => 'yes',
                'description' => __('Place the payment gateway in test mode.', 'wc-gateway-ccbill'),
            ),
        );
    }
    
    /**
     * Process the payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        // Generate payment URL
        $payment_url = $this->get_payment_url($order);
        
        // Reduce stock levels
        wc_reduce_stock_levels($order_id);
        
        // Remove cart
        WC()->cart->empty_cart();
        
        // Return redirect
        return array(
            'result'   => 'success',
            'redirect' => $payment_url
        );
    }
    
    /**
     * Generate CCBill payment URL
     */
    private function get_payment_url($order) {
        $params = array(
            'clientAccnum'     => $this->account_number,
            'clientSubacc'     => $this->sub_account,
            'formName'         => $this->form_name,
            'currencyCode'     => $this->currency_code,
            'formPrice'        => $order->get_total(),
            'formPeriod'       => '30',
            'customer_fname'   => $order->get_billing_first_name(),
            'customer_lname'   => $order->get_billing_last_name(),
            'email'            => $order->get_billing_email(),
            'phone'            => $order->get_billing_phone(),
            'address1'         => $order->get_billing_address_1(),
            'address2'         => $order->get_billing_address_2(),
            'city'             => $order->get_billing_city(),
            'state'            => $order->get_billing_state(),
            'zipcode'          => $order->get_billing_postcode(),
            'country'          => $order->get_billing_country(),
            'order_id'         => $order->get_id(),
        );
        
        // Generate hash for security
        $hash_string = $params['formPrice'] . $params['formPeriod'] . $params['currencyCode'] . $this->salt;
        $params['formDigest'] = md5($hash_string);
        
        $base_url = $this->testmode 
            ? 'https://sandbox.ccbill.com/jpost/signup.cgi' 
            : 'https://bill.ccbill.com/jpost/signup.cgi';
        
        return add_query_arg($params, $base_url);
    }
    
    /**
     * Handle webhook from CCBill
     */
    public function handle_webhook() {
        $raw_post = file_get_contents('php://input');
        $data = json_decode($raw_post, true);
        
        if (!$data) {
            parse_str($raw_post, $data);
        }
        
        // Verify webhook authenticity
        if (!$this->verify_webhook($data)) {
            status_header(401);
            exit;
        }
        
        // Process webhook based on type
        $this->process_webhook($data);
        
        status_header(200);
        exit;
    }
    
    /**
     * Verify webhook signature
     */
    private function verify_webhook($data) {
        // Implement CCBill webhook verification
        // This depends on CCBill's specific webhook security mechanism
        return true;
    }
    
    /**
     * Process webhook data
     */
    private function process_webhook($data) {
        if (!isset($data['order_id'])) {
            return;
        }
        
        $order = wc_get_order($data['order_id']);
        
        if (!$order) {
            return;
        }
        
        // Handle different event types
        switch ($data['eventType']) {
            case 'NewSaleSuccess':
                $order->payment_complete($data['subscription_id']);
                $order->add_order_note(__('Payment completed via CCBill', 'wc-gateway-ccbill'));
                break;
                
            case 'NewSaleFailure':
                $order->update_status('failed', __('Payment failed via CCBill', 'wc-gateway-ccbill'));
                break;
                
            case 'Renewal':
                // Handle subscription renewal
                do_action('woocommerce_ccbill_renewal', $order, $data);
                break;
                
            case 'Cancellation':
                // Handle subscription cancellation
                do_action('woocommerce_ccbill_cancellation', $order, $data);
                break;
        }
    }
}

/**
 * Add the gateway to WooCommerce
 */
function add_ccbill_gateway($gateways) {
    $gateways[] = 'WC_Gateway_CCBill';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'add_ccbill_gateway');
