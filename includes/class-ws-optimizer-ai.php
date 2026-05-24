<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = WS_OPTIMIZER_AI_SLUG;
        $this->version     = WS_OPTIMIZER_AI_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-loader.php';
        require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-i18n.php';
        require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-analyzer.php';
        require_once WS_OPTIMIZER_AI_PATH . 'admin/class-ws-optimizer-ai-admin.php';
        $this->loader = new WS_Optimizer_AI_Loader();
    }

    private function set_locale() {
        $i18n = new WS_Optimizer_AI_i18n();
        $this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $admin = new WS_Optimizer_AI_Admin( $this->plugin_name, $this->version );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        $this->loader->add_action( 'add_meta_boxes',        $admin, 'register_metaboxes' );
        $this->loader->add_action( 'wp_ajax_wsoa_analyze',    $admin, 'ajax_analyze_title' );
        $this->loader->add_action( 'wp_ajax_wsoa_clear_log',        $admin, 'ajax_clear_log' );
        $this->loader->add_action( 'admin_post_wsoa_save_settings',     $admin, 'handle_save_settings' );
        $this->loader->add_action( 'admin_post_wsoa_save_logs_settings', $admin, 'handle_save_logs_settings' );
        $this->loader->add_action( 'admin_menu',            $admin, 'add_settings_page' );
        $this->loader->add_filter( 'admin_body_class',      $admin, 'add_admin_body_class' );
        $this->loader->add_action( 'admin_head',            $admin, 'inline_reset_css' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() { return $this->plugin_name; }
    public function get_version()     { return $this->version; }
    public function get_loader()      { return $this->loader; }
}
