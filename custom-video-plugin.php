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
    $video_url = highlight_product_video_upload_default_video('assets/default-video.mp4');
    $video_url_mbl = highlight_product_video_upload_default_video('assets/default-video-mobile.mp4');

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

/**
 * Uploads a default video to the WordPress media library.
 */
function highlight_product_video_upload_default_video($relative_path) {
    // Construct the absolute path using the directory constant
    $file_path = HIGHLIGHT_PRODUCT_VIDEO_DIR . $relative_path;

    // Check if the file exists at the specified path
    if (!file_exists($file_path)) {
        return false; // Return false if the file does not exist
    }

    $file_name = basename($file_path); // Get the file name from the path
    $file_type = wp_check_filetype($file_name, null); // Check the file type

    // Read the file contents
    $file_content = file_get_contents($file_path);

    if (!$file_content) {
        return false; // Return false if the file contents cannot be read
    }

    // Upload the file using wp_upload_bits() and check for errors
    $upload = wp_upload_bits($file_name, null, $file_content);

    // Check if there was an error during the upload
    if ($upload['error']) {
        return false; // Return false if there was an error
    }

    // Prepare the attachment array to insert into the media library
    $attachment = [
        'guid'           => $upload['url'], // URL of the uploaded file
        'post_mime_type' => $file_type['type'], // Mime type
        'post_title'     => sanitize_file_name($file_name), // Clean the file name for title
        'post_content'   => '', // No content
        'post_status'    => 'inherit', // Set the status to 'inherit' to associate with media
    ];

    // Insert the attachment into the media library
    $attach_id = wp_insert_attachment($attachment, $upload['file']);

    if (!$attach_id) {
        return false; // Return false if the attachment insertion failed
    }

    // Include image functions to generate the metadata
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Update the attachment metadata
    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));

    return $upload['url']; // Return the URL of the uploaded file
}



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



