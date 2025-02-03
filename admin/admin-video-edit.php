<?php
if (!defined('ABSPATH')) {
    exit;
}

$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$video_post = get_post($video_id);

if (!$video_post || $video_post->post_type !== 'highlight_video') {
    echo '<div class="notice notice-error"><p>Invalid video ID.</p></div>';
    return;
}

// Handle form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_update'])) {
    $video_url = isset($_POST['video_url']) ? esc_url_raw(wp_unslash($_POST['video_url'])) : '';
    $video_url_mbl = isset($_POST['video_url_mbl']) ? esc_url_raw(wp_unslash($_POST['video_url_mbl'])) : '';
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($video_url && $product_id) {
        $updated = wp_update_post([
            'ID' => $video_id,
            'meta_input' => [
                'video_url' => wp_unslash($video_url),
                'video_url_mbl' => wp_unslash($video_url_mbl),
                'product_id' => $product_id,
            ],
        ]);

        if ($updated) {
            echo '<div class="notice notice-success is-dismissible"><p>Video updated successfully!</p></div>';
            wp_redirect(admin_url('admin.php?page=highlight-product-video'));
            exit;
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
            echo '<option value="' . esc_attr($product->get_id()) . '" ' . esc_attr($selected) . '>' . esc_html($product->get_name()) . '</option>';
        }
        ?>
    </select>
    <br><br>

    <input type="submit" name="video_update" class="button button-primary" value="Update Video">
</form>
