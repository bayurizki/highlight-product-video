<?php
if (!defined('ABSPATH')) {
    exit;
}

function highlight_product_video_plugin_shortcode($atts) {
    // Set default attributes for the shortcode
    $atts = shortcode_atts(
        [
            'id'         => 0,
            'product_id' => 0,
        ],
        $atts
    );

    // Get the video post by ID
    $video_post = get_post($atts['id']);
    if ($video_post && $video_post->post_type === 'highlight_product_video') {
        // Get the video URL and associated product ID
        $video_url = get_post_meta($video_post->ID, 'video_url', true);
        $video_url_mbl = get_post_meta($video_post->ID, 'video_url_mbl', true);
        $product_id = get_post_meta($video_post->ID, 'product_id', true);
        $product = wc_get_product($product_id);

        if ($video_url && $product) {
            // Start output buffering to collect the HTML
            ob_start();
            ?>
            <div class="highlight-product-video-container">
                <!-- Video Section -->
                    <video autoplay="autoplay" muted="muted" loop="loop" controlsList="nofullscreen" src="<?php echo esc_url($video_url); ?>" class="highlight-product-video video-desktop"></video>
                    <video autoplay="autoplay" muted="muted" loop="loop" controlsList="nofullscreen" src="<?php echo esc_url($video_url_mbl); ?>" class="highlight-product-video video-mobile" id="video-mobile"></video>
                    
                    <!-- Product Info Section -->
                    <div class="custom-product-info">
                        <a href="<?=get_permalink($product->get_id())?>">

                        <h3 class="custom-product-name"><?php echo esc_html($product->get_name()); ?></h3>
                        <p class="custom-product-price"><?php echo wc_price($product->get_price()); ?></p>
                        <img src="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'single-post-thumbnail' )[0] ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="custom-product-image">
                        </a>
                    </div>
            </div>
            <script type="text/javascript">
                document.getElementById('video-mobile').play();
            </script>

            <?php
            // Get and return the content from the buffer
            return ob_get_clean();
        }
    }

    // Return a message if the video or product is invalid
    return '<p>Invalid video or product.</p>';
}
add_shortcode('show_video', 'highlight_product_video_plugin_shortcode');

