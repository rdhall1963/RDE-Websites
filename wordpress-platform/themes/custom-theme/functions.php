<?php
/**
 * RDE Custom Theme Functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('RDE_THEME_VERSION', '1.0.0');
define('RDE_THEME_DIR', get_template_directory());
define('RDE_THEME_URI', get_template_directory_uri());

/**
 * Theme setup
 */
function rde_theme_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');
    
    // Let WordPress manage the document title
    add_theme_support('title-tag');
    
    // Enable support for Post Thumbnails
    add_theme_support('post-thumbnails');
    
    // Add support for wide and full aligned images
    add_theme_support('align-wide');
    
    // Add support for responsive embeds
    add_theme_support('responsive-embeds');
    
    // Add support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'rde-theme'),
        'footer'  => __('Footer Menu', 'rde-theme'),
    ));
    
    // Add WooCommerce support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Add Elementor support
    add_theme_support('elementor');
}
add_action('after_setup_theme', 'rde_theme_setup');

/**
 * Register widget areas
 */
function rde_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'rde-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'rde-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer', 'rde-theme'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'rde-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'rde_widgets_init');

/**
 * Enqueue scripts and styles
 */
function rde_theme_scripts() {
    // Enqueue theme styles
    wp_enqueue_style('rde-theme-style', get_stylesheet_uri(), array(), RDE_THEME_VERSION);
    
    // Enqueue theme scripts
    wp_enqueue_script('rde-theme-script', RDE_THEME_URI . '/assets/js/theme.js', array('jquery'), RDE_THEME_VERSION, true);
    
    // Enqueue comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'rde_theme_scripts');

/**
 * Custom template tags
 */
require RDE_THEME_DIR . '/inc/template-tags.php';

/**
 * Customizer additions
 */
require RDE_THEME_DIR . '/inc/customizer.php';

/**
 * WooCommerce customizations
 */
if (class_exists('WooCommerce')) {
    require RDE_THEME_DIR . '/inc/woocommerce.php';
}

/**
 * Elementor customizations
 */
if (defined('ELEMENTOR_VERSION')) {
    require RDE_THEME_DIR . '/inc/elementor.php';
}
