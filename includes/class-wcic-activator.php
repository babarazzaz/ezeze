<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */
class WCIC_Activator {

    /**
     * Activate the plugin.
     *
     * Create necessary database tables and set default options.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        
        // Create database tables if needed
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing chatbot conversations
        $table_name = $wpdb->prefix . 'wcic_conversations';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(50) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            message text NOT NULL,
            response text NOT NULL,
            recommendations text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Table for storing product index data
        $table_name_index = $wpdb->prefix . 'wcic_product_index';
        
        $sql .= "CREATE TABLE $table_name_index (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            product_data longtext NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY product_id (product_id)
        ) $charset_collate;";
        
        // Table for storing page content index
        $table_name_pages = $wpdb->prefix . 'wcic_page_index';
        
        $sql .= "CREATE TABLE $table_name_pages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_type varchar(20) NOT NULL,
            content_data longtext NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_options = array(
            'chatbot_enabled' => 'yes',
            'chatbot_title' => 'Store Assistant',
            'chatbot_welcome_message' => 'Hello! I\'m your personal shopping assistant. How can I help you today?',
            'chatbot_position' => 'bottom-right',
            'chatbot_primary_color' => '#0073aa',
            'chatbot_secondary_color' => '#f7f7f7',
            'chatbot_text_color' => '#333333',
            'chatbot_button_color' => '#0073aa',
            'chatbot_button_text_color' => '#ffffff',
            'indexing_frequency' => 'daily',
            'recommendation_priority' => 'relevance', // Options: relevance, newest, price_low, price_high, sales
            'max_recommendations' => 3,
            'openai_api_key' => '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',
            'openai_model' => 'gpt-3.5-turbo',
            'enable_product_recommendations' => 'yes',
            'enable_page_recommendations' => 'yes',
            'display_on_pages' => array('all'),
            'excluded_pages' => array(),
            'mobile_enabled' => 'yes',
            'desktop_enabled' => 'yes',
            'tablet_enabled' => 'yes',
        );
        
        foreach ($default_options as $option_name => $option_value) {
            add_option('wcic_' . $option_name, $option_value);
        }
        
        // Schedule initial product indexing
        if (!wp_next_scheduled('wcic_index_products')) {
            wp_schedule_event(time(), 'daily', 'wcic_index_products');
        }
        
        // Schedule initial page indexing
        if (!wp_next_scheduled('wcic_index_pages')) {
            wp_schedule_event(time() + 3600, 'daily', 'wcic_index_pages');
        }
    }
}