<?php

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI_Admin;

/**
 * JS selectors contract (must exist in partials):
 *   querySelectorAll : .wsoa-btn-analyze
 *   querySelector    : .wsoa-spinner
 *   getElementById   : wsoa-result-{postId}
 *   dataset          : data-post-id
 *
 * wsoaData keys consumed by JS:
 *   d.ajaxUrl, d.nonce, d.i18n.analyzing, d.i18n.analyze,
 *   d.i18n.reanalyze, d.i18n.error, d.i18n.noTitle
 */
class AdminTest extends WebStrategyTestCase {

    private WS_Optimizer_AI_Admin $admin;

    public function setUp(): void {
        parent::setUp();
        $this->admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '1.0.0' );
    }


    // ── Helpers ───────────────────────────────────────────────────────────────

    private function make_builder( $text ) {
        return new class( $text ) {
            private $t;
            public function __construct( $t ) { $this->t = $t; }
            public function usingModel( $m ) { return $this; }
            public function usingMaxTokens( $n ) { return $this; }
            public function generate_text() { return $this->t; }
            public function __call( $name, $args ) { return $this; }
        };
    }

    // ── Constructor ───────────────────────────────────────────────────────────

    public function test_constructor_sets_plugin_name() {
        $this->assertEquals( 'ws-optimizer-ai', $this->get_property( $this->admin, 'plugin_name' ) );
    }

    public function test_constructor_sets_version() {
        $this->assertEquals( '1.0.0', $this->get_property( $this->admin, 'version' ) );
    }

    // ── get_configured_post_types ─────────────────────────────────────────────

    public function test_get_configured_post_types_returns_default_when_no_option() {
        WP_Mock::userFunction( 'get_option', [
            'args'   => [ 'wsoa_settings', [] ],
            'return' => [],
        ] );
        $types = $this->admin->get_configured_post_types();
        $this->assertContains( 'post', $types );
        $this->assertContains( 'page', $types );
    }

    public function test_get_configured_post_types_returns_saved_value() {
        WP_Mock::userFunction( 'get_option', [
            'args'   => [ 'wsoa_settings', [] ],
            'return' => [ 'post_types' => [ 'post', 'product' ] ],
        ] );
        $types = $this->admin->get_configured_post_types();
        $this->assertContains( 'product', $types );
        $this->assertNotContains( 'page', $types );
    }

    // ── sanitize_settings ────────────────────────────────────────────────────

    public function test_sanitize_settings_keeps_valid_post_types() {
        $result = $this->admin->sanitize_settings( [ 'post_types' => [ 'post', 'page', 'product' ], 'model' => 'claude-opus-4-6' ] );
        $this->assertContains( 'post', $result['post_types'] );
        $this->assertContains( 'product', $result['post_types'] );
    }

    public function test_sanitize_settings_defaults_to_post_page_when_no_post_types() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'claude-opus-4-6' ] );
        $this->assertEquals( [ 'post', 'page' ], $result['post_types'] );
    }

    public function test_sanitize_settings_rejects_malformed_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'GPT 99 Turbo!!' ] );
        $this->assertEquals( \WS_Optimizer_AI_Analyzer::DEFAULT_MODEL, $result['model'] );
    }

    public function test_sanitize_settings_accepts_sonnet_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'claude-sonnet-4-6', 'post_types' => [ 'post' ] ] );
        $this->assertEquals( 'claude-sonnet-4-6', $result['model'] );
    }

    public function test_sanitize_settings_accepts_openai_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'gpt-5.3', 'post_types' => [ 'post' ] ] );
        $this->assertEquals( 'gpt-5.3', $result['model'] );
    }

    public function test_sanitize_settings_accepts_gemini_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'gemini-2.5-pro', 'post_types' => [ 'post' ] ] );
        $this->assertEquals( 'gemini-2.5-pro', $result['model'] );
    }

    public function test_sanitize_settings_preserves_auto_empty_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => '', 'post_types' => [ 'post' ] ] );
        $this->assertSame( '', $result['model'] );
    }

    public function test_sanitize_settings_defaults_model_when_key_absent() {
        $result = $this->admin->sanitize_settings( [ 'post_types' => [ 'post' ] ] );
        $this->assertEquals( \WS_Optimizer_AI_Analyzer::DEFAULT_MODEL, $result['model'] );
    }

    public function test_sanitize_settings_sanitizes_post_type_keys() {
        $result = $this->admin->sanitize_settings( [ 'post_types' => [ 'post', 'MY INVALID TYPE!' ], 'model' => 'claude-opus-4-6' ] );
        $this->assertNotContains( 'MY INVALID TYPE!', $result['post_types'] );
    }

    public function test_sanitize_settings_ignores_unknown_keys() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'claude-opus-4-6', 'injected_key' => 'evil' ] );
        $this->assertArrayNotHasKey( 'injected_key', $result );
    }

    // ── enqueue_scripts ───────────────────────────────────────────────────────

    public function test_enqueue_scripts_calls_localize_with_required_keys() {
        $screen             = new \stdClass();
        $screen->base       = 'post';
        $screen->post_type  = 'post';
        WP_Mock::userFunction( 'get_current_screen', [ 'return' => $screen ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [ 'post_types' => [ 'post' ] ] ] );
        WP_Mock::userFunction( 'wp_enqueue_script', [ 'return' => null ] );
        WP_Mock::userFunction( 'admin_url', [ 'return' => 'https://example.com/wp-admin/admin-ajax.php' ] );
        WP_Mock::userFunction( 'wp_create_nonce', [ 'return' => 'test_nonce' ] );

        $localized = null;
        WP_Mock::userFunction( 'wp_localize_script', [
            'return' => function ( $handle, $obj, $data ) use ( &$localized ) {
                $localized = $data;
            },
        ] );

        $this->admin->enqueue_scripts();

        $this->assertNotNull( $localized, 'wp_localize_script was not called' );
        $this->assertArrayHasKey( 'ajaxUrl', $localized );
        $this->assertArrayHasKey( 'nonce',   $localized );
        $this->assertArrayHasKey( 'i18n',    $localized );

        // All keys consumed by the JS must be present
        foreach ( [ 'analyzing', 'analyze', 'reanalyze', 'error', 'noTitle' ] as $key ) {
            $this->assertArrayHasKey( $key, $localized['i18n'], "i18n key '$key' missing from wp_localize_script" );
        }
    }

    public function test_enqueue_scripts_not_called_on_wrong_screen() {
        $screen       = new \stdClass();
        $screen->base = 'dashboard';
        WP_Mock::userFunction( 'get_current_screen', [ 'return' => $screen ] );

        $called = false;
        WP_Mock::userFunction( 'wp_enqueue_script', [
            'return' => function() use ( &$called ) { $called = true; },
        ] );

        $this->admin->enqueue_scripts();
        $this->assertFalse( $called, 'wp_enqueue_script must not be called on dashboard' );
    }

    public function test_enqueue_scripts_not_called_for_unconfigured_post_type() {
        $screen            = new \stdClass();
        $screen->base      = 'post';
        $screen->post_type = 'product';
        WP_Mock::userFunction( 'get_current_screen', [ 'return' => $screen ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [ 'post_types' => [ 'post' ] ] ] );

        $called = false;
        WP_Mock::userFunction( 'wp_enqueue_script', [
            'return' => function() use ( &$called ) { $called = true; },
        ] );

        $this->admin->enqueue_scripts();
        $this->assertFalse( $called );
    }

    // ── Metabox partial HTML ──────────────────────────────────────────────────
    // Contract: every class/id/data-attribute used in ws-optimizer-ai-admin.js
    // must be present in the rendered HTML.

    public function test_metabox_partial_contains_js_button_selector() {
        $post     = new \stdClass();
        $post->ID = 42;

        WP_Mock::userFunction( 'get_post_meta', [ 'return' => [] ] );

        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
        $html = ob_get_clean();

        // querySelector('.wsoa-btn-analyze') — the critical one that was broken
        $this->assertStringContainsString( 'wsoa-btn-analyze', $html,
            'Button class must match JS querySelectorAll selector' );
    }

    public function test_metabox_partial_contains_result_container_id() {
        $post     = new \stdClass();
        $post->ID = 42;

        WP_Mock::userFunction( 'get_post_meta', [ 'return' => [] ] );

        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
        $html = ob_get_clean();

        // getElementById('wsoa-result-' + postId)
        $this->assertStringContainsString( 'wsoa-result-42', $html,
            'Result container ID must match JS getElementById pattern' );
    }

    public function test_metabox_partial_contains_data_post_id() {
        $post     = new \stdClass();
        $post->ID = 42;

        WP_Mock::userFunction( 'get_post_meta', [ 'return' => [] ] );

        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
        $html = ob_get_clean();

        // btn.dataset.postId
        $this->assertStringContainsString( 'data-post-id="42"', $html,
            'data-post-id attribute must be present for JS dataset.postId' );
    }

    public function test_metabox_partial_contains_spinner() {
        $post     = new \stdClass();
        $post->ID = 1;

        WP_Mock::userFunction( 'get_post_meta', [ 'return' => [] ] );

        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
        $html = ob_get_clean();

        // querySelector('.wsoa-spinner')
        $this->assertStringContainsString( 'wsoa-spinner', $html,
            'Spinner element must be present for JS querySelector' );
    }

    public function test_metabox_partial_shows_cached_analysis() {
        $post     = new \stdClass();
        $post->ID = 7;

        $cached = [
            'score'           => 85,
            'verdict'         => '✅ Great title',
            'analysis'        => 'Good length and keyword present.',
            'strengths'       => [ 'Has a number', 'Good length' ],
            'issues'          => [ [ 'severity' => 'warning', 'message' => 'No power word' ] ],
            'recommendations' => [ 'Add a power word' ],
        ];

        WP_Mock::userFunction( 'get_post_meta', [
            'return' => function( $id, $key ) use ( $cached ) {
                if ( $key === '_wsoa_last_analysis' ) return $cached;
                if ( $key === '_wsoa_last_analyzed_title' ) return 'My cached title';
                return '';
            },
        ] );

        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/ws-optimizer-ai-admin-metabox.php';
        $html = ob_get_clean();

        $this->assertStringContainsString( '85',              $html );
        $this->assertStringContainsString( 'Great title',     $html );
        $this->assertStringContainsString( 'Has a number',    $html );
        $this->assertStringContainsString( 'No power word',   $html );
        $this->assertStringContainsString( 'Add a power word', $html );
        $this->assertStringContainsString( 'My cached title', $html );
    }

    // ── ajax_analyze_title — security guards ─────────────────────────────────

    public function test_ajax_analyze_title_rejects_missing_nonce() {
        WP_Mock::userFunction( 'check_ajax_referer', [
            'return' => function () { wp_die( 'bad nonce' ); },
        ] );
        WP_Mock::userFunction( 'wp_die', [
            'return' => function ( $msg ) { throw new \Exception( $msg ); },
        ] );

        $this->expectException( \Exception::class );
        $this->admin->ajax_analyze_title();
    }

    public function test_ajax_analyze_title_rejects_no_capability() {
        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'current_user_can', [ 'args' => [ 'edit_posts' ], 'return' => false ] );

        $sent = null;
        WP_Mock::userFunction( 'wp_send_json_error', [
            'return' => function ( $data ) use ( &$sent ) { $sent = $data; },
        ] );

        $_POST = [ 'nonce' => 'x', 'title' => 'Test', 'post_id' => 0 ];
        $this->admin->ajax_analyze_title();

        $this->assertNotNull( $sent );
        $this->assertArrayHasKey( 'message', $sent );
    }

    public function test_ajax_analyze_title_rejects_empty_title() {
        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return' => function ( $v ) { return trim( $v ); } ] );

        $sent = null;
        WP_Mock::userFunction( 'wp_send_json_error', [
            'return' => function ( $data ) use ( &$sent ) { $sent = $data; },
        ] );

        $_POST = [ 'nonce' => 'x', 'title' => '', 'post_id' => 0 ];
        $this->admin->ajax_analyze_title();

        $this->assertNotNull( $sent );
        $this->assertStringContainsString( 'vide', $sent['message'] );
    }

    // ── ajax_analyze_title — success path ────────────────────────────────────

    public function test_ajax_analyze_title_success_returns_correct_payload() {
        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return' => function ( $v ) { return trim( $v ); } ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );

        $analysis = [
            'score'           => 78,
            'verdict'         => '👍 Good title',
            'analysis'        => 'Decent.',
            'strengths'       => [ 'Has number' ],
            'issues'          => [],
            'recommendations' => [ 'Add power word' ],
        ];

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( json_encode( $analysis ) ),
        ] );

        $success = null;
        WP_Mock::userFunction( 'wp_send_json_success', [
            'return' => function ( $data ) use ( &$success ) { $success = $data; },
        ] );
        WP_Mock::userFunction( 'wp_send_json_error', [ 'return' => null ] );

        $_POST = [ 'nonce' => 'x', 'title' => 'My great SEO title 2024', 'post_id' => 0 ];
        $this->admin->ajax_analyze_title();

        $this->assertNotNull( $success, 'wp_send_json_success was not called' );
        $this->assertArrayHasKey( 'score',           $success );
        $this->assertArrayHasKey( 'verdict',         $success );
        $this->assertArrayHasKey( 'analysis',        $success );
        $this->assertArrayHasKey( 'strengths',       $success );
        $this->assertArrayHasKey( 'issues',          $success );
        $this->assertArrayHasKey( 'recommendations', $success );
        $this->assertEquals( 78, $success['score'] );
    }

    public function test_ajax_analyze_title_success_saves_post_meta() {
        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return' => function ( $v ) { return trim( $v ); } ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );

        $analysis = [ 'score' => 90, 'verdict' => '🔥', 'analysis' => 'x', 'strengths' => [], 'issues' => [], 'recommendations' => [] ];

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( json_encode( $analysis ) ),
        ] );
        WP_Mock::userFunction( 'wp_send_json_success', [ 'return' => null ] );
        WP_Mock::userFunction( 'current_time', [ 'return' => 1700000000 ] );

        $meta_calls = [];
        WP_Mock::userFunction( 'update_post_meta', [
            'return' => function( $id, $key, $val ) use ( &$meta_calls ) {
                $meta_calls[ $key ] = $val;
            },
        ] );

        $_POST = [ 'nonce' => 'x', 'title' => 'Test title', 'post_id' => 99 ];
        $this->admin->ajax_analyze_title();

        $this->assertArrayHasKey( '_wsoa_last_analysis',       $meta_calls );
        $this->assertArrayHasKey( '_wsoa_last_analyzed_title', $meta_calls );
        $this->assertArrayHasKey( '_wsoa_last_analysis_date',  $meta_calls );
        $this->assertEquals( 'Test title', $meta_calls['_wsoa_last_analyzed_title'] );
    }

    public function test_ajax_analyze_title_error_from_analyzer_returns_json_error() {
        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return' => function ( $v ) { return trim( $v ); } ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );

        // Analyzer returns error (invalid JSON from Claude)
        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( 'not json at all' ),
        ] );

        $error = null;
        WP_Mock::userFunction( 'wp_send_json_error', [
            'return' => function ( $data ) use ( &$error ) { $error = $data; },
        ] );
        WP_Mock::userFunction( 'wp_send_json_success', [ 'return' => null ] );

        $_POST = [ 'nonce' => 'x', 'title' => 'Valid title', 'post_id' => 0 ];
        $this->admin->ajax_analyze_title();

        $this->assertNotNull( $error, 'wp_send_json_error must be called on analyzer error' );
        $this->assertArrayHasKey( 'message', $error );
    }

    // ── register_metaboxes ───────────────────────────────────────────────────

    public function test_register_metaboxes_calls_add_meta_box_for_each_type() {
        WP_Mock::userFunction( 'get_option', [
            'args'   => [ 'wsoa_settings', [] ],
            'return' => [ 'post_types' => [ 'post', 'page' ] ],
        ] );

        $calls = [];
        WP_Mock::userFunction( 'add_meta_box', [
            'return' => function ( $id, $title, $cb, $screen ) use ( &$calls ) { $calls[] = $screen; },
        ] );

        $this->admin->register_metaboxes();

        $this->assertContains( 'post', $calls );
        $this->assertContains( 'page', $calls );
        $this->assertCount( 2, $calls );
    }

    public function test_register_metaboxes_does_nothing_with_empty_post_types() {
        WP_Mock::userFunction( 'get_option', [ 'return' => [ 'post_types' => [] ] ] );

        $called = false;
        WP_Mock::userFunction( 'add_meta_box', [
            'return' => function() use ( &$called ) { $called = true; },
        ] );

        $this->admin->register_metaboxes();
        $this->assertFalse( $called );
    }

    // ── add_admin_body_class ─────────────────────────────────────────────────

    public function test_add_admin_body_class_adds_class_on_settings_page() {
        $screen     = new \stdClass();
        $screen->id = 'settings_page_ws-optimizer-ai';
        WP_Mock::userFunction( 'get_current_screen', [ 'return' => $screen ] );

        $result = $this->admin->add_admin_body_class( 'existing-class' );
        $this->assertStringContainsString( 'wsoa-settings-page', $result );
    }

    public function test_add_admin_body_class_does_not_add_class_on_other_pages() {
        $screen     = new \stdClass();
        $screen->id = 'edit-post';
        WP_Mock::userFunction( 'get_current_screen', [ 'return' => $screen ] );

        $result = $this->admin->add_admin_body_class( 'existing-class' );
        $this->assertStringNotContainsString( 'wsoa-settings-page', $result );
    }
}
