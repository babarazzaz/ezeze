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
            __('Ezeze Intelligent Chatbot', 'wc-intelligent-chatbot'),
            __('Ezeze Chatbot', 'wc-intelligent-chatbot'),
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
        register_setting('wcic_general_settings', 'wcic_chatbot_enabled', array($this, 'sanitize_checkbox'));
        register_setting('wcic_general_settings', 'wcic_chatbot_title', 'sanitize_text_field');
        register_setting('wcic_general_settings', 'wcic_chatbot_welcome_message', 'sanitize_textarea_field');
        register_setting('wcic_general_settings', 'wcic_chatbot_position', 'sanitize_text_field');
        register_setting('wcic_general_settings', 'wcic_display_on_pages', array($this, 'sanitize_array'));
        register_setting('wcic_general_settings', 'wcic_excluded_pages', array($this, 'sanitize_array'));
        register_setting('wcic_general_settings', 'wcic_mobile_enabled', array($this, 'sanitize_checkbox'));
        register_setting('wcic_general_settings', 'wcic_desktop_enabled', array($this, 'sanitize_checkbox'));
        register_setting('wcic_general_settings', 'wcic_tablet_enabled', array($this, 'sanitize_checkbox'));
        
        // Appearance Settings
        register_setting('wcic_appearance_settings', 'wcic_chatbot_primary_color', 'sanitize_hex_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_secondary_color', 'sanitize_hex_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_text_color', 'sanitize_hex_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_button_color', 'sanitize_hex_color');
        register_setting('wcic_appearance_settings', 'wcic_chatbot_button_text_color', 'sanitize_hex_color');
        
        // AI Settings
        register_setting('wcic_ai_settings', 'wcic_openai_api_key', 'sanitize_text_field');
        register_setting('wcic_ai_settings', 'wcic_openai_model', 'sanitize_text_field');
        
        // Recommendation Settings
        register_setting('wcic_recommendation_settings', 'wcic_enable_product_recommendations', array($this, 'sanitize_checkbox'));
        register_setting('wcic_recommendation_settings', 'wcic_enable_page_recommendations', array($this, 'sanitize_checkbox'));
        register_setting('wcic_recommendation_settings', 'wcic_recommendation_priority', 'sanitize_text_field');
        register_setting('wcic_recommendation_settings', 'wcic_max_recommendations', 'absint');
        
        // Indexing Settings
        register_setting('wcic_indexing_settings', 'wcic_indexing_frequency', 'sanitize_text_field');
        
        // Advanced Features
        register_setting('wcic_advanced_features', 'wcic_enable_product_suggestions', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_quick_replies', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_voice_input', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_file_attachments', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_order_tracking', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_product_comparison', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_discount_codes', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_cart_management', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_multilingual', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_feedback', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_conversation_history', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_proactive_messages', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_typing_indicator', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_read_receipts', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_emoji', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_avatar', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_avatar_url', 'esc_url_raw');
        register_setting('wcic_advanced_features', 'wcic_enable_user_auth', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_analytics', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_enable_offline_mode', array($this, 'sanitize_checkbox'));
        register_setting('wcic_advanced_features', 'wcic_chatbot_personality', 'sanitize_text_field');
        
        // Add action to save settings
        add_action('admin_init', array($this, 'save_settings'));
        
        // Add action to handle form submission
        add_action('admin_post_save_ezeze_chatbot_settings', array($this, 'handle_form_submission'));
    }
    
    /**
     * Save settings when form is submitted.
     *
     * @since    1.0.1
     */
    public function save_settings() {
        if (isset($_POST['wcic_openai_api_key'])) {
            update_option('wcic_openai_api_key', sanitize_text_field($_POST['wcic_openai_api_key']));
        }
        
        if (isset($_POST['wcic_openai_model'])) {
            update_option('wcic_openai_model', sanitize_text_field($_POST['wcic_openai_model']));
        }
        
        // Save other settings as needed
        $this->save_setting_if_set('wcic_chatbot_enabled', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_chatbot_title', 'sanitize_text_field');
        $this->save_setting_if_set('wcic_chatbot_welcome_message', 'sanitize_textarea_field');
        $this->save_setting_if_set('wcic_chatbot_position', 'sanitize_text_field');
        $this->save_setting_if_set('wcic_chatbot_primary_color', 'sanitize_hex_color');
        $this->save_setting_if_set('wcic_chatbot_secondary_color', 'sanitize_hex_color');
        $this->save_setting_if_set('wcic_chatbot_text_color', 'sanitize_hex_color');
        $this->save_setting_if_set('wcic_chatbot_button_color', 'sanitize_hex_color');
        $this->save_setting_if_set('wcic_chatbot_button_text_color', 'sanitize_hex_color');
        $this->save_setting_if_set('wcic_enable_product_recommendations', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_page_recommendations', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_recommendation_priority', 'sanitize_text_field');
        $this->save_setting_if_set('wcic_max_recommendations', 'absint');
        $this->save_setting_if_set('wcic_indexing_frequency', 'sanitize_text_field');
        
        // Save advanced features settings
        $this->save_setting_if_set('wcic_enable_product_suggestions', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_quick_replies', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_voice_input', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_file_attachments', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_order_tracking', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_product_comparison', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_discount_codes', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_cart_management', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_multilingual', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_feedback', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_conversation_history', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_proactive_messages', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_typing_indicator', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_read_receipts', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_emoji', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_avatar', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_avatar_url', 'esc_url_raw');
        $this->save_setting_if_set('wcic_enable_user_auth', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_analytics', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_enable_offline_mode', array($this, 'sanitize_checkbox'));
        $this->save_setting_if_set('wcic_chatbot_personality', 'sanitize_text_field');
    }
    
    /**
     * Handle form submission from admin-post.php.
     *
     * @since    1.0.1
     */
    public function handle_form_submission() {
        // Check if user has proper permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wc-intelligent-chatbot'));
        }
        
        // Verify nonce
        if (!isset($_POST['ezeze_chatbot_nonce']) || !wp_verify_nonce($_POST['ezeze_chatbot_nonce'], 'ezeze_chatbot_settings_action')) {
            wp_die(__('Security check failed. Please try again.', 'wc-intelligent-chatbot'));
        }
        
        // Process checkbox fields - set them to 'no' if not present in POST data
        $checkbox_fields = $this->get_checkbox_fields();
        foreach ($checkbox_fields as $field) {
            if (!isset($_POST[$field])) {
                update_option($field, 'no');
            }
        }
        
        // Process all settings directly from $_POST
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'wcic_') === 0) {
                if (is_array($value)) {
                    update_option($key, $this->sanitize_array($value));
                } else if ($this->is_checkbox_field($key)) {
                    update_option($key, $value === 'yes' ? 'yes' : 'no');
                } else {
                    update_option($key, sanitize_text_field($value));
                }
            }
        }
        
        // Redirect back to the settings page with a success message
        wp_redirect(add_query_arg(array('page' => 'wc-intelligent-chatbot', 'settings-updated' => 'true'), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Get all checkbox fields.
     *
     * @since    1.0.1
     * @return   array    Array of checkbox field names.
     */
    private function get_checkbox_fields() {
        return array(
            'wcic_chatbot_enabled',
            'wcic_mobile_enabled',
            'wcic_desktop_enabled',
            'wcic_tablet_enabled',
            'wcic_enable_product_recommendations',
            'wcic_enable_page_recommendations',
            'wcic_enable_product_suggestions',
            'wcic_enable_quick_replies',
            'wcic_enable_voice_input',
            'wcic_enable_file_attachments',
            'wcic_enable_order_tracking',
            'wcic_enable_product_comparison',
            'wcic_enable_discount_codes',
            'wcic_enable_cart_management',
            'wcic_enable_multilingual',
            'wcic_enable_feedback',
            'wcic_enable_conversation_history',
            'wcic_enable_proactive_messages',
            'wcic_enable_typing_indicator',
            'wcic_enable_read_receipts',
            'wcic_enable_emoji',
            'wcic_enable_avatar',
            'wcic_enable_user_auth',
            'wcic_enable_analytics',
            'wcic_enable_offline_mode',
            'wcic_product_suggestions_show_price',
            'wcic_product_suggestions_show_image',
            'wcic_product_suggestions_add_to_cart'
        );
    }
    
    /**
     * Check if a field is a checkbox field.
     *
     * @since    1.0.1
     * @param    string    $field_name    The field name.
     * @return   bool                     Whether the field is a checkbox field.
     */
    private function is_checkbox_field($field_name) {
        $checkbox_fields = array(
            'wcic_chatbot_enabled',
            'wcic_mobile_enabled',
            'wcic_desktop_enabled',
            'wcic_tablet_enabled',
            'wcic_enable_product_recommendations',
            'wcic_enable_page_recommendations',
            'wcic_enable_product_suggestions',
            'wcic_enable_quick_replies',
            'wcic_enable_voice_input',
            'wcic_enable_file_attachments',
            'wcic_enable_order_tracking',
            'wcic_enable_product_comparison',
            'wcic_enable_discount_codes',
            'wcic_enable_cart_management',
            'wcic_enable_multilingual',
            'wcic_enable_feedback',
            'wcic_enable_conversation_history',
            'wcic_enable_proactive_messages',
            'wcic_enable_typing_indicator',
            'wcic_enable_read_receipts',
            'wcic_enable_emoji',
            'wcic_enable_avatar',
            'wcic_enable_user_auth',
            'wcic_enable_analytics',
            'wcic_enable_offline_mode'
        );
        
        return in_array($field_name, $checkbox_fields);
    }
    
    /**
     * Helper function to save a setting if it's set in the POST data.
     *
     * @since    1.0.1
     * @param    string    $option_name    The name of the option to save.
     * @param    callable  $sanitize_callback    The sanitization callback to use.
     */
    private function save_setting_if_set($option_name, $sanitize_callback) {
        if (isset($_POST[$option_name])) {
            $value = $_POST[$option_name];
            
            if (is_callable($sanitize_callback)) {
                $value = call_user_func($sanitize_callback, $value);
            }
            
            update_option($option_name, $value);
        }
    }
    
    /**
     * Sanitize checkbox values.
     *
     * @since    1.0.1
     * @param    mixed    $input    The input to sanitize.
     * @return   string             'yes' if checked, 'no' if not.
     */
    public function sanitize_checkbox($input) {
        return ($input === 'yes' || $input === true || $input === '1' || $input === 1) ? 'yes' : 'no';
    }
    
    /**
     * Sanitize array values.
     *
     * @since    1.0.1
     * @param    mixed    $input    The input to sanitize.
     * @return   array              The sanitized array.
     */
    public function sanitize_array($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized = array();
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_array($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
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