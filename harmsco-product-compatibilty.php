<?php
/*
Plugin Name: Harmsco Product Compatibility
Plugin URI: https://github.com/amazeing-technologies/harmsco-product-compatibility
Description: Displays compatible and family products for each housing using ACF fields and Elementor. Now with GitHub auto-update support.
Version: 1.2
Author: amazeing technolgies
Author URI:
Update URI: https://github.com/amazeing-technologies/harmsco-product-compatibility
*/

if (!defined('ABSPATH')) exit; // Security check

// ------------------------------------------------------------
// Shortcode: [product_compatibility_sections]
// ------------------------------------------------------------
add_shortcode('product_compatibility_sections', function () {
    if (!is_singular('product')) return '';

    $family_products     = get_field('family_compatible_products', false, false) ?: [];
    $compatible_products = get_field('compatible_products') ?: [];

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
                <h3 class="compatible-section-title">Compatible Products</h3>
                <?php echo $render_grid($compatible_products); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});

// ------------------------------------------------------------
// Ensure Elementor, TheGem & Widgets process shortcodes
// ------------------------------------------------------------
add_action('init', function () {
    add_filter('widget_text', 'do_shortcode');
    add_filter('widget_custom_html_content', 'do_shortcode');
    add_filter('the_content', 'do_shortcode', 11);
    add_filter('elementor/frontend/the_content', 'do_shortcode', 11);
    add_filter('elementor/widget/render_content', 'do_shortcode', 11);
});

// ------------------------------------------------------------
// GitHub Auto-Update System
// ------------------------------------------------------------
add_action('admin_init', function () {
    // Only run in admin
    if (!current_user_can('update_plugins')) return;

    $plugin_file = plugin_basename(__FILE__);
    $plugin_slug = dirname($plugin_file);

    // GitHub Repo Details
    $github_user = 'amazeing-technologies';
    $github_repo = 'harmsco-product-compatibility';

    // Check for updates from GitHub
    add_filter('pre_set_site_transient_update_plugins', function ($transient) use ($plugin_file, $github_user, $github_repo) {
        if (empty($transient->checked)) return $transient;

        $remote = wp_remote_get("https://api.github.com/repos/$github_user/$github_repo/releases/latest");
        if (is_wp_error($remote)) return $transient;

        $release = json_decode(wp_remote_retrieve_body($remote));
        if (!$release || empty($release->tag_name)) return $transient;

        $latest_version = ltrim($release->tag_name, 'v');
        $current_version = get_file_data(__FILE__, ['Version' => 'Version'])['Version'];

        if (version_compare($latest_version, $current_version, '>')) {
            $plugin_info = (object)[
                'slug' => $plugin_slug,
                'new_version' => $latest_version,
                'url' => "https://github.com/$github_user/$github_repo",
                'package' => $release->zipball_url
            ];
            $transient->response[$plugin_file] = $plugin_info;
        }
        return $transient;
    });

    // Optional: add admin notice confirming updater is active
    add_action('admin_notices', function () use ($github_user, $github_repo) {
        echo '<div class="notice notice-success is-dismissible">
            <p>✅ <strong>Harmsco Product Compatibility</strong> is connected to GitHub (<em>' . esc_html($github_user . '/' . $github_repo) . '</em>) and will auto-update when a new release is tagged.</p>
        </div>';
    });
});
