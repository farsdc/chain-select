<?php if (!defined('ABSPATH')) {
    exit;
} ?>

<div class="product-list">
    <?php while ($products->have_posts()):
        $products->the_post(); ?>
        <div class="product-item">
            <div class="product-thumbnail">
                <?php if (has_post_thumbnail()): ?>
                    <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>">
                <?php endif; ?>
            </div>
            <div class="product-info">
                <h2 class="product-title"><?php the_title(); ?></h2>
                <p class="product-description">
                    <?php echo wp_trim_words(get_the_content(), 100, '...'); ?>
                </p>
                <p class="product-price">
                    <?php echo wc_price(get_post_meta(get_the_ID(), '_price', true)); ?>
                </p>
                <p class="product-vendor">
                    <?php
                    $vendor_id = get_post_meta(get_the_ID(), '_vendor_id', true);
                    if ($vendor_id) {
                        $vendor = get_user_by('id', $vendor_id);
                        if ($vendor) {
                            echo __('Vendor:', 'chain-select') . ' ' . esc_html($vendor->display_name);
                        }
                    }
                    ?>
                </p>
                <a href="<?php echo esc_url(add_query_arg('add-to-cart', get_the_ID(), wc_get_cart_url())); ?>"
                    class="button add-to-cart">
                    <?php _e('Add to Cart', 'chain-select'); ?>
                </a>
            </div>
        </div>
    <?php endwhile;
    wp_reset_postdata(); ?>
</div>