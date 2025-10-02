<?php
/**
 * Webhook Handler for CCBill and Subscription Events
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RDE_Webhook_Handler {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', array($this, 'register_webhook_routes'));
    }
    
    /**
     * Register webhook REST API routes
     */
    public function register_webhook_routes() {
        // CCBill webhook
        register_rest_route('rde/v1', '/webhook/ccbill', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_ccbill_webhook'),
            'permission_callback' => '__return_true',
        ));
        
        // Generic subscription webhook
        register_rest_route('rde/v1', '/webhook/subscription', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_subscription_webhook'),
            'permission_callback' => array($this, 'verify_webhook_signature'),
        ));
        
        // Analytics webhook
        register_rest_route('rde/v1', '/webhook/analytics', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_analytics_webhook'),
            'permission_callback' => array($this, 'verify_webhook_signature'),
        ));
    }
    
    /**
     * Handle CCBill webhook
     */
    public function handle_ccbill_webhook($request) {
        $params = $request->get_params();
        
        // Log webhook data
        $this->log_webhook('ccbill', $params);
        
        // Verify CCBill signature
        if (!$this->verify_ccbill_signature($params)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid signature'
            ), 401);
        }
        
        $event_type = isset($params['eventType']) ? $params['eventType'] : '';
        
        switch ($event_type) {
            case 'NewSaleSuccess':
                $this->process_new_sale($params);
                break;
                
            case 'NewSaleFailure':
                $this->process_sale_failure($params);
                break;
                
            case 'Renewal':
                $this->process_renewal($params);
                break;
                
            case 'Cancellation':
                $this->process_cancellation($params);
                break;
                
            case 'Chargeback':
                $this->process_chargeback($params);
                break;
                
            case 'Refund':
                $this->process_refund($params);
                break;
                
            default:
                $this->log_webhook('ccbill_unknown', array(
                    'event_type' => $event_type,
                    'params'     => $params
                ));
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Webhook processed'
        ), 200);
    }
    
    /**
     * Handle subscription webhook
     */
    public function handle_subscription_webhook($request) {
        $params = $request->get_params();
        
        $this->log_webhook('subscription', $params);
        
        $action = isset($params['action']) ? $params['action'] : '';
        $subscription_id = isset($params['subscription_id']) ? $params['subscription_id'] : 0;
        
        switch ($action) {
            case 'created':
                do_action('rde_webhook_subscription_created', $subscription_id, $params);
                break;
                
            case 'updated':
                do_action('rde_webhook_subscription_updated', $subscription_id, $params);
                break;
                
            case 'cancelled':
                do_action('rde_webhook_subscription_cancelled', $subscription_id, $params);
                break;
                
            case 'renewed':
                do_action('rde_webhook_subscription_renewed', $subscription_id, $params);
                break;
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Subscription webhook processed'
        ), 200);
    }
    
    /**
     * Handle analytics webhook
     */
    public function handle_analytics_webhook($request) {
        $params = $request->get_params();
        
        $this->log_webhook('analytics', $params);
        
        // Process analytics data
        $this->store_analytics_data($params);
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Analytics data received'
        ), 200);
    }
    
    /**
     * Process new sale
     */
    private function process_new_sale($params) {
        $order_id = isset($params['order_id']) ? intval($params['order_id']) : 0;
        $subscription_id = isset($params['subscription_id']) ? $params['subscription_id'] : '';
        
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->payment_complete($subscription_id);
                $order->add_order_note(sprintf(
                    __('Payment completed via CCBill. Subscription ID: %s', 'rde-webhooks'),
                    $subscription_id
                ));
                
                // Create subscription record
                do_action('rde_create_subscription_from_order', $order, $params);
            }
        }
    }
    
    /**
     * Process sale failure
     */
    private function process_sale_failure($params) {
        $order_id = isset($params['order_id']) ? intval($params['order_id']) : 0;
        
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->update_status('failed', __('Payment failed via CCBill', 'rde-webhooks'));
            }
        }
    }
    
    /**
     * Process renewal
     */
    private function process_renewal($params) {
        $subscription_id = isset($params['subscription_id']) ? $params['subscription_id'] : '';
        
        // Update subscription next payment date
        do_action('rde_subscription_renewed', $subscription_id, $params);
    }
    
    /**
     * Process cancellation
     */
    private function process_cancellation($params) {
        $subscription_id = isset($params['subscription_id']) ? $params['subscription_id'] : '';
        
        // Cancel subscription
        do_action('rde_subscription_cancelled', $subscription_id, $params);
    }
    
    /**
     * Process chargeback
     */
    private function process_chargeback($params) {
        $order_id = isset($params['order_id']) ? intval($params['order_id']) : 0;
        
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->update_status('refunded', __('Chargeback received from CCBill', 'rde-webhooks'));
            }
        }
    }
    
    /**
     * Process refund
     */
    private function process_refund($params) {
        $order_id = isset($params['order_id']) ? intval($params['order_id']) : 0;
        
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->update_status('refunded', __('Refund processed via CCBill', 'rde-webhooks'));
            }
        }
    }
    
    /**
     * Verify CCBill signature
     */
    private function verify_ccbill_signature($params) {
        // Implement CCBill signature verification
        // This depends on your specific CCBill configuration
        return true;
    }
    
    /**
     * Verify webhook signature
     */
    public function verify_webhook_signature($request) {
        $signature = $request->get_header('X-Webhook-Signature');
        
        if (empty($signature)) {
            return false;
        }
        
        $body = $request->get_body();
        $secret = get_option('rde_webhook_secret', '');
        $expected_signature = hash_hmac('sha256', $body, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Log webhook data
     */
    private function log_webhook($type, $data) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'RDE Webhook [%s]: %s',
                $type,
                json_encode($data)
            ));
        }
        
        // Store in database for audit trail
        global $wpdb;
        $table_name = $wpdb->prefix . 'rde_webhook_logs';
        
        $wpdb->insert($table_name, array(
            'webhook_type' => $type,
            'payload'      => json_encode($data),
            'created_at'   => current_time('mysql')
        ));
    }
    
    /**
     * Store analytics data
     */
    private function store_analytics_data($params) {
        // Store analytics data in custom table or post meta
        do_action('rde_store_analytics', $params);
    }
}

// Initialize
RDE_Webhook_Handler::get_instance();
