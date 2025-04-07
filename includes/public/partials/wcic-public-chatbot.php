<?php
/**
 * The public-facing chatbot template.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div id="wcic-chatbot" class="wcic-chatbot wcic-position-<?php echo esc_attr($position); ?>" style="display: none;">
    <div class="wcic-chatbot-header" style="background-color: <?php echo esc_attr($primary_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
        <div class="wcic-chatbot-title"><?php echo esc_html(get_option('wcic_chatbot_title', 'Store Assistant')); ?></div>
        <div class="wcic-chatbot-controls">
            <span class="wcic-chatbot-minimize">−</span>
            <span class="wcic-chatbot-close">×</span>
        </div>
    </div>
    
    <div class="wcic-chatbot-body" style="background-color: <?php echo esc_attr($secondary_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
        <div class="wcic-chatbot-messages">
            <div class="wcic-message wcic-message-bot">
                <div class="wcic-message-content"><?php echo esc_html(get_option('wcic_chatbot_welcome_message', 'Hello! I\'m your personal shopping assistant. How can I help you today?')); ?></div>
            </div>
        </div>
    </div>
    
    <div class="wcic-chatbot-footer">
        <div class="wcic-chatbot-input-container">
            <input type="text" class="wcic-chatbot-input" placeholder="<?php esc_attr_e('Type your message here...', 'wc-intelligent-chatbot'); ?>" />
            <button class="wcic-chatbot-send" style="background-color: <?php echo esc_attr($button_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
                <?php esc_html_e('Send', 'wc-intelligent-chatbot'); ?>
            </button>
        </div>
    </div>
</div>

<div id="wcic-chatbot-button" class="wcic-chatbot-button wcic-position-<?php echo esc_attr($position); ?>" style="background-color: <?php echo esc_attr($primary_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
</div>

<style>
/* Custom styles based on settings */
.wcic-message-bot .wcic-message-content {
    background-color: <?php echo esc_attr($primary_color); ?>;
    color: <?php echo esc_attr($button_text_color); ?>;
}

.wcic-message-user .wcic-message-content {
    background-color: #e6e6e6;
    color: <?php echo esc_attr($text_color); ?>;
}

.wcic-recommendation-item {
    border-color: <?php echo esc_attr($primary_color); ?>;
}

.wcic-recommendation-title {
    color: <?php echo esc_attr($text_color); ?>;
}

.wcic-recommendation-price {
    color: <?php echo esc_attr($primary_color); ?>;
}

.wcic-recommendation-button {
    background-color: <?php echo esc_attr($button_color); ?>;
    color: <?php echo esc_attr($button_text_color); ?>;
}
</style>