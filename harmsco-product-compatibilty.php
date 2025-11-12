<?php
/*
Plugin Name: Harmsco Product Compatibility
Plugin URI: https://github.com/amazeing-technologies/harmsco-product-compatibility
Description: Displays compatible and family products for each housing using ACF fields.
Version: 1.1
Author: Amazeing Technologies
Update URI: https://github.com/amazeing-technologies/harmsco-product-compatibility
*/


if (!defined('ABSPATH')) exit; // safety check

// -------------------------------
// Shortcode: [product_compatibility_sections]
// -------------------------------
add_shortcode('product_compatibility_sections', function () {
    if (!is_singular('product')) return '';

    // Get ACF relationship fields
    $family_products     = get_field('family_compatible_products', false, false) ?: [];
    $compatible_products = get_field('compatible_products') ?: [];

    // Helper: product grid renderer
    $render_grid = function ($items) {
        if (empty($items)) return '';
        ob_start(); ?>
        <div class="compatible-products-grid">
            <?php foreach ($items as $item):
                $id    = is_object($item) ? $item->ID : $item;
                $thumb = get_the_post_thumbnail($id, 'medium');
                $title = get_the_title($id);
                $link  = get_permalink($id); ?>
                <div class="compatible-product-card">
                    <a href="<?php echo esc_url($link); ?>">
                        <div class="compatible-product-thumb"><?php echo $thumb; ?></div>
                        <div class="compatible-product-divider"></div>
                        <h4 class="compatible-product-title"><?php echo esc_html($title); ?></h4>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    };

    ob_start(); ?>
    <div class="compatible-section-wrapper">

        <?php if (!empty($family_products)) : ?>
            <div class="compatible-products-wrapper family-group">
                <h3 class="compatible-section-title">Product Series</h3>
                <?php echo $render_grid($family_products); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($compatible_products)) : ?>
            <div class="compatible-products-wrapper functional-group">
                <h3 class="compatible-section-title">Compatible Product Series</h3>
                <?php echo $render_grid($compatible_products); ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
});

// -------------------------------
// Force Elementor & TheGem to run shortcodes everywhere
// -------------------------------
add_action('init', function () {
    add_filter('widget_text', 'do_shortcode');
    add_filter('widget_custom_html_content', 'do_shortcode');
    add_filter('the_content', 'do_shortcode', 11);
    add_filter('elementor/frontend/the_content', 'do_shortcode', 11);
    add_filter('elementor/widget/render_content', 'do_shortcode', 11);
});
