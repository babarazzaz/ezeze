<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/includes
 */
class WCIC {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WCIC_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = WCIC_VERSION;
        $this->plugin_name = 'wc-intelligent-chatbot';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WCIC_Loader. Orchestrates the hooks of the plugin.
     * - WCIC_i18n. Defines internationalization functionality.
     * - WCIC_Admin. Defines all hooks for the admin area.
     * - WCIC_Public. Defines all hooks for the public side of the site.
     * - WCIC_API. Defines all hooks for the REST API.
     * - WCIC_Product_Indexer. Handles product indexing.
     * - WCIC_Page_Indexer. Handles page content indexing.
     * - WCIC_AI_Handler. Handles AI interactions.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/class-wcic-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/class-wcic-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/admin/class-wcic-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/public/class-wcic-public.php';

        /**
         * The class responsible for defining all REST API endpoints.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/api/class-wcic-api.php';

        /**
         * The class responsible for product indexing.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/indexers/class-wcic-product-indexer.php';

        /**
         * The class responsible for page content indexing.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/indexers/class-wcic-page-indexer.php';

        /**
         * The class responsible for AI interactions.
         */
        require_once WCIC_PLUGIN_DIR . 'includes/ai/class-wcic-ai-handler.php';

        $this->loader = new WCIC_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WCIC_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new WCIC_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WCIC_Admin($this->get_plugin_name(), $this->get_version());

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Admin menu and settings
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

        // Plugin action links
        $this->loader->add_filter('plugin_action_links_' . WCIC_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');

        // AJAX handlers for admin
        $this->loader->add_action('wp_ajax_wcic_manual_index_products', $plugin_admin, 'manual_index_products');
        $this->loader->add_action('wp_ajax_wcic_manual_index_pages', $plugin_admin, 'manual_index_pages');
        $this->loader->add_action('wp_ajax_wcic_test_ai_connection', $plugin_admin, 'test_ai_connection');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new WCIC_Public($this->get_plugin_name(), $this->get_version());

        // Public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Add chatbot to footer
        $this->loader->add_action('wp_footer', $plugin_public, 'render_chatbot');

        // AJAX handlers for public
        $this->loader->add_action('wp_ajax_wcic_send_message', $plugin_public, 'process_message');
        $this->loader->add_action('wp_ajax_nopriv_wcic_send_message', $plugin_public, 'process_message');
    }

    /**
     * Register all of the hooks related to the REST API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $plugin_api = new WCIC_API($this->get_plugin_name(), $this->get_version());

        // Register REST API routes
        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    WCIC_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}