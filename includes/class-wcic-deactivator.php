<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */
class WCIC_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clear scheduled events and perform cleanup.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wcic_index_products');
        wp_clear_scheduled_hook('wcic_index_pages');
        
        // We don't delete tables or options on deactivation
        // This ensures data is preserved if the plugin is reactivated
    }
}