<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the public-facing side of the site.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/public
 */
class WCIC_Public {

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
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, WCIC_PLUGIN_URL . 'assets/css/wcic-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, WCIC_PLUGIN_URL . 'assets/js/wcic-public.js', array('jquery'), $this->version, true);
        
        // Pass data to the script
        wp_localize_script($this->plugin_name, 'wcic_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcic-public-nonce'),
            'chatbot_title' => get_option('wcic_chatbot_title', 'Store Assistant'),
            'welcome_message' => get_option('wcic_chatbot_welcome_message', 'Hello! I\'m your personal shopping assistant. How can I help you today?'),
            'i18n' => array(
                'send' => __('Send', 'wc-intelligent-chatbot'),
                'typing' => __('Typing...', 'wc-intelligent-chatbot'),
                'error' => __('Sorry, there was an error processing your request. Please try again.', 'wc-intelligent-chatbot'),
                'placeholder' => __('Type your message here...', 'wc-intelligent-chatbot'),
            )
        ));
    }

    /**
     * Render the chatbot HTML in the footer.
     *
     * @since    1.0.0
     */
    public function render_chatbot() {
        // Check if chatbot is enabled
        if (get_option('wcic_chatbot_enabled', 'yes') !== 'yes') {
            return;
        }
        
        // Check display settings
        $display_on = get_option('wcic_display_on_pages', 'all');
        
        if ($display_on === 'shop' && !is_shop() && !is_product() && !is_product_category() && !is_product_tag() && !is_cart() && !is_checkout()) {
            return;
        }
        
        if ($display_on === 'custom') {
            $excluded_pages = get_option('wcic_excluded_pages', '');
            $excluded_ids = array_map('trim', explode(',', $excluded_pages));
            
            if (is_page() && in_array(get_the_ID(), $excluded_ids)) {
                return;
            }
        }
        
        // Check device settings
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $is_mobile = wp_is_mobile();
        $is_tablet = preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent);
        
        if ($is_mobile && get_option('wcic_mobile_enabled', 'yes') !== 'yes') {
            return;
        }
        
        if ($is_tablet && get_option('wcic_tablet_enabled', 'yes') !== 'yes') {
            return;
        }
        
        if (!$is_mobile && !$is_tablet && get_option('wcic_desktop_enabled', 'yes') !== 'yes') {
            return;
        }
        
        // Get chatbot position
        $position = get_option('wcic_chatbot_position', 'bottom-right');
        
        // Get colors
        $primary_color = get_option('wcic_chatbot_primary_color', '#0073aa');
        $secondary_color = get_option('wcic_chatbot_secondary_color', '#f7f7f7');
        $text_color = get_option('wcic_chatbot_text_color', '#333333');
        $button_color = get_option('wcic_chatbot_button_color', '#0073aa');
        $button_text_color = get_option('wcic_chatbot_button_text_color', '#ffffff');
        
        // Include the chatbot template
        include_once WCIC_PLUGIN_DIR . 'includes/public/partials/wcic-public-chatbot.php';
    }

    /**
     * Process a message from the chatbot.
     *
     * @since    1.0.0
     */
    public function process_message() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcic-public-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wc-intelligent-chatbot')));
        }
        
        // Get message
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        
        if (empty($message)) {
            wp_send_json_error(array('message' => __('Message cannot be empty.', 'wc-intelligent-chatbot')));
        }
        
        // Get session ID
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        
        if (empty($session_id)) {
            // Generate a new session ID if none provided
            $session_id = md5(uniqid() . time());
        }
        
        // Get user ID if logged in
        $user_id = get_current_user_id();
        
        // Process the message with AI
        $ai_handler = new WCIC_AI_Handler();
        $response = $ai_handler->process_message($message, $session_id);
        
        if (!$response) {
            wp_send_json_error(array('message' => __('Error processing message.', 'wc-intelligent-chatbot')));
        }
        
        // Save the conversation
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'wcic_conversations',
            array(
                'session_id' => $session_id,
                'user_id' => $user_id,
                'message' => $message,
                'response' => $response['message'],
                'recommendations' => !empty($response['recommendations']) ? json_encode($response['recommendations']) : null,
                'created_at' => current_time('mysql')
            )
        );
        
        // Return the response
        wp_send_json_success(array(
            'message' => $response['message'],
            'recommendations' => $response['recommendations'],
            'session_id' => $session_id
        ));
    }
}