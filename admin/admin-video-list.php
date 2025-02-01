<?php
if (!defined('ABSPATH')) {
    exit;
}

// Query all saved videos
$args = [
    'post_type'      => 'custom_video',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];

$video_posts = new WP_Query($args);
?>

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
                        <code>[show_video id="<?php echo esc_attr(get_the_ID()); ?>"]</code>
                    </td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=edit-custom-video&id=' . get_the_ID()); ?>" class="button">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No videos saved yet.</p>
<?php endif; ?>
