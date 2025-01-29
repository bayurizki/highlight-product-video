<?php
if (!defined('ABSPATH')) {
    exit;
}

function highlight_product_video_plugin_menu() {
    add_menu_page(
        'Custom Video Plugin',
        'Custom Video',
        'manage_options',
        'highlight-product-video-plugin',
        'highlight_product_video_plugin_admin_page',
        'dashicons-video-alt3'
    );

    // Add a submenu for editing videos
    add_submenu_page(
        null, // Parent slug is null to hide it from the menu
        'Edit Video',
        'Edit Video',
        'manage_options',
        'edit-highlight-product-video',
        'highlight_product_video_plugin_edit_page'
    );
}
add_action('admin_menu', 'highlight_product_video_plugin_menu');

function highlight_product_video_plugin_enqueue_admin_scripts($hook) {
    // Identify the current screen's query variables
    $screen = get_current_screen();
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

    // Load scripts only on specific plugin pages
    if ($current_page === 'highlight-product-video-plugin' || $current_page === 'edit-highlight-product-video') {
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
            'highlight-product-video-admin-script',
            HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL . 'assets/admin.js',
            ['jquery', 'select2'],
            filemtime(HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_PATH . 'assets/admin.js'), 
            true
        );

        // Load your custom admin.css style
        wp_enqueue_style(
            'highlight-product-video-admin-style',
            HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_URL . 'assets/admin.css',
            [],
            filemtime(HIGHLIGHT_PRODUCT_VIDEO_PLUGIN_PATH . 'assets/admin.css') // Cache busting
        );
    }
}
add_action('admin_enqueue_scripts', 'highlight_product_video_plugin_enqueue_admin_scripts');

function highlight_product_video_plugin_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_submit'])) {
        $video_url = isset($_POST['video_url']) ? esc_url_raw($_POST['video_url']) : '';
        $video_url_mbl = isset($_POST['video_url_mbl']) ? esc_url_raw($_POST['video_url_mbl']) : '';
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($video_url && $product_id) {
            $video_id = wp_insert_post([
                'post_type'   => 'highlight_product_video',
                'post_title'  => 'Video for Product ' . $product_id,
                'post_status' => 'publish',
                'meta_input'  => [
                    'video_url'  => $video_url,
                    'video_url_mbl' => $video_url_mbl,
                    'product_id' => $product_id,
                ],
            ]);

            if ($video_id) {
                echo '<div class="notice notice-success is-dismissible"><p>Video saved successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to save the video.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>All fields are required.</p></div>';
        }
    }

    // Display list of videos with edit and delete buttons
    $args = [
        'post_type'      => 'highlight_product_video',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];

    $video_posts = new WP_Query($args);

    ?>
    <div class="wrap">
        <h1>Custom Video Plugin</h1>

        <h2>Saved Videos</h2>

        <?php if ($video_posts->have_posts()) : ?>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th>Video</th>
                        <th>Product</th>
                        <th>Shortcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($video_posts->have_posts()) : $video_posts->the_post(); ?>
                        <tr>
                            <td><?php echo esc_url(get_post_meta(get_the_ID(), 'video_url', true)); ?></td>
                            <td>
                                <?php
                                $product_id = get_post_meta(get_the_ID(), 'product_id', true);
                                $product = wc_get_product($product_id);
                                echo $product ? esc_html($product->get_name()) : 'No product associated';
                                ?>
                            </td>
                            <td>
                                <code>[show_video id="<?php echo get_the_ID(); ?>"]</code>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=edit-highlight-product-video&id=' . get_the_ID()); ?>" class="button">Edit</a>
                                <!-- <a href="<?php echo get_edit_post_link(get_the_ID()); ?>" class="button">Edit</a> -->
                                <!-- <a href="<?php echo get_delete_post_link(get_the_ID()); ?>" class="button" onclick="return confirm('Are you sure you want to delete this video?');">Delete</a> -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No videos saved yet.</p>
        <?php endif; ?>
    </div>
    <?php
}

function highlight_product_video_plugin_edit_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $video_post = get_post($video_id);

    if (!$video_post || $video_post->post_type !== 'highlight_product_video') {
        echo '<div class="notice notice-error"><p>Invalid video ID.</p></div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_update'])) {
        $video_url = isset($_POST['video_url']) ? esc_url_raw($_POST['video_url']) : '';
        $video_url_mbl = isset($_POST['video_url_mbl']) ? esc_url_raw($_POST['video_url_mbl']) : '';
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($video_url && $product_id) {
            $updated = wp_update_post([
                'ID' => $video_id,
                'meta_input' => [
                    'video_url' => $video_url,
                    'video_url_mbl' => $video_url_mbl,
                    'product_id' => $product_id,
                ],
            ]);

            if ($updated) {
            // Redirect to the main page after saving
                echo '<div class="notice notice-success is-dismissible"><p>Video updated successfully!</p></div>';
                wp_redirect(admin_url('admin.php?page=highlight-product-video-plugin'));
                exit; // Ensure no further code is executed after the redirect
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update the video.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>All fields are required.</p></div>';
        }
    }

    $video_url = get_post_meta($video_id, 'video_url', true);
    $video_url_mbl = get_post_meta($video_id, 'video_url_mbl', true);
    $product_id = get_post_meta($video_id, 'product_id', true);

    ?>
    <div class="wrap">
        <h1>Edit Video</h1>

        <form method="post">
            <label for="video_url">Select Video:</label>
            <input type="hidden" name="video_url" id="video_url" value="<?php echo esc_url($video_url); ?>" required>
            <button type="button" id="select-video" class="button">Select Video</button>
            <div id="selected-video-preview"><?php echo esc_url($video_url); ?></div>
            <br><br>

            <label for="video_url_mbl">Select Video for Mobile:</label>
            <input type="hidden" name="video_url_mbl" id="video_url_mbl" value="<?php echo esc_url($video_url_mbl); ?>" required>
            <button type="button" id="select-video-mbl" class="button">Select Video Mobile</button>
            <div id="selected-video-preview_mbl"><?php echo esc_url($video_url_mbl); ?></div>
            <br><br>

            <label for="product_id">Select Product:</label>
            <select name="product_id" id="product_id" style="width: 100%;" required>
                <option value="">Select a product</option>
                <?php
                $products = wc_get_products(['limit' => -1]);
                foreach ($products as $product) {
                    $selected = selected($product->get_id(), $product_id, false);
                    echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                }
                ?>
            </select>
            <br><br>

            <input type="submit" name="video_update" class="button button-primary" value="Update Video">
        </form>
    </div>
    <?php
}
