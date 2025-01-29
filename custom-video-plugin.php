<?php
/**
 * Plugin Name: Custom Video Plugin
 * Description: A plugin to display videos with associated products using shortcodes.
 * Version: 1.0
 * Author: bayurizki
 * Author URI: https://github.com/bayurizki/
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include admin interface
require_once HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_DIR . 'admin/admin-interface.php';
// Include shortcode handler
require_once HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_DIR . 'includes/shortcode-handler.php';

// Enqueue frontend styles and scripts
function highlight_product_video_plugin_enqueue_scripts() {
    wp_enqueue_style('highlight-product-video-admin-style', HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL . 'assets/admin.css', [], '1.0.0');
    wp_enqueue_style('highlight-product-video-style', HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL . 'assets/styles.css', [], '1.11.0');

    wp_enqueue_script('highlight-product-video-script', HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL . 'assets/script.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'highlight_product_video_plugin_enqueue_scripts');

// Register custom post type
function highlight_product_video_plugin_register_post_type() {
    register_post_type('highlight_product_video', [
        'label' => 'Custom Videos',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'custom-fields'],
        'capability_type' => 'post',
    ]);
}
add_action('init', 'highlight_product_video_plugin_register_post_type');
