<?php
if (!defined('ABSPATH')) {
    exit;
}

function highlight_product_video_menu() {
    add_menu_page(
        'Highlight Product Video',
        'Highlight Product Video',
        'manage_options',
        'highlight-product-video',
        'highlight_product_video_admin_page',
        'dashicons-video-alt3'
    );

    // Add a submenu for editing videos
    add_submenu_page(
        null, // Parent slug is null to hide it from the menu
        'Edit Video',
        'Edit Video',
        'manage_options',
        'edit-highlight-product-video-',
        'highlight_product_video_edit_page'
    );
}
add_action('admin_menu', 'highlight_product_video_menu');

function highlight_product_video_enqueue_admin_scripts($hook) {
    // Identify the current screen's query variables
    $screen = get_current_screen();
    // Load scripts only on specific plugin pages
    wp_enqueue_media();
    wp_enqueue_script('jquery');

    // Include Select2 for better dropdown UX
    wp_enqueue_script(
        'select2',
        HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/vendor/select2.min.js',
        ['jquery'],
        '4.1.0',
        true
    );
    wp_enqueue_style(
        'select2',
        HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/vendor/select2.min.css',
        [],
        '4.1.0'
    );

    // Load your custom admin.js script
    wp_enqueue_script(
        'highlight-product-video--admin-script',
        HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/admin.js',
        ['jquery', 'select2'],
        filemtime(HIGHLIGHT_PRODUCT_VIDEO_DIR . 'assets/admin.js'), 
        true
    );

    // Load your custom admin.css style
    wp_enqueue_style(
        'highlight-product-video--admin-style',
        HIGHLIGHT_PRODUCT_VIDEO_URL . 'assets/admin.css',
        [],
        filemtime(HIGHLIGHT_PRODUCT_VIDEO_DIR . 'assets/admin.css') // Cache busting
    );
    
}
add_action('admin_enqueue_scripts', 'highlight_product_video_enqueue_admin_scripts');

function highlight_product_video_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Highlight Product Video</h1>';

    include plugin_dir_path(__FILE__) . 'admin-video-list.php';

    echo '</div>';
}


function highlight_product_video_edit_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="wrap">';
    
    include plugin_dir_path(__FILE__) . 'admin-video-edit.php';

    echo '</div>';
}
