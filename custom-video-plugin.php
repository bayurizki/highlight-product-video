<?php
/**
 * Plugin Name: Highlight Product Video
 * Description: A plugin to display videos with associated woocommerce product using shortcodes.
 * Version: 1.0
 * Author: Dibara team
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HIGHLIGHT_PRODUCT_VIDEO_DIR', plugin_dir_path(__FILE__));
define('HIGHLIGHT_PRODUCT_VIDEO_URL', plugin_dir_url(__FILE__));

// Include admin interface
require_once HIGHLIGHT_PRODUCT_VIDEO_DIR . 'admin/admin-interface.php';
// Include shortcode handler
require_once HIGHLIGHT_PRODUCT_VIDEO_DIR . 'includes/shortcode-handler.php';

// Enqueue frontend styles and scripts
function highlight_product_video_enqueue_scripts() {
    wp_enqueue_style('highlight-product-video--admin-style', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/admin.css', [], '1.0.0');
    wp_enqueue_style('highlight-product-video--style', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/styles.css', [], '1.11.0');

    wp_enqueue_script('highlight-product-video--script', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/script.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'highlight_product_video_enqueue_scripts');

// Register custom post type
function highlight_product_video_register_post_type() {
    register_post_type('highlight_video', [
        'label' => 'Highlight Product Videos',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'custom-fields'],
        'capability_type' => 'post',
    ]);
}
add_action('init', 'highlight_product_video_register_post_type');


function highlight_product_video_activate() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the plugin
        wp_die(
            'This plugin requires WooCommerce to be installed and activated. <br><a href="' . esc_url(admin_url('plugins.php')) . '">Go back</a>',
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }

    // Upload the default video to the media library
    $video_url ='https://bdd.services/content/default.mp4';
    $video_url_mbl = 'https://bdd.services/content/default-mobile.mp4';

    // $video_url = 'test';
    // $video_url_mbl = 'test';
    
    // Check if there are any existing videos
    $existing_videos = new WP_Query([
        'post_type'      => 'highlight_video',
        'posts_per_page' => 1,
    ]);

    // If no videos exist, insert a default video post
    if (!$existing_videos->have_posts() && $video_url) {
        wp_insert_post([
            'post_type'   => 'highlight_video',
            'post_title'  => 'Default Video',
            'post_status' => 'publish',
            'meta_input'  => [
                'video_url'  => $video_url,
                'video_url_mbl' => $video_url_mbl,
                'product_id' => 0, // Set to a valid product ID if needed
            ],
        ]);
        
    }
}

// Hook into plugin activation
register_activation_hook(__FILE__, 'highlight_product_video_activate');


if (!defined('ABSPATH')) {
    exit;
}

// Function to delete default video records
function highlight_video_product_plugin_remove_records() {
    global $wpdb;

    // Get all default video records (adjust based on your logic)
    $default_videos = get_posts([
        'post_type'      => 'highlight_video',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => 'is_default_video',
                'value'   => '1',
                'compare' => '='
            ]
        ]
    ]);

    // Delete each default video post
    if (!empty($default_videos)) {
        foreach ($default_videos as $post_id) {
            wp_delete_post($post_id, true); // Force delete
        }
    }
}

// Hook into plugin deactivation
register_deactivation_hook(__FILE__, 'highlight_video_product_plugin_remove_records');



