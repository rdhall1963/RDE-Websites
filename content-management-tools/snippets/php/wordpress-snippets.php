<?php
/**
 * Custom WordPress Snippets Library
 * 
 * Common PHP snippets for WordPress development
 */

// Snippet: Custom Post Type Registration
function register_custom_post_type() {
    $labels = array(
        'name'               => 'Custom Items',
        'singular_name'      => 'Custom Item',
        'menu_name'          => 'Custom Items',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Custom Item',
        'edit_item'          => 'Edit Custom Item',
        'new_item'           => 'New Custom Item',
        'view_item'          => 'View Custom Item',
        'search_items'       => 'Search Custom Items',
        'not_found'          => 'No custom items found',
        'not_found_in_trash' => 'No custom items found in Trash',
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'custom-items'),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_icon'           => 'dashicons-portfolio',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'        => true,
    );
    
    register_post_type('custom_item', $args);
}
// add_action('init', 'register_custom_post_type');

// Snippet: Custom Taxonomy Registration
function register_custom_taxonomy() {
    $labels = array(
        'name'              => 'Categories',
        'singular_name'     => 'Category',
        'search_items'      => 'Search Categories',
        'all_items'         => 'All Categories',
        'parent_item'       => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item'         => 'Edit Category',
        'update_item'       => 'Update Category',
        'add_new_item'      => 'Add New Category',
        'new_item_name'     => 'New Category Name',
        'menu_name'         => 'Categories',
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'custom-category'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('custom_category', array('custom_item'), $args);
}
// add_action('init', 'register_custom_taxonomy');

// Snippet: Add custom meta box
function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box',
        'Custom Information',
        'render_custom_meta_box',
        'post',
        'normal',
        'high'
    );
}
// add_action('add_meta_boxes', 'add_custom_meta_box');

function render_custom_meta_box($post) {
    wp_nonce_field('custom_meta_box_nonce', 'custom_meta_box_nonce');
    $value = get_post_meta($post->ID, '_custom_field', true);
    echo '<label for="custom_field">Custom Field: </label>';
    echo '<input type="text" id="custom_field" name="custom_field" value="' . esc_attr($value) . '" />';
}

function save_custom_meta_box($post_id) {
    if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'custom_meta_box_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['custom_field'])) {
        update_post_meta($post_id, '_custom_field', sanitize_text_field($_POST['custom_field']));
    }
}
// add_action('save_post', 'save_custom_meta_box');

// Snippet: Custom shortcode
function custom_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Default Title',
        'content' => 'Default content',
    ), $atts);
    
    return '<div class="custom-shortcode"><h3>' . esc_html($atts['title']) . '</h3><p>' . esc_html($atts['content']) . '</p></div>';
}
// add_shortcode('custom', 'custom_shortcode');

// Snippet: Add custom admin column
function add_custom_admin_column($columns) {
    $columns['custom_field'] = 'Custom Field';
    return $columns;
}
// add_filter('manage_post_posts_columns', 'add_custom_admin_column');

function populate_custom_admin_column($column, $post_id) {
    if ($column === 'custom_field') {
        echo esc_html(get_post_meta($post_id, '_custom_field', true));
    }
}
// add_action('manage_post_posts_custom_column', 'populate_custom_admin_column', 10, 2);

// Snippet: Custom query modification
function modify_main_query($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('posts_per_page', 12);
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
    }
}
// add_action('pre_get_posts', 'modify_main_query');

// Snippet: Add custom user role
function add_custom_user_role() {
    add_role(
        'custom_role',
        'Custom Role',
        array(
            'read'         => true,
            'edit_posts'   => true,
            'delete_posts' => false,
        )
    );
}
// add_action('init', 'add_custom_user_role');

// Snippet: Enqueue custom assets conditionally
function enqueue_custom_assets() {
    if (is_singular('post')) {
        wp_enqueue_style('custom-post-style', get_template_directory_uri() . '/css/custom-post.css');
        wp_enqueue_script('custom-post-script', get_template_directory_uri() . '/js/custom-post.js', array('jquery'), '1.0', true);
    }
}
// add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

// Snippet: Custom REST API endpoint
function register_custom_rest_route() {
    register_rest_route('custom/v1', '/data', array(
        'methods'  => 'GET',
        'callback' => 'get_custom_data',
        'permission_callback' => '__return_true',
    ));
}
// add_action('rest_api_init', 'register_custom_rest_route');

function get_custom_data() {
    return array(
        'status' => 'success',
        'data'   => array(
            'message' => 'Custom API data',
        ),
    );
}
