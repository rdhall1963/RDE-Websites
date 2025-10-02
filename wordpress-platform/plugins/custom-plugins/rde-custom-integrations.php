<?php
/**
 * Plugin Name: RDE Custom Integrations
 * Plugin URI: https://github.com/rdhall1963/RDE-Websites
 * Description: Custom integrations for RDE websites including enhanced features for WooCommerce, Elementor, and analytics.
 * Version: 1.0.0
 * Author: RDE Websites
 * Author URI: https://github.com/rdhall1963
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: rde-custom
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RDE_CUSTOM_VERSION', '1.0.0');
define('RDE_CUSTOM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RDE_CUSTOM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class RDE_Custom_Integrations {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // WooCommerce hooks
        if (class_exists('WooCommerce')) {
            add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
        }
        
        // Elementor hooks
        if (defined('ELEMENTOR_VERSION')) {
            add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));
        }
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('rde-custom', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Include required files
        $this->includes();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/class-analytics.php';
        require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/class-seo.php';
        require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/class-multilingual.php';
        
        if (class_exists('WooCommerce')) {
            require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/woocommerce/class-custom-fields.php';
            require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/woocommerce/class-checkout-customizer.php';
        }
        
        if (defined('ELEMENTOR_VERSION')) {
            require_once RDE_CUSTOM_PLUGIN_DIR . 'includes/elementor/class-custom-widgets.php';
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('RDE Settings', 'rde-custom'),
            __('RDE Settings', 'rde-custom'),
            'manage_options',
            'rde-custom-settings',
            array($this, 'render_admin_page'),
            'dashicons-admin-generic',
            30
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        include RDE_CUSTOM_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_rde-custom-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style('rde-custom-admin', RDE_CUSTOM_PLUGIN_URL . 'admin/css/admin.css', array(), RDE_CUSTOM_VERSION);
        wp_enqueue_script('rde-custom-admin', RDE_CUSTOM_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), RDE_CUSTOM_VERSION, true);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('rde-custom-frontend', RDE_CUSTOM_PLUGIN_URL . 'assets/css/frontend.css', array(), RDE_CUSTOM_VERSION);
        wp_enqueue_script('rde-custom-frontend', RDE_CUSTOM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), RDE_CUSTOM_VERSION, true);
    }
    
    /**
     * Add custom payment gateways
     */
    public function add_payment_gateways($gateways) {
        // Payment gateway classes will be added here
        return $gateways;
    }
    
    /**
     * Register custom Elementor widgets
     */
    public function register_elementor_widgets($widgets_manager) {
        // Custom widgets will be registered here
    }
}

/**
 * Initialize the plugin
 */
function rde_custom_integrations() {
    return RDE_Custom_Integrations::get_instance();
}

// Start the plugin
rde_custom_integrations();
