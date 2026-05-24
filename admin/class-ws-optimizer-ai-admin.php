<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Admin {

    private $plugin_name;
    private $version;

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
        $is_settings    = in_array( $screen->id, [ 'settings_page_ws-optimizer-ai', 'admin_page_ws-optimizer-ai-logs' ], true );

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
    // White frame fix (Avada & third-party themes)
    // -------------------------------------------------------------------------

    public function add_admin_body_class( $classes ) {
        $screen = get_current_screen();
        $wsoa_screens = [ 'settings_page_ws-optimizer-ai', 'admin_page_ws-optimizer-ai-logs' ];
        if ( $screen && in_array( $screen->id, $wsoa_screens, true ) ) {
            $classes .= ' wsoa-settings-page';
        }
        return $classes;
    }

    public function inline_reset_css() {
        $screen = get_current_screen();
        $wsoa_screens = [ 'settings_page_ws-optimizer-ai', 'admin_page_ws-optimizer-ai-logs' ];
        if ( ! $screen || ! in_array( $screen->id, $wsoa_screens, true ) ) {
            return;
        }
        echo '<style>
        .wsoa-settings-page #wpwrap,
        .wsoa-settings-page #wpcontent,
        .wsoa-settings-page #wpbody,
        .wsoa-settings-page #wpbody-content { background: #14121C !important; }
        .wsoa-settings-page #wpbody,
        .wsoa-settings-page #wpbody-content { padding: 0 !important; }
        .wsoa-settings-page .wrap,
        .wsoa-settings-page #wpcontent .wrap { margin: 0 !important; padding: 0 !important; background: #14121C !important; max-width: none !important; }
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

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    public function add_settings_page() {
        // Main settings page under Settings
        add_submenu_page(
            'options-general.php',
            __( 'WS SEO Title AI', 'ws-optimizer-ai' ),
            __( 'WS SEO Title AI', 'ws-optimizer-ai' ),
            'manage_options',
            'ws-optimizer-ai',
            [ $this, 'render_settings_page' ]
        );
        // AI Logs tab as a second submenu page (hidden from nav — accessed via tab)
        add_submenu_page(
            null,
            __( 'WS SEO Title AI — AI Logs', 'ws-optimizer-ai' ),
            __( 'AI Logs', 'ws-optimizer-ai' ),
            'manage_options',
            'ws-optimizer-ai-logs',
            [ $this, 'render_logs_page' ]
        );
    }

    public function render_settings_page() {
        require WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-settings.php';
    }

    public function render_logs_page() {
        require WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-logs.php';
    }

    public function register_settings() {
        register_setting(
            'wsoa_settings_group',
            'wsoa_settings',
            [ $this, 'sanitize_settings' ]
        );

        register_setting(
            'wsoa_logs_settings_group',
            'wsoa_capture_logs',
            [ 'sanitize_callback' => 'rest_sanitize_boolean' ]
        );

        add_settings_section(
            'wsoa_general',
            __( 'Configuration générale', 'ws-optimizer-ai' ),
            null,
            'ws-optimizer-ai'
        );

        add_settings_field(
            'wsoa_post_types',
            __( 'Types de publication', 'ws-optimizer-ai' ),
            [ $this, 'render_post_types_field' ],
            'ws-optimizer-ai',
            'wsoa_general'
        );

    }

    public function render_post_types_field() {
        $settings   = get_option( 'wsoa_settings', [] );
        $selected   = $settings['post_types'] ?? [ 'post', 'page' ];
        $post_types = get_post_types( [ 'public' => true ], 'objects' );

        foreach ( $post_types as $pt ) {
            printf(
                '<label style="display:block;margin-bottom:6px;"><input type="checkbox" name="wsoa_settings[post_types][]" value="%s" %s> %s</label>',
                esc_attr( $pt->name ),
                checked( in_array( $pt->name, $selected, true ), true, false ),
                esc_html( $pt->labels->singular_name )
            );
        }
    }


    public function sanitize_settings( $input ) {
        $sanitized = [];

        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_key', $input['post_types'] );
        } else {
            $sanitized['post_types'] = [ 'post', 'page' ];
        }

        $allowed_models          = [ 'claude-opus-4-6', 'claude-sonnet-4-6' ];
        $sanitized['model']      = in_array( $input['model'] ?? '', $allowed_models, true )
            ? $input['model']
            : WS_Optimizer_AI_Analyzer::DEFAULT_MODEL;
        $sanitized['max_tokens'] = isset( $input['max_tokens'] ) ? absint( $input['max_tokens'] ) : WS_Optimizer_AI_Analyzer::DEFAULT_MAX_TOKENS;

        return $sanitized;
    }

    // ── Helpers
    // -------------------------------------------------------------------------

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
    // Helpers
    // -------------------------------------------------------------------------

    public function get_configured_post_types() {
        $settings = get_option( 'wsoa_settings', [] );
        return $settings['post_types'] ?? [ 'post', 'page' ];
    }
}
