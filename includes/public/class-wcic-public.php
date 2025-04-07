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
            'enable_product_suggestions' => get_option('wcic_enable_product_suggestions', 'yes'),
            'enable_quick_replies' => get_option('wcic_enable_quick_replies', 'yes'),
            'enable_voice_input' => get_option('wcic_enable_voice_input', 'no'),
            'enable_file_attachments' => get_option('wcic_enable_file_attachments', 'no'),
            'enable_order_tracking' => get_option('wcic_enable_order_tracking', 'yes'),
            'enable_cart_management' => get_option('wcic_enable_cart_management', 'yes'),
            'enable_emoji' => get_option('wcic_enable_emoji', 'yes'),
            'enable_typing_indicator' => get_option('wcic_enable_typing_indicator', 'yes'),
            'chatbot_personality' => get_option('wcic_chatbot_personality', 'friendly'),
            'i18n' => array(
                'send' => __('Send', 'wc-intelligent-chatbot'),
                'typing' => __('Typing...', 'wc-intelligent-chatbot'),
                'error' => __('Sorry, there was an error processing your request. Please try again.', 'wc-intelligent-chatbot'),
                'placeholder' => __('Type your message here...', 'wc-intelligent-chatbot'),
                'product_suggestions' => __('You might be interested in these products:', 'wc-intelligent-chatbot'),
                'quick_replies' => __('Quick replies:', 'wc-intelligent-chatbot'),
                'view_product' => __('View Product', 'wc-intelligent-chatbot'),
                'add_to_cart' => __('Add to Cart', 'wc-intelligent-chatbot'),
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
        
        // Check if we should show product suggestions
        $show_suggestions = isset($_POST['show_suggestions']) && $_POST['show_suggestions'] === 'yes';
        
        // Process the message with AI
        $ai_handler = new WCIC_AI_Handler();
        $response = $ai_handler->process_message($message, $session_id);
        
        if (!$response) {
            wp_send_json_error(array('message' => __('Error processing message.', 'wc-intelligent-chatbot')));
        }
        
        // Get product suggestions if enabled
        $product_suggestions = array();
        if ($show_suggestions && get_option('wcic_enable_product_suggestions', 'yes') === 'yes') {
            $product_suggestions = $this->get_product_suggestions($message);
        }
        
        // Generate quick replies based on the context
        $quick_replies = array();
        if (get_option('wcic_enable_quick_replies', 'yes') === 'yes') {
            $quick_replies = $this->generate_quick_replies($message, $response['message']);
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
            'product_suggestions' => $product_suggestions,
            'quick_replies' => $quick_replies,
            'session_id' => $session_id
        ));
    }
    
    /**
     * Get product suggestions based on the user message.
     *
     * @since    1.0.1
     * @param    string    $message    The user message.
     * @return   array                 Array of product suggestions.
     */
    private function get_product_suggestions($message) {
        // Extract keywords from the message
        $keywords = $this->extract_keywords($message);
        
        if (empty($keywords)) {
            return array();
        }
        
        // Query products based on keywords
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            's' => implode(' ', $keywords),
            'orderby' => 'relevance',
        );
        
        $products_query = new WP_Query($args);
        $suggestions = array();
        
        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {
                $products_query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                
                if (!$product) {
                    continue;
                }
                
                $suggestions[] = array(
                    'id' => $product_id,
                    'title' => $product->get_name(),
                    'price' => $product->get_price_html(),
                    'url' => get_permalink($product_id),
                    'image' => wp_get_attachment_url($product->get_image_id()) ?: wc_placeholder_img_src(),
                );
            }
            
            wp_reset_postdata();
        }
        
        return $suggestions;
    }
    
    /**
     * Extract keywords from a message.
     *
     * @since    1.0.1
     * @param    string    $message    The message to extract keywords from.
     * @return   array                 Array of keywords.
     */
    private function extract_keywords($message) {
        // Common words to exclude
        $stop_words = array('a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'can', 'could', 'will', 'would', 'should', 'may', 'might',
            'must', 'shall', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them',
            'my', 'your', 'his', 'its', 'our', 'their', 'mine', 'yours', 'hers', 'ours', 'theirs',
            'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'doing', 'to', 'from', 'in', 'out', 'on', 'off',
            'over', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why',
            'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no',
            'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will',
            'just', 'don', 'don\'t', 'should', 'now', 'd', 'll', 'm', 'o', 're', 've', 'y', 'ain', 'aren',
            'aren\'t', 'couldn', 'couldn\'t', 'didn', 'didn\'t', 'doesn', 'doesn\'t', 'hadn', 'hadn\'t',
            'hasn', 'hasn\'t', 'haven', 'haven\'t', 'isn', 'isn\'t', 'ma', 'mightn', 'mightn\'t', 'mustn',
            'mustn\'t', 'needn', 'needn\'t', 'shan', 'shan\'t', 'shouldn', 'shouldn\'t', 'wasn', 'wasn\'t',
            'weren', 'weren\'t', 'won', 'won\'t', 'wouldn', 'wouldn\'t');
        
        // Convert message to lowercase and remove punctuation
        $message = strtolower($message);
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', '', $message);
        
        // Split message into words
        $words = preg_split('/\s+/', $message);
        
        // Filter out stop words and short words
        $keywords = array_filter($words, function($word) use ($stop_words) {
            return !in_array($word, $stop_words) && strlen($word) > 2;
        });
        
        // Return unique keywords
        return array_unique($keywords);
    }
    
    /**
     * Generate quick replies based on the context.
     *
     * @since    1.0.1
     * @param    string    $user_message    The user message.
     * @param    string    $bot_response    The bot response.
     * @return   array                      Array of quick replies.
     */
    private function generate_quick_replies($user_message, $bot_response) {
        // Common quick replies based on context
        $product_related_replies = array(
            'Show me more products',
            'What\'s on sale?',
            'What\'s your best seller?',
            'Do you have any discounts?',
            'Can you recommend something?'
        );
        
        $order_related_replies = array(
            'Track my order',
            'When will my order arrive?',
            'Can I change my order?',
            'What\'s your return policy?',
            'How do I cancel my order?'
        );
        
        $general_replies = array(
            'Tell me more',
            'How does this work?',
            'Can you help me find something?',
            'What payment methods do you accept?',
            'Do you ship internationally?'
        );
        
        // Determine which set of replies to use based on the context
        $context_keywords = array(
            'product' => array('product', 'item', 'buy', 'purchase', 'price', 'cost', 'sale', 'discount', 'offer'),
            'order' => array('order', 'shipping', 'delivery', 'track', 'status', 'arrive', 'return', 'cancel')
        );
        
        $context = 'general';
        $combined_text = strtolower($user_message . ' ' . $bot_response);
        
        foreach ($context_keywords as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($combined_text, $keyword) !== false) {
                    $context = $key;
                    break 2;
                }
            }
        }
        
        // Select replies based on context
        switch ($context) {
            case 'product':
                $replies = $product_related_replies;
                break;
            case 'order':
                $replies = $order_related_replies;
                break;
            default:
                $replies = $general_replies;
                break;
        }
        
        // Shuffle and take 3 random replies
        shuffle($replies);
        return array_slice($replies, 0, 3);
    }
}