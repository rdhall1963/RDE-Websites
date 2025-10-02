<?php
/**
 * Subscription Manager
 * 
 * Automated subscription lifecycle management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RDE_Subscription_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_subscription_post_type'));
        add_action('rde_check_subscription_renewals', array($this, 'check_renewals'));
        add_action('rde_check_subscription_expirations', array($this, 'check_expirations'));
        
        // Schedule daily checks
        if (!wp_next_scheduled('rde_check_subscription_renewals')) {
            wp_schedule_event(time(), 'daily', 'rde_check_subscription_renewals');
        }
        
        if (!wp_next_scheduled('rde_check_subscription_expirations')) {
            wp_schedule_event(time(), 'daily', 'rde_check_subscription_expirations');
        }
    }
    
    /**
     * Register subscription custom post type
     */
    public function register_subscription_post_type() {
        register_post_type('rde_subscription', array(
            'labels' => array(
                'name'          => __('Subscriptions', 'rde-subscriptions'),
                'singular_name' => __('Subscription', 'rde-subscriptions'),
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'supports'     => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
    }
    
    /**
     * Create a new subscription
     */
    public function create_subscription($args) {
        $defaults = array(
            'user_id'           => 0,
            'product_id'        => 0,
            'order_id'          => 0,
            'status'            => 'active',
            'amount'            => 0,
            'billing_period'    => 'month',
            'billing_interval'  => 1,
            'start_date'        => current_time('mysql'),
            'next_payment_date' => '',
            'end_date'          => '',
            'trial_end_date'    => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Create subscription post
        $subscription_id = wp_insert_post(array(
            'post_type'   => 'rde_subscription',
            'post_title'  => sprintf('Subscription #%s', uniqid()),
            'post_status' => 'publish',
        ));
        
        if (is_wp_error($subscription_id)) {
            return false;
        }
        
        // Save subscription meta
        foreach ($args as $key => $value) {
            update_post_meta($subscription_id, '_' . $key, $value);
        }
        
        // Set next payment date
        if (empty($args['next_payment_date'])) {
            $next_payment = $this->calculate_next_payment_date(
                $args['start_date'],
                $args['billing_period'],
                $args['billing_interval']
            );
            update_post_meta($subscription_id, '_next_payment_date', $next_payment);
        }
        
        // Trigger action
        do_action('rde_subscription_created', $subscription_id, $args);
        
        return $subscription_id;
    }
    
    /**
     * Calculate next payment date
     */
    private function calculate_next_payment_date($from_date, $period, $interval = 1) {
        $date = new DateTime($from_date);
        
        switch ($period) {
            case 'day':
                $date->modify("+{$interval} days");
                break;
            case 'week':
                $date->modify("+{$interval} weeks");
                break;
            case 'month':
                $date->modify("+{$interval} months");
                break;
            case 'year':
                $date->modify("+{$interval} years");
                break;
        }
        
        return $date->format('Y-m-d H:i:s');
    }
    
    /**
     * Update subscription status
     */
    public function update_subscription_status($subscription_id, $new_status) {
        $valid_statuses = array('active', 'paused', 'cancelled', 'expired', 'pending');
        
        if (!in_array($new_status, $valid_statuses)) {
            return false;
        }
        
        $old_status = get_post_meta($subscription_id, '_status', true);
        update_post_meta($subscription_id, '_status', $new_status);
        
        // Trigger status change action
        do_action('rde_subscription_status_changed', $subscription_id, $old_status, $new_status);
        
        return true;
    }
    
    /**
     * Cancel subscription
     */
    public function cancel_subscription($subscription_id) {
        $this->update_subscription_status($subscription_id, 'cancelled');
        update_post_meta($subscription_id, '_end_date', current_time('mysql'));
        
        do_action('rde_subscription_cancelled', $subscription_id);
        
        return true;
    }
    
    /**
     * Pause subscription
     */
    public function pause_subscription($subscription_id) {
        $this->update_subscription_status($subscription_id, 'paused');
        do_action('rde_subscription_paused', $subscription_id);
        return true;
    }
    
    /**
     * Resume subscription
     */
    public function resume_subscription($subscription_id) {
        $this->update_subscription_status($subscription_id, 'active');
        
        // Recalculate next payment date
        $billing_period = get_post_meta($subscription_id, '_billing_period', true);
        $billing_interval = get_post_meta($subscription_id, '_billing_interval', true);
        $next_payment = $this->calculate_next_payment_date(
            current_time('mysql'),
            $billing_period,
            $billing_interval
        );
        update_post_meta($subscription_id, '_next_payment_date', $next_payment);
        
        do_action('rde_subscription_resumed', $subscription_id);
        return true;
    }
    
    /**
     * Check for upcoming renewals
     */
    public function check_renewals() {
        global $wpdb;
        
        $today = current_time('Y-m-d');
        $grace_days = 3; // Days before renewal to send notification
        $notification_date = date('Y-m-d', strtotime("+{$grace_days} days"));
        
        // Get subscriptions due for renewal
        $subscription_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_next_payment_date' 
            AND meta_value BETWEEN %s AND %s",
            $today,
            $notification_date
        ));
        
        foreach ($subscription_ids as $subscription_id) {
            $status = get_post_meta($subscription_id, '_status', true);
            
            if ($status === 'active') {
                $this->process_renewal($subscription_id);
            }
        }
    }
    
    /**
     * Process subscription renewal
     */
    private function process_renewal($subscription_id) {
        $user_id = get_post_meta($subscription_id, '_user_id', true);
        $amount = get_post_meta($subscription_id, '_amount', true);
        
        // Trigger renewal action (payment processing happens here)
        do_action('rde_subscription_renewal', $subscription_id, $user_id, $amount);
        
        // Send renewal notification
        $this->send_renewal_notification($subscription_id);
        
        // Update next payment date
        $billing_period = get_post_meta($subscription_id, '_billing_period', true);
        $billing_interval = get_post_meta($subscription_id, '_billing_interval', true);
        $current_next_payment = get_post_meta($subscription_id, '_next_payment_date', true);
        
        $next_payment = $this->calculate_next_payment_date(
            $current_next_payment,
            $billing_period,
            $billing_interval
        );
        update_post_meta($subscription_id, '_next_payment_date', $next_payment);
    }
    
    /**
     * Check for expired subscriptions
     */
    public function check_expirations() {
        global $wpdb;
        
        $today = current_time('Y-m-d');
        
        // Get subscriptions that should be expired
        $subscription_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_end_date' 
            AND meta_value <= %s 
            AND meta_value != ''",
            $today
        ));
        
        foreach ($subscription_ids as $subscription_id) {
            $status = get_post_meta($subscription_id, '_status', true);
            
            if ($status !== 'expired' && $status !== 'cancelled') {
                $this->update_subscription_status($subscription_id, 'expired');
                $this->send_expiration_notification($subscription_id);
            }
        }
    }
    
    /**
     * Send renewal notification
     */
    private function send_renewal_notification($subscription_id) {
        $user_id = get_post_meta($subscription_id, '_user_id', true);
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        $next_payment_date = get_post_meta($subscription_id, '_next_payment_date', true);
        $amount = get_post_meta($subscription_id, '_amount', true);
        
        $subject = __('Subscription Renewal Reminder', 'rde-subscriptions');
        $message = sprintf(
            __('Your subscription will renew on %s for %s.', 'rde-subscriptions'),
            date('F j, Y', strtotime($next_payment_date)),
            wc_price($amount)
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    /**
     * Send expiration notification
     */
    private function send_expiration_notification($subscription_id) {
        $user_id = get_post_meta($subscription_id, '_user_id', true);
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        $subject = __('Subscription Expired', 'rde-subscriptions');
        $message = __('Your subscription has expired. Please renew to continue enjoying our services.', 'rde-subscriptions');
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    /**
     * Get user subscriptions
     */
    public function get_user_subscriptions($user_id, $status = '') {
        $args = array(
            'post_type'      => 'rde_subscription',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_user_id',
                    'value' => $user_id,
                ),
            ),
        );
        
        if (!empty($status)) {
            $args['meta_query'][] = array(
                'key'   => '_status',
                'value' => $status,
            );
        }
        
        return get_posts($args);
    }
}

// Initialize
RDE_Subscription_Manager::get_instance();
