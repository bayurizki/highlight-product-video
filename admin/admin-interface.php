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
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

    // Load scripts only on specific plugin pages
    if ($current_page === 'highlight-product-video' || $current_page === 'edit-highlight-product-video-') {
        wp_enqueue_media();
        wp_enqueue_script('jquery');

        // Include Select2 for better dropdown UX
        wp_enqueue_script(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            ['jquery'],
            '4.1.0',
            true
        );
        wp_enqueue_style(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
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
}
add_action('admin_enqueue_scripts', 'highlight_product_video_enqueue_admin_scripts');

function highlight_product_video_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Highlight Product Video</h1>';

    // Add a button to generate the default video record
    echo '<form method="post" action="">';
    wp_nonce_field('generate_default_video', 'generate_default_video_nonce');
    echo '<input type="submit" name="generate_default_video" class="button button-primary" value="Generate Default Video">';
    echo '</form>';

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
