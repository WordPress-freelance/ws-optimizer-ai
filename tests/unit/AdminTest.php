<?php

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI_Admin;

class AdminTest extends WebStrategyTestCase {

    private WS_Optimizer_AI_Admin $admin;

    public function setUp(): void {
        parent::setUp();
        $this->admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '1.0.0' );
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
        $input    = [ 'post_types' => [ 'post', 'page', 'product' ], 'model' => 'claude-opus-4-6' ];
        $result   = $this->admin->sanitize_settings( $input );

        $this->assertContains( 'post', $result['post_types'] );
        $this->assertContains( 'product', $result['post_types'] );
    }

    public function test_sanitize_settings_defaults_to_post_page_when_no_post_types() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'claude-opus-4-6' ] );

        $this->assertEquals( [ 'post', 'page' ], $result['post_types'] );
    }

    public function test_sanitize_settings_rejects_invalid_model() {
        $result = $this->admin->sanitize_settings( [ 'model' => 'gpt-99-turbo' ] );

        $this->assertEquals( \WS_Optimizer_AI_Analyzer::DEFAULT_MODEL, $result['model'] );
    }

    public function test_sanitize_settings_accepts_sonnet_model() {
        $result = $this->admin->sanitize_settings( [
            'model'      => 'claude-sonnet-4-6',
            'post_types' => [ 'post' ],
        ] );

        $this->assertEquals( 'claude-sonnet-4-6', $result['model'] );
    }

    public function test_sanitize_settings_sanitizes_post_type_keys() {
        $result = $this->admin->sanitize_settings( [
            'post_types' => [ 'post', 'MY INVALID TYPE!' ],
            'model'      => 'claude-opus-4-6',
        ] );

        // sanitize_key strips uppercase and special chars
        $this->assertNotContains( 'MY INVALID TYPE!', $result['post_types'] );
    }

    // ── ajax_analyze_title ───────────────────────────────────────────────────

    public function test_ajax_analyze_title_rejects_missing_nonce() {
        $sent = null;
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
        WP_Mock::userFunction( 'current_user_can', [
            'args'   => [ 'edit_posts' ],
            'return' => false,
        ] );

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
        WP_Mock::userFunction( 'wp_unslash', [
            'return' => function ( $v ) { return $v; },
        ] );
        WP_Mock::userFunction( 'sanitize_text_field', [
            'return' => function ( $v ) { return trim( strip_tags( $v ) ); },
        ] );

        $sent = null;
        WP_Mock::userFunction( 'wp_send_json_error', [
            'return' => function ( $data ) use ( &$sent ) { $sent = $data; },
        ] );

        $_POST = [ 'nonce' => 'x', 'title' => '', 'post_id' => 0 ];
        $this->admin->ajax_analyze_title();

        $this->assertNotNull( $sent );
        $this->assertStringContainsString( 'vide', $sent['message'] );
    }

    // ── register_metaboxes ───────────────────────────────────────────────────

    public function test_register_metaboxes_calls_add_meta_box_for_each_type() {
        WP_Mock::userFunction( 'get_option', [
            'args'   => [ 'wsoa_settings', [] ],
            'return' => [ 'post_types' => [ 'post', 'page' ] ],
        ] );

        $calls = [];
        WP_Mock::userFunction( 'add_meta_box', [
            'return' => function ( $id, $title, $cb, $screen ) use ( &$calls ) {
                $calls[] = $screen;
            },
        ] );

        $this->admin->register_metaboxes();

        $this->assertContains( 'post', $calls );
        $this->assertContains( 'page', $calls );
        $this->assertCount( 2, $calls );
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
        $this->assertEquals( 'existing-class', $result );
    }
}
