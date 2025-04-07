<?php
/**
 * Admin indexing page for the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get indexing stats
global $wpdb;
$product_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wcic_product_index");
$page_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wcic_page_index");

// Get last indexing time
$last_product_index = $wpdb->get_var("SELECT MAX(last_updated) FROM {$wpdb->prefix}wcic_product_index");
$last_page_index = $wpdb->get_var("SELECT MAX(last_updated) FROM {$wpdb->prefix}wcic_page_index");

// Get total products and pages
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$total_pages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('page', 'post') AND post_status = 'publish'");
?>

<div class="wrap wcic-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wcic-indexing-stats">
        <div class="wcic-stat-box">
            <h3><?php _e('Product Indexing', 'wc-intelligent-chatbot'); ?></h3>
            <p><?php printf(__('Indexed Products: %d / %d', 'wc-intelligent-chatbot'), $product_count, $total_products); ?></p>
            <?php if ($last_product_index): ?>
                <p><?php printf(__('Last Updated: %s', 'wc-intelligent-chatbot'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_product_index))); ?></p>
            <?php else: ?>
                <p><?php _e('Not indexed yet', 'wc-intelligent-chatbot'); ?></p>
            <?php endif; ?>
            <button type="button" class="button button-primary" id="wcic-index-products"><?php _e('Index All Products', 'wc-intelligent-chatbot'); ?></button>
            <span id="wcic-product-indexing-result" style="margin-left: 10px;"></span>
        </div>
        
        <div class="wcic-stat-box">
            <h3><?php _e('Page Indexing', 'wc-intelligent-chatbot'); ?></h3>
            <p><?php printf(__('Indexed Pages: %d / %d', 'wc-intelligent-chatbot'), $page_count, $total_pages); ?></p>
            <?php if ($last_page_index): ?>
                <p><?php printf(__('Last Updated: %s', 'wc-intelligent-chatbot'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_page_index))); ?></p>
            <?php else: ?>
                <p><?php _e('Not indexed yet', 'wc-intelligent-chatbot'); ?></p>
            <?php endif; ?>
            <button type="button" class="button button-primary" id="wcic-index-pages"><?php _e('Index All Pages', 'wc-intelligent-chatbot'); ?></button>
            <span id="wcic-page-indexing-result" style="margin-left: 10px;"></span>
        </div>
    </div>
    
    <div class="wcic-indexing-info">
        <h3><?php _e('Indexing Information', 'wc-intelligent-chatbot'); ?></h3>
        <p><?php _e('The indexing process analyzes your products and pages to make them searchable by the chatbot. This allows the AI to provide accurate information and recommendations to your customers.', 'wc-intelligent-chatbot'); ?></p>
        
        <h4><?php _e('Automatic Indexing', 'wc-intelligent-chatbot'); ?></h4>
        <p><?php printf(__('Automatic indexing is currently set to run %s.', 'wc-intelligent-chatbot'), '<strong>' . esc_html(get_option('wcic_indexing_frequency', 'daily')) . '</strong>'); ?></p>
        <p><?php _e('You can change this frequency in the Settings tab.', 'wc-intelligent-chatbot'); ?></p>
        
        <h4><?php _e('Manual Indexing', 'wc-intelligent-chatbot'); ?></h4>
        <p><?php _e('Use the buttons above to manually trigger indexing. This is useful after adding new products or making significant changes to your site content.', 'wc-intelligent-chatbot'); ?></p>
        
        <h4><?php _e('Indexing on Product/Page Update', 'wc-intelligent-chatbot'); ?></h4>
        <p><?php _e('Individual products and pages are automatically indexed when they are created or updated.', 'wc-intelligent-chatbot'); ?></p>
    </div>
</div>

<style>
.wcic-indexing-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.wcic-stat-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 5px;
    flex: 1;
}

.wcic-indexing-info {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Index products
    $('#wcic-index-products').on('click', function() {
        var button = $(this);
        var result_span = $('#wcic-product-indexing-result');
        
        button.prop('disabled', true);
        result_span.html('<span style="color: blue;"><?php _e('Indexing products...', 'wc-intelligent-chatbot'); ?></span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wcic_manual_index_products',
                nonce: wcic_admin_params.nonce
            },
            success: function(response) {
                button.prop('disabled', false);
                
                if (response.success) {
                    result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                    // Refresh the page after a short delay to update stats
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                }
            },
            error: function() {
                button.prop('disabled', false);
                result_span.html('<span style="color: red;"><?php _e('Error during indexing. Please try again.', 'wc-intelligent-chatbot'); ?></span>');
            }
        });
    });
    
    // Index pages
    $('#wcic-index-pages').on('click', function() {
        var button = $(this);
        var result_span = $('#wcic-page-indexing-result');
        
        button.prop('disabled', true);
        result_span.html('<span style="color: blue;"><?php _e('Indexing pages...', 'wc-intelligent-chatbot'); ?></span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wcic_manual_index_pages',
                nonce: wcic_admin_params.nonce
            },
            success: function(response) {
                button.prop('disabled', false);
                
                if (response.success) {
                    result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                    // Refresh the page after a short delay to update stats
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                }
            },
            error: function() {
                button.prop('disabled', false);
                result_span.html('<span style="color: red;"><?php _e('Error during indexing. Please try again.', 'wc-intelligent-chatbot'); ?></span>');
            }
        });
    });
});
</script>