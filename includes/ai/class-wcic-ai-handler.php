<?php
/**
 * The AI handler functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/ai
 */

/**
 * The AI handler functionality of the plugin.
 *
 * Handles interactions with the OpenAI API for the chatbot.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/ai
 */
class WCIC_AI_Handler {

    /**
     * The OpenAI API key.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The OpenAI API key.
     */
    private $api_key;

    /**
     * The OpenAI model to use.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $model    The OpenAI model.
     */
    private $model;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Default API key if none is set in options
        $default_api_key = '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';
        
        $this->api_key = get_option('wcic_openai_api_key', $default_api_key);
        $this->model = get_option('wcic_openai_model', 'gpt-3.5-turbo');
    }

    /**
     * Process a message from the chatbot.
     *
     * @since    1.0.0
     * @param    string    $message      The user's message.
     * @param    string    $session_id   The session ID.
     * @return   array|bool              The response data, or false on failure.
     */
    public function process_message($message, $session_id) {
        // Check if API key is set
        if (empty($this->api_key)) {
            return array(
                'message' => __('The chatbot is not properly configured. Please contact the site administrator.', 'wc-intelligent-chatbot'),
                'recommendations' => array()
            );
        }
        
        // Get conversation history
        $history = $this->get_conversation_history($session_id);
        
        // Prepare the prompt
        $prompt = $this->prepare_prompt($message, $history);
        
        // Call the OpenAI API
        $response = $this->call_openai_api($prompt);
        
        if (!$response) {
            return array(
                'message' => __('Sorry, I encountered an error while processing your request. Please try again.', 'wc-intelligent-chatbot'),
                'recommendations' => array()
            );
        }
        
        // Parse the response
        $parsed_response = $this->parse_response($response);
        
        // Get recommendations if enabled
        $recommendations = array();
        
        if (get_option('wcic_enable_product_recommendations', 'yes') === 'yes') {
            $recommendations = $this->get_product_recommendations($message, $parsed_response['message']);
        }
        
        // Add page recommendations if enabled and no product recommendations found
        if (empty($recommendations) && get_option('wcic_enable_page_recommendations', 'yes') === 'yes') {
            $recommendations = $this->get_page_recommendations($message, $parsed_response['message']);
        }
        
        return array(
            'message' => $parsed_response['message'],
            'recommendations' => $recommendations
        );
    }

    /**
     * Test the connection to the OpenRouter API.
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to test.
     * @return   bool                  Whether the connection was successful.
     */
    public function test_connection($api_key) {
        // Simple test prompt
        $prompt = array(
            array('role' => 'system', 'content' => 'You are a helpful assistant.'),
            array('role' => 'user', 'content' => 'Hello, this is a test message. Please respond with "Connection successful."')
        );
        
        // Call the OpenRouter API
        $response = $this->call_openai_api($prompt, $api_key);
        
        return $response !== false;
    }

    /**
     * Get the conversation history for a session.
     *
     * @since    1.0.0
     * @param    string    $session_id    The session ID.
     * @return   array                    The conversation history.
     */
    private function get_conversation_history($session_id) {
        global $wpdb;
        
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT message, response FROM {$wpdb->prefix}wcic_conversations 
                WHERE session_id = %s 
                ORDER BY created_at ASC 
                LIMIT 10",
                $session_id
            )
        );
        
        $history = array();
        
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $history[] = array('role' => 'user', 'content' => $message->message);
                $history[] = array('role' => 'assistant', 'content' => $message->response);
            }
        }
        
        return $history;
    }

    /**
     * Prepare the prompt for the OpenAI API.
     *
     * @since    1.0.0
     * @param    string    $message    The user's message.
     * @param    array     $history    The conversation history.
     * @return   array                 The prepared prompt.
     */
    private function prepare_prompt($message, $history) {
        // Get site information
        $site_name = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        
        // System message with context
        $system_message = "You are a helpful shopping assistant for the online store '{$site_name}'. ";
        $system_message .= "Store description: {$site_description}. ";
        $system_message .= "Your goal is to assist customers by answering questions about products, store policies, and providing helpful information. ";
        $system_message .= "Be friendly, concise, and helpful. ";
        $system_message .= "If you don't know the answer to a question, suggest that the customer contact support for more information.";
        
        $prompt = array(
            array('role' => 'system', 'content' => $system_message)
        );
        
        // Add conversation history
        if (!empty($history)) {
            $prompt = array_merge($prompt, $history);
        }
        
        // Add the current message
        $prompt[] = array('role' => 'user', 'content' => $message);
        
        return $prompt;
    }

    /**
     * Call the OpenRouter API.
     *
     * @since    1.0.0
     * @param    array     $prompt     The prepared prompt.
     * @param    string    $api_key    Optional. The API key to use.
     * @return   array|bool            The API response, or false on failure.
     */
    private function call_openai_api($prompt, $api_key = null) {
        // Use provided API key or the stored one
        $api_key = $api_key ?: $this->api_key;
        
        // API endpoint - Using OpenRouter instead of OpenAI directly
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        
        // Request data
        $data = array(
            'model' => 'openai/gpt-4o', // Using gpt-4o model from OpenRouter
            'messages' => $prompt,
            'temperature' => 0.7,
            'max_tokens' => 500,
        );
        
        // Request headers
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
            'HTTP-Referer' => get_site_url(), // Adding site URL as referer for OpenRouter
            'X-Title' => 'Ezeze Intelligent Chatbot' // Adding title for OpenRouter
        );
        
        // Make the request
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 30,
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            error_log('OpenRouter API Error: ' . $response->get_error_message());
            return false;
        }
        
        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['choices'][0]['message']['content'])) {
            error_log('OpenRouter API Error: Invalid response - ' . $body);
            return false;
        }
        
        return $data;
    }

    /**
     * Parse the response from the OpenAI API.
     *
     * @since    1.0.0
     * @param    array    $response    The API response.
     * @return   array                 The parsed response.
     */
    private function parse_response($response) {
        $message = $response['choices'][0]['message']['content'];
        
        return array(
            'message' => $message,
        );
    }

    /**
     * Get product recommendations based on the user's message and AI response.
     *
     * @since    1.0.0
     * @param    string    $message     The user's message.
     * @param    string    $ai_response The AI's response.
     * @return   array                  The product recommendations.
     */
    private function get_product_recommendations($message, $ai_response) {
        // Get maximum number of recommendations
        $max_recommendations = intval(get_option('wcic_max_recommendations', 3));
        
        // Get recommendation priority
        $priority = get_option('wcic_recommendation_priority', 'relevance');
        
        // Extract keywords from message and response
        $keywords = $this->extract_keywords($message . ' ' . $ai_response);
        
        if (empty($keywords)) {
            return array();
        }
        
        // Search for products based on keywords
        global $wpdb;
        
        $recommendations = array();
        $processed_ids = array();
        
        foreach ($keywords as $keyword) {
            // Skip short keywords
            if (strlen($keyword) < 3) {
                continue;
            }
            
            // Search in product index
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT product_id, product_data 
                    FROM {$wpdb->prefix}wcic_product_index 
                    WHERE product_data LIKE %s 
                    LIMIT 10",
                    '%' . $wpdb->esc_like($keyword) . '%'
                )
            );
            
            if (empty($results)) {
                continue;
            }
            
            foreach ($results as $result) {
                // Skip if already processed
                if (in_array($result->product_id, $processed_ids)) {
                    continue;
                }
                
                $processed_ids[] = $result->product_id;
                
                // Get product data
                $product_data = json_decode($result->product_data, true);
                
                // Skip if product data is invalid
                if (empty($product_data) || !isset($product_data['name'])) {
                    continue;
                }
                
                // Create recommendation
                $recommendation = array(
                    'id' => $product_data['id'],
                    'title' => $product_data['name'],
                    'url' => $product_data['permalink'],
                    'price' => wc_price($product_data['price']),
                    'type' => 'product',
                );
                
                // Add image if available
                if (!empty($product_data['images']) && isset($product_data['images'][0]['thumbnail'])) {
                    $recommendation['image'] = $product_data['images'][0]['thumbnail'];
                }
                
                // Add stock status if available
                if (isset($product_data['stock_status'])) {
                    $recommendation['stock'] = $product_data['stock_status'];
                }
                
                $recommendations[] = $recommendation;
                
                // Stop if we have enough recommendations
                if (count($recommendations) >= $max_recommendations) {
                    break 2;
                }
            }
        }
        
        // Sort recommendations based on priority
        if (!empty($recommendations)) {
            $this->sort_recommendations($recommendations, $priority);
        }
        
        return $recommendations;
    }

    /**
     * Get page recommendations based on the user's message and AI response.
     *
     * @since    1.0.0
     * @param    string    $message     The user's message.
     * @param    string    $ai_response The AI's response.
     * @return   array                  The page recommendations.
     */
    private function get_page_recommendations($message, $ai_response) {
        // Get maximum number of recommendations
        $max_recommendations = intval(get_option('wcic_max_recommendations', 3));
        
        // Extract keywords from message and response
        $keywords = $this->extract_keywords($message . ' ' . $ai_response);
        
        if (empty($keywords)) {
            return array();
        }
        
        // Search for pages based on keywords
        global $wpdb;
        
        $recommendations = array();
        $processed_ids = array();
        
        foreach ($keywords as $keyword) {
            // Skip short keywords
            if (strlen($keyword) < 3) {
                continue;
            }
            
            // Search in page index
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id, content_data 
                    FROM {$wpdb->prefix}wcic_page_index 
                    WHERE content_data LIKE %s 
                    LIMIT 10",
                    '%' . $wpdb->esc_like($keyword) . '%'
                )
            );
            
            if (empty($results)) {
                continue;
            }
            
            foreach ($results as $result) {
                // Skip if already processed
                if (in_array($result->post_id, $processed_ids)) {
                    continue;
                }
                
                $processed_ids[] = $result->post_id;
                
                // Get page data
                $page_data = json_decode($result->content_data, true);
                
                // Skip if page data is invalid
                if (empty($page_data) || !isset($page_data['title'])) {
                    continue;
                }
                
                // Create recommendation
                $recommendation = array(
                    'id' => $page_data['id'],
                    'title' => $page_data['title'],
                    'url' => $page_data['permalink'],
                    'type' => $page_data['type'],
                );
                
                // Add image if available
                if (!empty($page_data['featured_image']) && isset($page_data['featured_image']['thumbnail'])) {
                    $recommendation['image'] = $page_data['featured_image']['thumbnail'];
                }
                
                $recommendations[] = $recommendation;
                
                // Stop if we have enough recommendations
                if (count($recommendations) >= $max_recommendations) {
                    break 2;
                }
            }
        }
        
        return $recommendations;
    }

    /**
     * Extract keywords from a text.
     *
     * @since    1.0.0
     * @param    string    $text    The text to extract keywords from.
     * @return   array              The extracted keywords.
     */
    private function extract_keywords($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove punctuation
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        
        // Split into words
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Remove common stop words
        $stop_words = array(
            'a', 'an', 'the', 'and', 'or', 'but', 'if', 'then', 'else', 'when',
            'at', 'from', 'by', 'for', 'with', 'about', 'against', 'between',
            'into', 'through', 'during', 'before', 'after', 'above', 'below',
            'to', 'of', 'in', 'on', 'is', 'are', 'was', 'were', 'be', 'been',
            'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing',
            'i', 'me', 'my', 'mine', 'myself', 'you', 'your', 'yours', 'yourself',
            'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself',
            'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves',
            'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those',
            'am', 'can', 'could', 'may', 'might', 'must', 'shall', 'should',
            'will', 'would', 'how', 'when', 'where', 'why', 'all', 'any', 'both',
            'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
            'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very',
        );
        
        $keywords = array_diff($words, $stop_words);
        
        // Remove duplicates and return
        return array_unique($keywords);
    }

    /**
     * Sort recommendations based on priority.
     *
     * @since    1.0.0
     * @param    array     &$recommendations    The recommendations to sort.
     * @param    string    $priority            The priority to sort by.
     */
    private function sort_recommendations(&$recommendations, $priority) {
        switch ($priority) {
            case 'newest':
                // Sort by ID (assuming newer products have higher IDs)
                usort($recommendations, function($a, $b) {
                    return $b['id'] - $a['id'];
                });
                break;
                
            case 'price_low':
                // Sort by price (low to high)
                usort($recommendations, function($a, $b) {
                    $price_a = floatval(preg_replace('/[^0-9.]/', '', $a['price']));
                    $price_b = floatval(preg_replace('/[^0-9.]/', '', $b['price']));
                    return $price_a - $price_b;
                });
                break;
                
            case 'price_high':
                // Sort by price (high to low)
                usort($recommendations, function($a, $b) {
                    $price_a = floatval(preg_replace('/[^0-9.]/', '', $a['price']));
                    $price_b = floatval(preg_replace('/[^0-9.]/', '', $b['price']));
                    return $price_b - $price_a;
                });
                break;
                
            case 'sales':
                // We would need sales data for this, but it's not available in the index
                // For now, we'll just keep the default order
                break;
                
            case 'relevance':
            default:
                // Keep the default order (relevance)
                break;
        }
    }
}