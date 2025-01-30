<?php
/**
 * Plugin Name: Highlight Product Video
 * Description: A plugin to display videos with associated products using shortcodes.
 * Version: 1.0
 * Author: BY
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
    wp_enqueue_style('custom-video-admin-style', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/admin.css', [], '1.0.0');
    wp_enqueue_style('custom-video-style', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/styles.css', [], '1.11.0');

    wp_enqueue_script('custom-video-script', HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/script.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'highlight_product_video_enqueue_scripts');

// Register custom post type
function highlight_product_video_register_post_type() {
    register_post_type('custom_video', [
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
    $video_url = highlight_product_video_upload_default_video('assets/default-video.mp4');
    $video_url_mbl = highlight_product_video_upload_default_video('assets/default-video-mobile.mp4');

    // Check if there are any existing videos
    $existing_videos = new WP_Query([
        'post_type'      => 'custom_video',
        'posts_per_page' => 1,
    ]);

    // If no videos exist, insert a default video post
    if (!$existing_videos->have_posts() && $video_url) {
        wp_insert_post([
            'post_type'   => 'custom_video',
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

/**
 * Uploads a default video to the WordPress media library.
 */
function highlight_product_video_upload_default_video($relative_path) {
    $file_path = HIGHLIGHT_PRODUCT_VIDEO_DIR . $relative_path;

    // Check if file exists
    if (!file_exists($file_path)) {
        return false;
    }

    $file_name = basename($file_path);
    $file_type = wp_check_filetype($file_name, null);

    // Prepare file for upload
    $upload = wp_upload_bits($file_name, null, file_get_contents($file_path));

    if (!$upload['error']) {
        // Insert file into the media library
        $attachment = [
            'guid'           => $upload['url'],
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name($file_name),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata(
            $attach_id,
            wp_generate_attachment_metadata($attach_id, $upload['file'])
        );

        return $upload['url']; // Return the uploaded video URL
    }

    return false;
}



