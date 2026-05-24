<?php
/**
 * Bootstrap PHPUnit — WS SEO Title AI
 * Ordre critique des 6 étapes.
 */

// ── Étape 1 : Constantes WP + plugin ─────────────────────────────────────────
define( 'ABSPATH',           '/tmp/wp/' );
define( 'WP_DEBUG',          true );
define( 'OBJECT',            'OBJECT' );
define( 'ARRAY_A',           'ARRAY_A' );
define( 'DAY_IN_SECONDS',    86400 );
define( 'WEEK_IN_SECONDS',   604800 );

define( 'WS_OPTIMIZER_AI_VERSION', '1.0.0' );
define( 'WS_OPTIMIZER_AI_SLUG',    'ws-optimizer-ai' );
define( 'WS_OPTIMIZER_AI_FILE',    dirname( __DIR__ ) . '/ws-optimizer-ai.php' );
define( 'WS_OPTIMIZER_AI_PATH',    dirname( __DIR__ ) . '/' );
define( 'WS_OPTIMIZER_AI_URL',     'https://example.com/wp-content/plugins/ws-optimizer-ai/' );

// ── Étape 2 : Autoloader (charge Patchwork côté lib) ─────────────────────────
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// ── Étape 3 : WP_Mock::bootstrap() — active Patchwork ────────────────────────
WP_Mock::bootstrap();

// ── Étape 4a : Stubs PUREMENT utilitaires (jamais mockés, OK dans ce fichier) ─
if ( ! function_exists( 'absint' ) ) {
    function absint( $v ) { return abs( (int) $v ); }
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $t, $d = 'default' ) { return esc_html( $t ); }
}
if ( ! function_exists( 'esc_html_e' ) ) {
    function esc_html_e( $t, $d = 'default' ) { echo esc_html( $t ); }
}
if ( ! function_exists( 'esc_attr__' ) ) {
    function esc_attr__( $t, $d = 'default' ) { return esc_attr( $t ); }
}
if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) { return filter_var( $url, FILTER_SANITIZE_URL ); }
}
if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $k ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $k ) ); }
}
if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( $s ) { return rtrim( $s, '/\\' ) . '/'; }
}
if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) { return basename( dirname( $file ) ) . '/' . basename( $file ); }
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) { return trailingslashit( dirname( $file ) ); }
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) { return 'https://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; }
}
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) { return $text; }
}
if ( ! function_exists( '_e' ) ) {
    function _e( $text, $domain = 'default' ) { echo $text; }
}

// ── Étape 4b : Stubs mockables — fichier séparé pour que Patchwork puisse les intercepter
require_once __DIR__ . '/stubs.php';

// ── Étape 5 : Classes stubs ───────────────────────────────────────────────────
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public $errors = [];
        public $error_data = [];
        public function __construct( $code = '', $msg = '', $data = '' ) {
            if ( $code ) $this->errors[ $code ][] = $msg;
            if ( $data )  $this->error_data[ $code ] = $data;
        }
        public function get_error_message( $code = '' ) {
            return $code && isset( $this->errors[ $code ][0] ) ? $this->errors[ $code ][0] : '';
        }
        public function get_error_code() {
            $codes = array_keys( $this->errors );
            return reset( $codes );
        }
    }
}
if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $t ) { return $t instanceof WP_Error; }
}
if ( ! class_exists( 'wpdb' ) ) {
    class wpdb {
        public $prefix    = 'wp_';
        public $postmeta  = 'wp_postmeta';
        public $options   = 'wp_options';
        public $last_query = '';
        public function query( $sql ) { $this->last_query = $sql; return true; }
        public function get_results( $sql ) { $this->last_query = $sql; return []; }
        public function prepare( $sql, ...$args ) { return vsprintf( str_replace( '%s', "'%s'", $sql ), $args ); }
    }
}

// ── Étape 6 : Classes plugin ──────────────────────────────────────────────────
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-loader.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-i18n.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-activator.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-deactivator.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-analyzer.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai.php';
require_once WS_OPTIMIZER_AI_PATH . 'admin/class-ws-optimizer-ai-admin.php';
