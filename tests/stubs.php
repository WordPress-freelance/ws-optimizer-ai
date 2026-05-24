<?php
/**
 * Stubs des fonctions WP que Patchwork doit pouvoir redéfinir.
 * Ce fichier est inclus APRÈS WP_Mock::bootstrap() pour que Patchwork
 * intercepte l'inclusion et puisse mocker ces fonctions dans les tests.
 */

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}
if ( ! function_exists( 'add_shortcode' ) ) {
    function add_shortcode( $tag, $callback ) {}
}
if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain( $domain, $path = false, $dir = null ) {}
}
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0 ) {
        return json_encode( $data, $options );
    }
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) { return $default; }
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) { return true; }
}
if ( ! function_exists( 'add_option' ) ) {
    function add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) { return true; }
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) { return true; }
}
if ( ! function_exists( 'get_post_meta' ) ) {
    function get_post_meta( $post_id, $key = '', $single = false ) {
        return $single ? '' : [];
    }
}
if ( ! function_exists( 'update_post_meta' ) ) {
    function update_post_meta( $post_id, $key, $value, $prev_value = '' ) { return true; }
}
if ( ! function_exists( 'get_permalink' ) ) {
    function get_permalink( $post = 0 ) { return 'https://example.com/post/'; }
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) { return true; }
}
if ( ! function_exists( 'check_ajax_referer' ) ) {
    function check_ajax_referer( $action, $query_arg = false, $die = true ) { return true; }
}
if ( ! function_exists( 'wp_send_json_success' ) ) {
    function wp_send_json_success( $data = null ) {}
}
if ( ! function_exists( 'wp_send_json_error' ) ) {
    function wp_send_json_error( $data = null ) {}
}
if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message = '', $title = '', $args = [] ) {
        throw new \Exception( is_string( $message ) ? $message : 'wp_die' );
    }
}
if ( ! function_exists( 'add_meta_box' ) ) {
    function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {}
}
if ( ! function_exists( 'add_submenu_page' ) ) {
    function add_submenu_page( $parent, $title, $menu_title, $capability, $slug, $callback = '', $position = null ) {}
}
if ( ! function_exists( 'register_setting' ) ) {
    function register_setting( $group, $option_name, $args = [] ) {}
}
if ( ! function_exists( 'settings_fields' ) ) {
    function settings_fields( $group ) {}
}
if ( ! function_exists( 'do_settings_sections' ) ) {
    function do_settings_sections( $page ) {}
}
if ( ! function_exists( 'submit_button' ) ) {
    function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = '' ) {}
}
if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) { return 'test_nonce'; }
}
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action = -1 ) { return true; }
}
if ( ! function_exists( 'wp_enqueue_style' ) ) {
    function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {}
}
if ( ! function_exists( 'wp_enqueue_script' ) ) {
    function wp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {}
}
if ( ! function_exists( 'wp_localize_script' ) ) {
    function wp_localize_script( $handle, $object_name, $l10n ) {}
}
if ( ! function_exists( 'get_current_screen' ) ) {
    function get_current_screen() { return null; }
}
if ( ! function_exists( 'get_post_types' ) ) {
    function get_post_types( $args = [], $output = 'names', $operator = 'and' ) {
        return [ 'post' => 'post', 'page' => 'page' ];
    }
}
if ( ! function_exists( 'deactivate_plugins' ) ) {
    function deactivate_plugins( $plugins ) {}
}
if ( ! function_exists( 'is_plugin_active' ) ) {
    function is_plugin_active( $plugin ) { return false; }
}
// wp_ai_client_prompt : stub pour WordPress 7.0 AI Client
if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
    function wp_ai_client_prompt( $prompt_text, $arg2 = null, $arg3 = null ) {
        // Returns a mock builder object that supports ->usingModel()->usingMaxTokens()->generate_text()
        return new class {
            public function usingModel( $m ) { return $this; }
            public function usingMaxTokens( $t ) { return $this; }
            public function generate_text() { return ''; }
            public function __call( $name, $args ) { return $this; }
        };
    }
}
if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type, $gmt = 0 ) {
        return $type === 'timestamp' ? time() : date( 'Y-m-d H:i:s' );
    }
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) { return true; }
}
if ( ! function_exists( 'get_class_methods' ) ) {
    // PHP built-in — no stub needed
}
