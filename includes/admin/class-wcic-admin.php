<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/admin
 */
class WCIC_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, WCIC_PLUGIN_URL . 'assets/css/wcic-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, WCIC_PLUGIN_URL . 'assets/js/wcic-admin.js', array('jquery', 'wp-color-picker'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'wcic_admin_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcic-admin-nonce'),
            'i18n' => array(
                'indexing_products' => __('Indexing products...', 'wc-intelligent-chatbot'),
                'indexing_pages' => __('Indexing pages...', 'wc-intelligent-chatbot'),
                'indexing_complete' => __('Indexing complete!', 'wc-intelligent-chatbot'),
                'indexing_error' => __('Error during indexing. Please check server logs.', 'wc-intelligent-chatbot'),
                'testing_connection' => __('Testing AI connection...', 'wc-intelligent-chatbot'),
                'connection_success' => __('Connection successful!', 'wc-intelligent-chatbot'),
                'connection_error' => __('Connection failed. Please check your API key.', 'wc-intelligent-chatbot'),
            )
        ));
    }

    /**
     * Add menu items to the admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WC Intelligent Chatbot', 'wc-intelligent-chatbot'),
            __('Chatbot', 'wc-intelligent-chatbot'),
            'manage_options',
            'wc-intelligent-chatbot',
            array($this, 'display_settings_page'),
            'dashicons-format-chat',
            56
        );
        
        add_submenu_page(
            'wc-intelligent-chatbot',
            __('Settings', 'wc-intelligent-chatbot'),
            __('Settings', 'wc-intelligent-chatbot'),
            'manage_options',
            'wc-intelligent-chatbot',
            array($this, 'display_settings_page')
        );
        
        add_submenu_page(
            'wc-intelligent-chatbot',
            __('Indexing', 'wc-intelligent-chatbot'),
            __('Indexing', 'wc-intelligent-chatbot'),
            'manage_options',
            'wc-intelligent-chatbot-indexing',
            array($this, 'display_indexing_page')
        );
        
        add_submenu_page(
            'wc-intelligent-chatbot',
            __('Conversations', 'wc-intelligent-chatbot'),
            __('Conversations', 'wc-intelligent-chatbot'),
            'manage_options',
            'wc-intelligent-chatbot-conversations',
            array($this, 'display_conversations_page')
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // General Settings
        register_setting('wcic_general_settings', 'wcic_chatbot_enabled');
        register_setting('wcic_general_settings', 'wcic_chatbot_title');
        register_setting('wcic_general_settings', 'wcic_chatbot_welcome_message');
        register_setting('wcic_general_settings', 'wcic_chatbot_position');
        register_setting('wcic_general_settings', 'wcic_display_on_pages');
        register_setting('wcic_general_settings', 'wcic_excluded_pages');
        register_setting('wcic_general_settings', 'wcic_mobile_enabled');
        register_setting('wcic_general_settings', 'wcic_desktop_enabled');
        register_setting('wcic_general_settings', 'wcic_tablet_enabled');
        
        // Appearance Settings
        register_setting('wcic_appearance_settings', 'wcic_chatbot_primary_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_secondary_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_text_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_button_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_button_text_color');
        
        // AI Settings
        register_setting('wcic_ai_settings', 'wcic_openai_api_key');
        register_setting('wcic_ai_settings', 'wcic_openai_model');
        
        // Recommendation Settings
        register_setting('wcic_recommendation_settings', 'wcic_enable_product_recommendations');
        register_setting('wcic_recommendation_settings', 'wcic_enable_page_recommendations');
        register_setting('wcic_recommendation_settings', 'wcic_recommendation_priority');
        register_setting('wcic_recommendation_settings', 'wcic_max_recommendations');
        
        // Indexing Settings
        register_setting('wcic_indexing_settings', 'wcic_indexing_frequency');
    }

    /**
     * Add action links to the plugins page.
     *
     * @since    1.0.0
     * @param    array    $links    The existing action links.
     * @return   array              The modified action links.
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-intelligent-chatbot') . '">' . __('Settings', 'wc-intelligent-chatbot') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once WCIC_PLUGIN_DIR . 'includes/admin/partials/wcic-admin-settings.php';
    }

    /**
     * Display the indexing page.
     *
     * @since    1.0.0
     */
    public function display_indexing_page() {
        include_once WCIC_PLUGIN_DIR . 'includes/admin/partials/wcic-admin-indexing.php';
    }

    /**
     * Display the conversations page.
     *
     * @since    1.0.0
     */
    public function display_conversations_page() {
        include_once WCIC_PLUGIN_DIR . 'includes/admin/partials/wcic-admin-conversations.php';
    }

    /**
     * Handle manual product indexing via AJAX.
     *
     * @since    1.0.0
     */
    public function manual_index_products() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcic-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wc-intelligent-chatbot')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wc-intelligent-chatbot')));
        }
        
        // Perform indexing
        $indexer = new WCIC_Product_Indexer();
        $result = $indexer->index_all_products();
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Product indexing completed successfully.', 'wc-intelligent-chatbot'),
                'count' => $result
            ));
        } else {
            wp_send_json_error(array('message' => __('Error during product indexing.', 'wc-intelligent-chatbot')));
        }
    }

    /**
     * Handle manual page indexing via AJAX.
     *
     * @since    1.0.0
     */
    public function manual_index_pages() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcic-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wc-intelligent-chatbot')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wc-intelligent-chatbot')));
        }
        
        // Perform indexing
        $indexer = new WCIC_Page_Indexer();
        $result = $indexer->index_all_pages();
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Page indexing completed successfully.', 'wc-intelligent-chatbot'),
                'count' => $result
            ));
        } else {
            wp_send_json_error(array('message' => __('Error during page indexing.', 'wc-intelligent-chatbot')));
        }
    }

    /**
     * Test AI connection via AJAX.
     *
     * @since    1.0.0
     */
    public function test_ai_connection() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcic-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wc-intelligent-chatbot')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wc-intelligent-chatbot')));
        }
        
        // Get API key
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : get_option('wcic_openai_api_key', '');
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key is required.', 'wc-intelligent-chatbot')));
        }
        
        // Test connection
        $ai_handler = new WCIC_AI_Handler();
        $result = $ai_handler->test_connection($api_key);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Connection successful!', 'wc-intelligent-chatbot')));
        } else {
            wp_send_json_error(array('message' => __('Connection failed. Please check your API key.', 'wc-intelligent-chatbot')));
        }
    }
}