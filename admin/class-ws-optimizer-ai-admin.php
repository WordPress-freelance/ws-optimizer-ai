<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Admin {

    private $plugin_name;
    private $version;

    const SCREEN_ID = 'settings_page_ws-optimizer-ai';

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    // -------------------------------------------------------------------------
    // Enqueue
    // -------------------------------------------------------------------------

    public function enqueue_styles() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        $is_post_screen = in_array( $screen->base, [ 'post', 'edit' ], true );
        $is_settings    = ( $screen->id === self::SCREEN_ID );

        if ( $is_post_screen || $is_settings ) {
            wp_enqueue_style(
                $this->plugin_name,
                WS_OPTIMIZER_AI_URL . 'admin/css/ws-optimizer-ai-admin.css',
                [],
                $this->version
            );
        }
    }

    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->base !== 'post' ) {
            return;
        }

        $post_types = $this->get_configured_post_types();
        if ( ! in_array( $screen->post_type, $post_types, true ) ) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name,
            WS_OPTIMIZER_AI_URL . 'admin/js/ws-optimizer-ai-admin.js',
            [],
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'wsoaData',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wsoa_analyze' ),
                'i18n'    => [
                    'analyzing' => __( 'Analyse en cours…', 'ws-optimizer-ai' ),
                    'analyze'   => __( 'Analyser le titre', 'ws-optimizer-ai' ),
                    'reanalyze' => __( 'Ré-analyser', 'ws-optimizer-ai' ),
                    'error'     => __( 'Erreur lors de l\'analyse.', 'ws-optimizer-ai' ),
                    'noTitle'   => __( 'Ajoutez d\'abord un titre.', 'ws-optimizer-ai' ),
                ],
            ]
        );
    }

    // -------------------------------------------------------------------------
    // White frame fix + logo lock (Avada & third-party themes)
    // -------------------------------------------------------------------------

    public function add_admin_body_class( $classes ) {
        $screen = get_current_screen();
        if ( $screen && $screen->id === self::SCREEN_ID ) {
            $classes .= ' wsoa-settings-page';
        }
        return $classes;
    }

    public function inline_reset_css() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== self::SCREEN_ID ) {
            return;
        }
        echo '<style id="wsoa-reset">
        /* White-frame fix — background only, never touch #wpcontent margin */
        .wsoa-settings-page #wpwrap,
        .wsoa-settings-page #wpcontent,
        .wsoa-settings-page #wpbody,
        .wsoa-settings-page #wpbody-content { background:#14121C !important; }
        .wsoa-settings-page #wpbody,
        .wsoa-settings-page #wpbody-content { padding:0 !important; }
        .wsoa-settings-page .wrap,
        .wsoa-settings-page #wpcontent .wrap { margin:0 !important; padding:0 !important; background:#14121C !important; max-width:none !important; }
        .wsoa-settings-page #wpfooter { background:#14121C !important; }
        /* Lock SVG logo dimensions — beats Avada global svg{max-width:100%} */
        .ws-admin-wrap .ws-logo-mark { width:26px !important; height:auto !important; flex-shrink:0 !important; }
        .ws-admin-wrap .ws-title-logo { width:34px !important; height:34px !important; min-width:34px !important; flex-shrink:0 !important; }
        .ws-admin-wrap svg { max-width:none !important; }
        </style>';
    }

    // -------------------------------------------------------------------------
    // Metabox
    // -------------------------------------------------------------------------

    public function register_metaboxes() {
        $post_types = $this->get_configured_post_types();
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'wsoa-title-analyzer',
                __( 'Analyse Titre SEO (IA)', 'ws-optimizer-ai' ),
                [ $this, 'render_metabox' ],
                $post_type,
                'side',
                'high'
            );
        }
    }

    public function render_metabox( $post ) {
        require WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
    }

    // -------------------------------------------------------------------------
    // AJAX
    // -------------------------------------------------------------------------

    public function ajax_analyze_title() {
        check_ajax_referer( 'wsoa_analyze', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission refusée.', 'ws-optimizer-ai' ) ] );
            return;
        }

        $title   = isset( $_POST['title'] )   ? sanitize_text_field( wp_unslash( $_POST['title'] ) )   : '';
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( '' === $title ) {
            wp_send_json_error( [ 'message' => __( 'Le titre est vide.', 'ws-optimizer-ai' ) ] );
            return;
        }

        $settings   = get_option( 'wsoa_settings', [] );
        $model      = $settings['model']      ?? WS_Optimizer_AI_Analyzer::DEFAULT_MODEL;
        $max_tokens = isset( $settings['max_tokens'] ) ? absint( $settings['max_tokens'] ) : WS_Optimizer_AI_Analyzer::DEFAULT_MAX_TOKENS;

        $analyzer = new WS_Optimizer_AI_Analyzer( $model, $max_tokens );
        $result   = $analyzer->analyze_title( $title, $post_id ?: null );

        if ( isset( $result['error'] ) ) {
            wp_send_json_error( [ 'message' => $result['error'] ] );
            return;
        }

        if ( $post_id && ! empty( $result['success'] ) ) {
            update_post_meta( $post_id, '_wsoa_last_analysis',       $result['data'] );
            update_post_meta( $post_id, '_wsoa_last_analyzed_title', $title );
            update_post_meta( $post_id, '_wsoa_last_analysis_date',  current_time( 'timestamp' ) );
        }

        wp_send_json_success( $result['data'] );
    }

    public function ajax_clear_log() {
        check_ajax_referer( 'wsoa_clear_log', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
            return;
        }
        delete_option( 'wsoa_debug_log' );
        wp_send_json_success();
    }

    // -------------------------------------------------------------------------
    // Settings page (HTML pur — formulaires via admin-post.php)
    // -------------------------------------------------------------------------

    public function add_settings_page() {
        // Single page — onglets gérés via le paramètre ?tab= dans render_settings_page().
        add_submenu_page(
            'options-general.php',
            __( 'WS SEO Title AI', 'ws-optimizer-ai' ),
            __( 'WS SEO Title AI', 'ws-optimizer-ai' ),
            'manage_options',
            'ws-optimizer-ai',
            [ $this, 'render_settings_page' ]
        );
    }

    public function render_settings_page() {
        $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
        if ( 'logs' === $tab ) {
            require WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-logs.php';
        } else {
            require WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-settings.php';
        }
    }

    public function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
        }
        check_admin_referer( 'wsoa_save_settings' );

        $existing = get_option( 'wsoa_settings', [] );
        $input    = [
            'post_types' => ( isset( $_POST['wsoa_post_types'] ) && is_array( $_POST['wsoa_post_types'] ) )
                ? array_map( 'sanitize_key', wp_unslash( $_POST['wsoa_post_types'] ) )
                : [],
            'model'      => isset( $_POST['wsoa_model'] )
                ? sanitize_text_field( wp_unslash( $_POST['wsoa_model'] ) )
                : ( $existing['model'] ?? WS_Optimizer_AI_Analyzer::DEFAULT_MODEL ),
            'max_tokens' => $existing['max_tokens'] ?? WS_Optimizer_AI_Analyzer::DEFAULT_MAX_TOKENS,
        ];

        update_option( 'wsoa_settings', $this->sanitize_settings( $input ) );

        wp_safe_redirect( add_query_arg(
            [ 'page' => 'ws-optimizer-ai', 'saved' => '1' ],
            admin_url( 'options-general.php' )
        ) );
        exit;
    }

    public function handle_save_logs_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
        }
        check_admin_referer( 'wsoa_save_logs_settings' );

        update_option( 'wsoa_capture_logs', ! empty( $_POST['wsoa_capture_logs'] ) );

        wp_safe_redirect( add_query_arg(
            [ 'page' => 'ws-optimizer-ai', 'tab' => 'logs', 'saved' => '1' ],
            admin_url( 'options-general.php' )
        ) );
        exit;
    }

    public function sanitize_settings( $input ) {
        $sanitized = [];

        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_key', $input['post_types'] );
        } else {
            $sanitized['post_types'] = [ 'post', 'page' ];
        }

        // Modèle : accepte tout ID bien formé (Claude, OpenAI/GPT, Gemini, …).
        // '' = Auto (l'AI Client choisit selon les providers configurés).
        // Clé absente = défaut historique. ID malformé = repli sur le défaut.
        if ( ! array_key_exists( 'model', $input ) ) {
            $sanitized['model'] = WS_Optimizer_AI_Analyzer::DEFAULT_MODEL;
        } else {
            $model = strtolower( trim( (string) $input['model'] ) );
            if ( '' === $model ) {
                $sanitized['model'] = '';
            } elseif ( preg_match( '/^[a-z0-9.\-]+$/', $model ) ) {
                $sanitized['model'] = $model;
            } else {
                $sanitized['model'] = WS_Optimizer_AI_Analyzer::DEFAULT_MODEL;
            }
        }
        $sanitized['max_tokens'] = isset( $input['max_tokens'] ) ? absint( $input['max_tokens'] ) : WS_Optimizer_AI_Analyzer::DEFAULT_MAX_TOKENS;

        return $sanitized;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function get_configured_post_types() {
        $settings = get_option( 'wsoa_settings', [] );
        return $settings['post_types'] ?? [ 'post', 'page' ];
    }
}
