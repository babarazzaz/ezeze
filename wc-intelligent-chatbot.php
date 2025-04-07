<?php
/**
 * Plugin Name: WC Intelligent Chatbot
 * Plugin URI: https://example.com/wc-intelligent-chatbot
 * Description: An advanced, intelligent chatbot for WordPress and WooCommerce that analyzes products and content in real-time to provide personalized recommendations and answers.
 * Version: 1.0.0
 * Author: OpenHands
 * Author URI: https://example.com
 * Text Domain: wc-intelligent-chatbot
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.7
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WCIC_VERSION', '1.0.0');
define('WCIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCIC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCIC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wc_intelligent_chatbot() {
    require_once WCIC_PLUGIN_DIR . 'includes/class-wcic-activator.php';
    WCIC_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wc_intelligent_chatbot() {
    require_once WCIC_PLUGIN_DIR . 'includes/class-wcic-deactivator.php';
    WCIC_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wc_intelligent_chatbot');
register_deactivation_hook(__FILE__, 'deactivate_wc_intelligent_chatbot');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once WCIC_PLUGIN_DIR . 'includes/class-wcic.php';

/**
 * Begins execution of the plugin.
 */
function run_wc_intelligent_chatbot() {
    $plugin = new WCIC();
    $plugin->run();
}

// Check if WooCommerce is active
function wcic_check_woocommerce() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'wcic_woocommerce_missing_notice');
        return false;
    }
    return true;
}

// Admin notice for missing WooCommerce
function wcic_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('WC Intelligent Chatbot requires WooCommerce to be installed and active.', 'wc-intelligent-chatbot'); ?></p>
    </div>
    <?php
}

// Initialize the plugin if WooCommerce is active
if (wcic_check_woocommerce()) {
    run_wc_intelligent_chatbot();
}