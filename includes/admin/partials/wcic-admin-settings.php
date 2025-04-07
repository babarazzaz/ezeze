<?php
/**
 * Admin settings page for the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap wcic-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="#general-settings" class="nav-tab nav-tab-active"><?php _e('General', 'wc-intelligent-chatbot'); ?></a>
        <a href="#appearance-settings" class="nav-tab"><?php _e('Appearance', 'wc-intelligent-chatbot'); ?></a>
        <a href="#ai-settings" class="nav-tab"><?php _e('AI Configuration', 'wc-intelligent-chatbot'); ?></a>
        <a href="#recommendation-settings" class="nav-tab"><?php _e('Recommendations', 'wc-intelligent-chatbot'); ?></a>
        <a href="#indexing-settings" class="nav-tab"><?php _e('Indexing', 'wc-intelligent-chatbot'); ?></a>
        <a href="#advanced-features" class="nav-tab"><?php _e('Advanced Features', 'wc-intelligent-chatbot'); ?></a>
    </h2>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="wcic-settings-form">
    <input type="hidden" name="action" value="save_ezeze_chatbot_settings">
        <div id="general-settings" class="wcic-settings-tab">
            <?php wp_nonce_field('ezeze_chatbot_settings_action', 'ezeze_chatbot_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Enable Chatbot', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_chatbot_enabled" value="yes" <?php checked(get_option('wcic_chatbot_enabled', 'yes'), 'yes'); ?> />
                            <?php _e('Enable the chatbot on your site', 'wc-intelligent-chatbot'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Chatbot Title', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_title" value="<?php echo esc_attr(get_option('wcic_chatbot_title', 'Store Assistant')); ?>" class="regular-text" />
                        <p class="description"><?php _e('The title displayed in the chatbot header', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Welcome Message', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <textarea name="wcic_chatbot_welcome_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('wcic_chatbot_welcome_message', 'Hello! I\'m your personal shopping assistant. How can I help you today?')); ?></textarea>
                        <p class="description"><?php _e('The initial message displayed when the chatbot is opened', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Chatbot Position', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_chatbot_position">
                            <option value="bottom-right" <?php selected(get_option('wcic_chatbot_position', 'bottom-right'), 'bottom-right'); ?>><?php _e('Bottom Right', 'wc-intelligent-chatbot'); ?></option>
                            <option value="bottom-left" <?php selected(get_option('wcic_chatbot_position', 'bottom-right'), 'bottom-left'); ?>><?php _e('Bottom Left', 'wc-intelligent-chatbot'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Display On', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wcic_display_on_pages" value="all" <?php checked(get_option('wcic_display_on_pages', 'all'), 'all'); ?> />
                            <?php _e('All Pages', 'wc-intelligent-chatbot'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="wcic_display_on_pages" value="shop" <?php checked(get_option('wcic_display_on_pages', 'all'), 'shop'); ?> />
                            <?php _e('Shop Pages Only', 'wc-intelligent-chatbot'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="wcic_display_on_pages" value="custom" <?php checked(get_option('wcic_display_on_pages', 'all'), 'custom'); ?> />
                            <?php _e('Custom Selection', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <div id="wcic-excluded-pages" style="margin-top: 10px; <?php echo (get_option('wcic_display_on_pages', 'all') !== 'custom') ? 'display: none;' : ''; ?>">
                            <p><?php _e('Enter page IDs to exclude (comma separated):', 'wc-intelligent-chatbot'); ?></p>
                            <input type="text" name="wcic_excluded_pages" value="<?php echo esc_attr(get_option('wcic_excluded_pages', '')); ?>" class="regular-text" />
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Device Visibility', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_mobile_enabled" value="yes" <?php checked(get_option('wcic_mobile_enabled', 'yes'), 'yes'); ?> />
                            <?php _e('Show on Mobile Devices', 'wc-intelligent-chatbot'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="wcic_desktop_enabled" value="yes" <?php checked(get_option('wcic_desktop_enabled', 'yes'), 'yes'); ?> />
                            <?php _e('Show on Desktop', 'wc-intelligent-chatbot'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="wcic_tablet_enabled" value="yes" <?php checked(get_option('wcic_tablet_enabled', 'yes'), 'yes'); ?> />
                            <?php _e('Show on Tablets', 'wc-intelligent-chatbot'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="appearance-settings" class="wcic-settings-tab" style="display: none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Primary Color', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_primary_color" value="<?php echo esc_attr(get_option('wcic_chatbot_primary_color', '#0073aa')); ?>" class="wcic-color-picker" />
                        <p class="description"><?php _e('The main color for the chatbot header and buttons', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Secondary Color', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_secondary_color" value="<?php echo esc_attr(get_option('wcic_chatbot_secondary_color', '#f7f7f7')); ?>" class="wcic-color-picker" />
                        <p class="description"><?php _e('The background color for the chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Text Color', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_text_color" value="<?php echo esc_attr(get_option('wcic_chatbot_text_color', '#333333')); ?>" class="wcic-color-picker" />
                        <p class="description"><?php _e('The color for the chatbot text', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Button Color', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_button_color" value="<?php echo esc_attr(get_option('wcic_chatbot_button_color', '#0073aa')); ?>" class="wcic-color-picker" />
                        <p class="description"><?php _e('The color for buttons in the chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Button Text Color', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="text" name="wcic_chatbot_button_text_color" value="<?php echo esc_attr(get_option('wcic_chatbot_button_text_color', '#ffffff')); ?>" class="wcic-color-picker" />
                        <p class="description"><?php _e('The color for text on buttons', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="ai-settings" class="wcic-settings-tab" style="display: none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('OpenRouter API Key', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="password" name="wcic_openai_api_key" value="<?php echo esc_attr(get_option('wcic_openai_api_key', '')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Your OpenRouter API key for AI-powered conversations', 'wc-intelligent-chatbot'); ?></p>
                        <button type="button" class="button button-secondary" id="wcic-test-ai-connection"><?php _e('Test Connection', 'wc-intelligent-chatbot'); ?></button>
                        <span id="wcic-connection-result" style="margin-left: 10px;"></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('AI Model', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_openai_model" class="regular-text">
                            <option value="openai/gpt-4o" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'openai/gpt-4o'); ?>><?php _e('OpenAI: GPT-4o (Recommended)', 'wc-intelligent-chatbot'); ?></option>
                            <option value="meta-llama/llama-4-maverick" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'meta-llama/llama-4-maverick'); ?>><?php _e('Meta: Llama 4 Maverick', 'wc-intelligent-chatbot'); ?></option>
                            <option value="meta-llama/llama-4-scout" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'meta-llama/llama-4-scout'); ?>><?php _e('Meta: Llama 4 Scout', 'wc-intelligent-chatbot'); ?></option>
                            <option value="google/gemini-2.5-pro-preview-03-25" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'google/gemini-2.5-pro-preview-03-25'); ?>><?php _e('Google: Gemini 2.5 Pro', 'wc-intelligent-chatbot'); ?></option>
                            <option value="openrouter/quasar-alpha" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'openrouter/quasar-alpha'); ?>><?php _e('OpenRouter: Quasar Alpha', 'wc-intelligent-chatbot'); ?></option>
                            <option value="mistral/ministral-8b" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'mistral/ministral-8b'); ?>><?php _e('Mistral: Ministral 8B', 'wc-intelligent-chatbot'); ?></option>
                            <option value="anthropic/claude-3-opus" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'anthropic/claude-3-opus'); ?>><?php _e('Anthropic: Claude 3 Opus', 'wc-intelligent-chatbot'); ?></option>
                            <option value="anthropic/claude-3-sonnet" <?php selected(get_option('wcic_openai_model', 'openai/gpt-4o'), 'anthropic/claude-3-sonnet'); ?>><?php _e('Anthropic: Claude 3 Sonnet', 'wc-intelligent-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select the AI model to use via OpenRouter. Different models have different capabilities and pricing.', 'wc-intelligent-chatbot'); ?></p>
                        <p><a href="https://openrouter.ai/models" target="_blank" class="button button-secondary"><?php _e('View All OpenRouter Models', 'wc-intelligent-chatbot'); ?></a></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="recommendation-settings" class="wcic-settings-tab" style="display: none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Product Recommendations', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_product_recommendations" value="yes" <?php checked(get_option('wcic_enable_product_recommendations', 'yes'), 'yes'); ?> />
                            <?php _e('Enable product recommendations in chatbot responses', 'wc-intelligent-chatbot'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Page Recommendations', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_page_recommendations" value="yes" <?php checked(get_option('wcic_enable_page_recommendations', 'yes'), 'yes'); ?> />
                            <?php _e('Enable page recommendations in chatbot responses', 'wc-intelligent-chatbot'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Recommendation Priority', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_recommendation_priority">
                            <option value="relevance" <?php selected(get_option('wcic_recommendation_priority', 'relevance'), 'relevance'); ?>><?php _e('Relevance', 'wc-intelligent-chatbot'); ?></option>
                            <option value="newest" <?php selected(get_option('wcic_recommendation_priority', 'relevance'), 'newest'); ?>><?php _e('Newest Products', 'wc-intelligent-chatbot'); ?></option>
                            <option value="price_low" <?php selected(get_option('wcic_recommendation_priority', 'relevance'), 'price_low'); ?>><?php _e('Price (Low to High)', 'wc-intelligent-chatbot'); ?></option>
                            <option value="price_high" <?php selected(get_option('wcic_recommendation_priority', 'relevance'), 'price_high'); ?>><?php _e('Price (High to Low)', 'wc-intelligent-chatbot'); ?></option>
                            <option value="sales" <?php selected(get_option('wcic_recommendation_priority', 'relevance'), 'sales'); ?>><?php _e('Best Selling', 'wc-intelligent-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php _e('How to prioritize product recommendations', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Maximum Recommendations', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="number" name="wcic_max_recommendations" value="<?php echo esc_attr(get_option('wcic_max_recommendations', '3')); ?>" min="1" max="10" />
                        <p class="description"><?php _e('Maximum number of recommendations to show in a response', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="indexing-settings" class="wcic-settings-tab" style="display: none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Indexing Frequency', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_indexing_frequency">
                            <option value="hourly" <?php selected(get_option('wcic_indexing_frequency', 'daily'), 'hourly'); ?>><?php _e('Hourly', 'wc-intelligent-chatbot'); ?></option>
                            <option value="twicedaily" <?php selected(get_option('wcic_indexing_frequency', 'daily'), 'twicedaily'); ?>><?php _e('Twice Daily', 'wc-intelligent-chatbot'); ?></option>
                            <option value="daily" <?php selected(get_option('wcic_indexing_frequency', 'daily'), 'daily'); ?>><?php _e('Daily', 'wc-intelligent-chatbot'); ?></option>
                            <option value="weekly" <?php selected(get_option('wcic_indexing_frequency', 'daily'), 'weekly'); ?>><?php _e('Weekly', 'wc-intelligent-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php _e('How often to automatically update the product and page index', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Manual Indexing', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <p><?php _e('You can manually trigger indexing from the Indexing page.', 'wc-intelligent-chatbot'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=wc-intelligent-chatbot-indexing'); ?>" class="button button-secondary"><?php _e('Go to Indexing Page', 'wc-intelligent-chatbot'); ?></a>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="advanced-features" class="wcic-settings-tab" style="display: none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Product Suggestions', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_product_suggestions" value="yes" <?php checked(get_option('wcic_enable_product_suggestions', 'yes'), 'yes'); ?> />
                            <?php _e('Enable automatic product suggestions in chat', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Show product suggestions based on user conversation context', 'wc-intelligent-chatbot'); ?></p>
                        
                        <div class="wcic-nested-settings" style="margin-top: 15px; padding-left: 20px; border-left: 3px solid #f0f0f0;">
                            <h4><?php _e('Product Suggestion Settings', 'wc-intelligent-chatbot'); ?></h4>
                            
                            <div style="margin-bottom: 10px;">
                                <label for="wcic_product_suggestions_count">
                                    <?php _e('Number of products to suggest:', 'wc-intelligent-chatbot'); ?>
                                </label>
                                <select name="wcic_product_suggestions_count" id="wcic_product_suggestions_count">
                                    <option value="3" <?php selected(get_option('wcic_product_suggestions_count', '5'), '3'); ?>>3</option>
                                    <option value="5" <?php selected(get_option('wcic_product_suggestions_count', '5'), '5'); ?>>5</option>
                                    <option value="8" <?php selected(get_option('wcic_product_suggestions_count', '5'), '8'); ?>>8</option>
                                    <option value="10" <?php selected(get_option('wcic_product_suggestions_count', '5'), '10'); ?>>10</option>
                                </select>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <label for="wcic_product_suggestions_sort">
                                    <?php _e('Sort products by:', 'wc-intelligent-chatbot'); ?>
                                </label>
                                <select name="wcic_product_suggestions_sort" id="wcic_product_suggestions_sort">
                                    <option value="relevance" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'relevance'); ?>><?php _e('Relevance', 'wc-intelligent-chatbot'); ?></option>
                                    <option value="date" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'date'); ?>><?php _e('Newest first', 'wc-intelligent-chatbot'); ?></option>
                                    <option value="price" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'price'); ?>><?php _e('Price (low to high)', 'wc-intelligent-chatbot'); ?></option>
                                    <option value="price-desc" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'price-desc'); ?>><?php _e('Price (high to low)', 'wc-intelligent-chatbot'); ?></option>
                                    <option value="popularity" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'popularity'); ?>><?php _e('Popularity', 'wc-intelligent-chatbot'); ?></option>
                                    <option value="rating" <?php selected(get_option('wcic_product_suggestions_sort', 'relevance'), 'rating'); ?>><?php _e('Rating', 'wc-intelligent-chatbot'); ?></option>
                                </select>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <label>
                                    <input type="checkbox" name="wcic_product_suggestions_show_price" value="yes" <?php checked(get_option('wcic_product_suggestions_show_price', 'yes'), 'yes'); ?> />
                                    <?php _e('Show product prices', 'wc-intelligent-chatbot'); ?>
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <label>
                                    <input type="checkbox" name="wcic_product_suggestions_show_image" value="yes" <?php checked(get_option('wcic_product_suggestions_show_image', 'yes'), 'yes'); ?> />
                                    <?php _e('Show product images', 'wc-intelligent-chatbot'); ?>
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <label>
                                    <input type="checkbox" name="wcic_product_suggestions_add_to_cart" value="yes" <?php checked(get_option('wcic_product_suggestions_add_to_cart', 'yes'), 'yes'); ?> />
                                    <?php _e('Show "Add to Cart" button', 'wc-intelligent-chatbot'); ?>
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Quick Replies', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_quick_replies" value="yes" <?php checked(get_option('wcic_enable_quick_replies', 'yes'), 'yes'); ?> />
                            <?php _e('Enable quick reply buttons', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Show suggested replies as clickable buttons to help users', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Voice Input', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_voice_input" value="yes" <?php checked(get_option('wcic_enable_voice_input', 'no'), 'yes'); ?> />
                            <?php _e('Enable voice input for messages', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow users to speak their messages instead of typing', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('File Attachments', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_file_attachments" value="yes" <?php checked(get_option('wcic_enable_file_attachments', 'no'), 'yes'); ?> />
                            <?php _e('Enable file attachments in chat', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow users to upload images to help with product inquiries', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Order Tracking', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_order_tracking" value="yes" <?php checked(get_option('wcic_enable_order_tracking', 'yes'), 'yes'); ?> />
                            <?php _e('Enable order tracking in chat', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow users to check their order status via the chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Product Comparison', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_product_comparison" value="yes" <?php checked(get_option('wcic_enable_product_comparison', 'yes'), 'yes'); ?> />
                            <?php _e('Enable product comparison in chat', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow the chatbot to compare products when asked', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Discount Codes', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_discount_codes" value="yes" <?php checked(get_option('wcic_enable_discount_codes', 'yes'), 'yes'); ?> />
                            <?php _e('Enable discount code generation', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow the chatbot to generate and offer discount codes', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Cart Management', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_cart_management" value="yes" <?php checked(get_option('wcic_enable_cart_management', 'yes'), 'yes'); ?> />
                            <?php _e('Enable cart management in chat', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow users to add products to cart and checkout via the chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Multilingual Support', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_multilingual" value="yes" <?php checked(get_option('wcic_enable_multilingual', 'yes'), 'yes'); ?> />
                            <?php _e('Enable multilingual support', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically detect and respond in the user\'s language', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Customer Feedback', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_feedback" value="yes" <?php checked(get_option('wcic_enable_feedback', 'yes'), 'yes'); ?> />
                            <?php _e('Enable customer feedback collection', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Ask users to rate their experience after conversations', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Conversation History', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_conversation_history" value="yes" <?php checked(get_option('wcic_enable_conversation_history', 'yes'), 'yes'); ?> />
                            <?php _e('Enable conversation history', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Save and display conversation history for returning users', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Proactive Messages', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_proactive_messages" value="yes" <?php checked(get_option('wcic_enable_proactive_messages', 'yes'), 'yes'); ?> />
                            <?php _e('Enable proactive messages', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically initiate conversations based on user behavior', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Typing Indicators', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_typing_indicator" value="yes" <?php checked(get_option('wcic_enable_typing_indicator', 'yes'), 'yes'); ?> />
                            <?php _e('Enable typing indicators', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Show when the chatbot is "typing" a response', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Read Receipts', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_read_receipts" value="yes" <?php checked(get_option('wcic_enable_read_receipts', 'no'), 'yes'); ?> />
                            <?php _e('Enable read receipts', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Show when messages have been read', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Emoji Support', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_emoji" value="yes" <?php checked(get_option('wcic_enable_emoji', 'yes'), 'yes'); ?> />
                            <?php _e('Enable emoji support', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow emoji usage in conversations', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Chatbot Avatar', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_avatar" value="yes" <?php checked(get_option('wcic_enable_avatar', 'yes'), 'yes'); ?> />
                            <?php _e('Enable chatbot avatar', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Show a custom avatar for the chatbot', 'wc-intelligent-chatbot'); ?></p>
                        <div class="wcic-avatar-upload" style="margin-top: 10px;">
                            <input type="hidden" name="wcic_avatar_url" id="wcic_avatar_url" value="<?php echo esc_attr(get_option('wcic_avatar_url', '')); ?>" />
                            <button type="button" class="button button-secondary" id="wcic-upload-avatar"><?php _e('Upload Avatar', 'wc-intelligent-chatbot'); ?></button>
                            <div id="wcic-avatar-preview" style="margin-top: 10px; max-width: 100px;">
                                <?php if (get_option('wcic_avatar_url')) : ?>
                                    <img src="<?php echo esc_url(get_option('wcic_avatar_url')); ?>" style="max-width: 100%;" />
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('User Authentication', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_user_auth" value="yes" <?php checked(get_option('wcic_enable_user_auth', 'no'), 'yes'); ?> />
                            <?php _e('Enable user authentication', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Allow users to log in via the chatbot for personalized service', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Analytics Integration', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_analytics" value="yes" <?php checked(get_option('wcic_enable_analytics', 'yes'), 'yes'); ?> />
                            <?php _e('Enable analytics integration', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Track chatbot usage and performance metrics', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Offline Mode', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wcic_enable_offline_mode" value="yes" <?php checked(get_option('wcic_enable_offline_mode', 'yes'), 'yes'); ?> />
                            <?php _e('Enable offline mode', 'wc-intelligent-chatbot'); ?>
                        </label>
                        <p class="description"><?php _e('Collect messages when no agents are available', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Chatbot Personality', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_chatbot_personality" class="regular-text">
                            <option value="friendly" <?php selected(get_option('wcic_chatbot_personality', 'friendly'), 'friendly'); ?>><?php _e('Friendly and Helpful', 'wc-intelligent-chatbot'); ?></option>
                            <option value="professional" <?php selected(get_option('wcic_chatbot_personality', 'friendly'), 'professional'); ?>><?php _e('Professional and Formal', 'wc-intelligent-chatbot'); ?></option>
                            <option value="casual" <?php selected(get_option('wcic_chatbot_personality', 'friendly'), 'casual'); ?>><?php _e('Casual and Conversational', 'wc-intelligent-chatbot'); ?></option>
                            <option value="humorous" <?php selected(get_option('wcic_chatbot_personality', 'friendly'), 'humorous'); ?>><?php _e('Humorous and Playful', 'wc-intelligent-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select the personality style for your chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Hide all tabs
        $('.wcic-settings-tab').hide();
        
        // Remove active class from all tabs
        $('.nav-tab').removeClass('nav-tab-active');
        
        // Show the selected tab
        $($(this).attr('href')).show();
        
        // Add active class to the clicked tab
        $(this).addClass('nav-tab-active');
    });
    
    // Show the active tab on page load
    $('.nav-tab-active').trigger('click');
});
</script>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Hide all tabs
        $('.wcic-settings-tab').hide();
        
        // Remove active class from all tabs
        $('.nav-tab').removeClass('nav-tab-active');
        
        // Show the selected tab
        $($(this).attr('href')).show();
        
        // Add active class to the clicked tab
        $(this).addClass('nav-tab-active');
    });
    
    // Initialize color pickers
    $('.wcic-color-picker').wpColorPicker();
    
    // Toggle excluded pages field
    $('input[name="wcic_display_on_pages"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#wcic-excluded-pages').show();
        } else {
            $('#wcic-excluded-pages').hide();
        }
    });
    
    // Test AI connection
    $('#wcic-test-ai-connection').on('click', function() {
        var api_key = $('input[name="wcic_openai_api_key"]').val();
        var result_span = $('#wcic-connection-result');
        
        if (!api_key) {
            result_span.html('<span style="color: red;"><?php _e('Please enter an API key first.', 'wc-intelligent-chatbot'); ?></span>');
            return;
        }
        
        result_span.html('<span style="color: blue;"><?php _e('Testing connection...', 'wc-intelligent-chatbot'); ?></span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wcic_test_ai_connection',
                nonce: wcic_admin_params.nonce,
                api_key: api_key
            },
            success: function(response) {
                if (response.success) {
                    result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                } else {
                    result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                }
            },
            error: function() {
                result_span.html('<span style="color: red;"><?php _e('Connection error. Please try again.', 'wc-intelligent-chatbot'); ?></span>');
            }
        });
    });
});
</script>