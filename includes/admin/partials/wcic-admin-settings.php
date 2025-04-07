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
    </h2>
    
    <form method="post" action="options.php" class="wcic-settings-form">
        <div id="general-settings" class="wcic-settings-tab">
            <?php
            settings_fields('wcic_general_settings');
            ?>
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
            <?php
            settings_fields('wcic_appearance_settings');
            ?>
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
            <?php
            settings_fields('wcic_ai_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('OpenAI API Key', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <input type="password" name="wcic_openai_api_key" value="<?php echo esc_attr(get_option('wcic_openai_api_key', '')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Your OpenAI API key for AI-powered conversations', 'wc-intelligent-chatbot'); ?></p>
                        <button type="button" class="button button-secondary" id="wcic-test-ai-connection"><?php _e('Test Connection', 'wc-intelligent-chatbot'); ?></button>
                        <span id="wcic-connection-result" style="margin-left: 10px;"></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('AI Model', 'wc-intelligent-chatbot'); ?></th>
                    <td>
                        <select name="wcic_openai_model">
                            <option value="gpt-3.5-turbo" <?php selected(get_option('wcic_openai_model', 'gpt-3.5-turbo'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                            <option value="gpt-4" <?php selected(get_option('wcic_openai_model', 'gpt-3.5-turbo'), 'gpt-4'); ?>>GPT-4</option>
                            <option value="gpt-4-turbo" <?php selected(get_option('wcic_openai_model', 'gpt-3.5-turbo'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                        </select>
                        <p class="description"><?php _e('Select the AI model to use for the chatbot', 'wc-intelligent-chatbot'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="recommendation-settings" class="wcic-settings-tab" style="display: none;">
            <?php
            settings_fields('wcic_recommendation_settings');
            ?>
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
            <?php
            settings_fields('wcic_indexing_settings');
            ?>
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