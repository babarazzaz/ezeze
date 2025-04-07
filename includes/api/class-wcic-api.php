<?php
/**
 * The REST API functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/api
 */

/**
 * The REST API functionality of the plugin.
 *
 * Defines the plugin name, version, and REST API endpoints.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/api
 */
class WCIC_API {

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
     * Register the REST API routes.
     *
     * @since    1.0.0
     */
    public function register_routes() {
        register_rest_route('wcic/v1', '/message', array(
            'methods' => 'POST',
            'callback' => array($this, 'process_message'),
            'permission_callback' => '__return_true',
            'args' => array(
                'message' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'session_id' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        register_rest_route('wcic/v1', '/conversation/(?P<session_id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_conversation'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }

    /**
     * Check if the user has admin permissions.
     *
     * @since    1.0.0
     * @return   bool    Whether the user has permission.
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Process a message from the chatbot via REST API.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function process_message($request) {
        $message = $request->get_param('message');
        $session_id = $request->get_param('session_id');
        
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
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Error processing message.', 'wc-intelligent-chatbot')
            ), 500);
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
        return new WP_REST_Response(array(
            'success' => true,
            'message' => $response['message'],
            'recommendations' => $response['recommendations'],
            'session_id' => $session_id
        ), 200);
    }

    /**
     * Get a conversation by session ID via REST API.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function get_conversation($request) {
        $session_id = $request->get_param('session_id');
        
        global $wpdb;
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT message, response, recommendations, created_at 
                FROM {$wpdb->prefix}wcic_conversations 
                WHERE session_id = %s 
                ORDER BY created_at ASC",
                $session_id
            )
        );
        
        if (empty($messages)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No messages found for this conversation.', 'wc-intelligent-chatbot')
            ), 404);
        }
        
        $formatted_messages = array();
        
        foreach ($messages as $message) {
            // Add user message
            $formatted_messages[] = array(
                'is_user' => true,
                'message' => $message->message,
                'time' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->created_at)),
                'recommendations' => null
            );
            
            // Add bot response
            $recommendations = null;
            if (!empty($message->recommendations)) {
                $recommendations = json_decode($message->recommendations, true);
            }
            
            $formatted_messages[] = array(
                'is_user' => false,
                'message' => $message->response,
                'time' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->created_at)),
                'recommendations' => $recommendations
            );
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'session_id' => $session_id,
            'messages' => $formatted_messages
        ), 200);
    }
}