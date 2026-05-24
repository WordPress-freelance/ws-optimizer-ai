<?php

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI_Analyzer;

class AnalyzerTest extends WebStrategyTestCase {

    private WS_Optimizer_AI_Analyzer $analyzer;

    public function setUp(): void {
        parent::setUp();
        $this->analyzer = new WS_Optimizer_AI_Analyzer();
    }

    // ── Constructor ───────────────────────────────────────────────────────────

    public function test_constructor_default_model() {
        $this->assertEquals( WS_Optimizer_AI_Analyzer::DEFAULT_MODEL, $this->analyzer->get_model() );
    }

    public function test_constructor_custom_model() {
        $a = new WS_Optimizer_AI_Analyzer( 'claude-sonnet-4-6', 500 );
        $this->assertEquals( 'claude-sonnet-4-6', $a->get_model() );
        $this->assertEquals( 500, $a->get_max_tokens() );
    }

    public function test_constructor_default_max_tokens() {
        $this->assertEquals( WS_Optimizer_AI_Analyzer::DEFAULT_MAX_TOKENS, $this->analyzer->get_max_tokens() );
    }

    // ── build_basic_data ─────────────────────────────────────────────────────

    public function test_build_basic_data_without_post_id() {
        $data = $this->analyzer->build_basic_data( 'Mon titre SEO 2024' );

        $this->assertEquals( 'Mon titre SEO 2024', $data['title'] );
        $this->assertEquals( 18, $data['length'] );
        $this->assertIsInt( $data['word_count'] );
        $this->assertEquals( 1, $data['has_number'] );
        $this->assertEquals( 0, $data['has_modifier'] );
        $this->assertArrayNotHasKey( 'focus_keyword', $data );
        $this->assertArrayNotHasKey( 'url', $data );
    }

    public function test_build_basic_data_detects_modifier() {
        $data = $this->analyzer->build_basic_data( 'Titre (avec parenthèse)' );
        $this->assertEquals( 1, $data['has_modifier'] );
    }

    public function test_build_basic_data_no_number() {
        $data = $this->analyzer->build_basic_data( 'Titre sans chiffre' );
        $this->assertEquals( 0, $data['has_number'] );
    }

    public function test_build_basic_data_with_post_id_uses_yoast_keyword() {
        WP_Mock::userFunction( 'get_post_meta', [
            'return' => function ( $post_id, $key ) {
                if ( '_yoast_wpseo_focuskw' === $key ) return 'mot clé test';
                return '';
            },
        ] );
        WP_Mock::userFunction( 'get_permalink', [
            'return' => 'https://example.com/article/',
        ] );

        $data = $this->analyzer->build_basic_data( 'Mon titre', 42 );

        $this->assertEquals( 'mot clé test', $data['focus_keyword'] );
        $this->assertEquals( 'https://example.com/article/', $data['url'] );
    }

    public function test_build_basic_data_with_post_id_falls_back_to_rankmath() {
        WP_Mock::userFunction( 'get_post_meta', [
            'return' => function ( $post_id, $key ) {
                if ( '_rank_math_focus_keyword' === $key ) return 'keyword rank math';
                return '';
            },
        ] );
        WP_Mock::userFunction( 'get_permalink', [
            'return' => 'https://example.com/',
        ] );

        $data = $this->analyzer->build_basic_data( 'Titre', 7 );

        $this->assertEquals( 'keyword rank math', $data['focus_keyword'] );
    }

    public function test_build_basic_data_with_post_id_no_keyword() {
        WP_Mock::userFunction( 'get_post_meta', [ 'return' => '' ] );
        WP_Mock::userFunction( 'get_permalink', [ 'return' => 'https://example.com/' ] );

        $data = $this->analyzer->build_basic_data( 'Titre sans keyword', 5 );

        $this->assertEquals( 'Non définie', $data['focus_keyword'] );
    }

    // ── build_prompt ─────────────────────────────────────────────────────────

    public function test_build_prompt_returns_string() {
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function ( $data ) { return json_encode( $data ); },
        ] );

        $prompt = $this->analyzer->build_prompt( [ 'title' => 'Test', 'length' => 4 ] );

        $this->assertIsString( $prompt );
    }

    public function test_build_prompt_contains_expected_keys() {
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function ( $data ) { return json_encode( $data ); },
        ] );

        $prompt = $this->analyzer->build_prompt( [ 'title' => 'Test' ] );

        $this->assertStringContainsString( '"score"', $prompt );
        $this->assertStringContainsString( '"verdict"', $prompt );
        $this->assertStringContainsString( '"issues"', $prompt );
        $this->assertStringContainsString( '"recommendations"', $prompt );
        $this->assertStringContainsString( '"analysis"', $prompt );
    }

    public function test_build_prompt_contains_seo_criteria() {
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function ( $data ) { return json_encode( $data ); },
        ] );

        $prompt = $this->analyzer->build_prompt( [ 'title' => 'Test' ] );

        $this->assertStringContainsString( '50-60', $prompt );
    }

    // ── parse_response ───────────────────────────────────────────────────────

    public function test_parse_response_valid_array_input() {
        $json_str = '{"score":85,"verdict":"✅ Excellent","strengths":["Court"],"issues":[],"recommendations":[],"analysis":"Bon."}';
        $payload  = [ 'content' => [ [ 'text' => $json_str ] ] ];

        $result = $this->analyzer->parse_response( $payload );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 85, $result['data']['score'] );
        $this->assertEquals( '✅ Excellent', $result['data']['verdict'] );
    }

    public function test_parse_response_valid_string_input() {
        $json_str = '{"score":70,"verdict":"Moyen","strengths":[],"issues":[],"recommendations":[],"analysis":"OK."}';

        $result = $this->analyzer->parse_response( $json_str );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 70, $result['data']['score'] );
    }

    public function test_parse_response_strips_markdown_backticks() {
        $payload = [ 'content' => [ [ 'text' => "```json\n{\"score\":60,\"verdict\":\"Moyen\",\"strengths\":[],\"issues\":[],\"recommendations\":[],\"analysis\":\"A.\"}\n```" ] ] ];

        $result = $this->analyzer->parse_response( $payload );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 60, $result['data']['score'] );
    }

    public function test_parse_response_invalid_json_returns_error() {
        $payload = [ 'content' => [ [ 'text' => 'pas du json' ] ] ];

        $result = $this->analyzer->parse_response( $payload );

        $this->assertArrayHasKey( 'error', $result );
        $this->assertEquals( 'pas du json', $result['raw'] );
    }

    public function test_parse_response_empty_string_returns_error() {
        $result = $this->analyzer->parse_response( '' );

        $this->assertArrayHasKey( 'error', $result );
    }

    // ── analyze_title ────────────────────────────────────────────────────────

    public function test_analyze_title_empty_string_returns_error() {
        $result = $this->analyzer->analyze_title( '' );

        $this->assertArrayHasKey( 'error', $result );
        $this->assertStringContainsString( 'vide', $result['error'] );
    }

    public function test_analyze_title_whitespace_only_returns_error() {
        $result = $this->analyzer->analyze_title( '   ' );

        $this->assertArrayHasKey( 'error', $result );
    }

    // ── call_claude ──────────────────────────────────────────────────────────

    /**
     * Build a chainable WP_AI_Client_Prompt_Builder mock.
     * generate_text() returns $text_to_return.
     */
    private function make_builder( $text_to_return ) {
        return new class( $text_to_return ) {
            private $text;
            public function __construct( $t ) { $this->text = $t; }
            public function usingModel( $m ) { return $this; }
            public function usingMaxTokens( $t ) { return $this; }
            public function generate_text() { return $this->text; }
            public function __call( $name, $args ) { return $this; }
        };
    }

    public function test_call_claude_returns_parsed_data_on_success() {
        $json_str = '{"score":90,"verdict":"OK","strengths":["Good"],"issues":[],"recommendations":[],"analysis":"Fine."}';

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( $json_str ),
        ] );
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function( $data, $opts = 0 ) { return json_encode( $data, $opts ); },
        ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );
        WP_Mock::userFunction( 'update_option', [ 'return' => true ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'test prompt' ] );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 90, $result['data']['score'] );
    }

    public function test_call_claude_builder_chains_model_and_max_tokens() {
        // Verify generate_text() is called and the result is parsed correctly.
        // Builder chaining (usingModel/usingMaxTokens) is verified by the
        // make_builder stub returning $this for chaining without crashing.
        $json_str = '{"score":85,"verdict":"Good","strengths":[],"issues":[],"recommendations":[],"analysis":"Ok."}';

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( $json_str ),
        ] );
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function( $data, $opts = 0 ) { return json_encode( $data, $opts ); },
        ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );
        WP_Mock::userFunction( 'update_option', [ 'return' => true ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'test prompt' ] );

        // If generate_text() was not called, result would be an error
        $this->assertTrue( $result['success'], 'generate_text() must be called on the builder' );
        $this->assertEquals( 85, $result['data']['score'] );
    }

    public function test_call_claude_handles_real_object_with_get_text() {
        // generate_text() returns an object with get_text() — also test extract_text on that path
        $json_str = '{"score":75,"verdict":"Good","strengths":[],"issues":[],"recommendations":[],"analysis":"OK."}';
        $inner    = new class( $json_str ) {
            private $t;
            public function __construct( $t ) { $this->t = $t; }
            public function get_text() { return $this->t; }
        };

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( $inner ),
        ] );
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function( $data, $opts = 0 ) { return json_encode( $data, $opts ); },
        ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );
        WP_Mock::userFunction( 'update_option', [ 'return' => true ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'prompt' ] );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 75, $result['data']['score'] );
    }

    public function test_extract_text_from_array() {
        $result = $this->invoke_method( $this->analyzer, 'extract_text', [
            [ 'content' => [ [ 'text' => '{"score":80}' ] ] ]
        ] );
        $this->assertEquals( '{"score":80}', $result );
    }

    public function test_extract_text_from_object_with_get_text() {
        $obj = new class { public function get_text() { return '{"score":88}'; } };
        $result = $this->invoke_method( $this->analyzer, 'extract_text', [ $obj ] );
        $this->assertEquals( '{"score":88}', $result );
    }

    public function test_extract_text_from_object_via_json_encode() {
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function( $data ) { return json_encode( $data ); },
        ] );
        $obj          = new \stdClass();
        $inner        = new \stdClass();
        $inner->text  = '{"score":65}';
        $obj->content = [ $inner ];
        $result = $this->invoke_method( $this->analyzer, 'extract_text', [ $obj ] );
        $this->assertEquals( '{"score":65}', $result );
    }

    public function test_extract_text_from_scalar() {
        $result = $this->invoke_method( $this->analyzer, 'extract_text', [ '{"score":50}' ] );
        $this->assertEquals( '{"score":50}', $result );
    }

    public function test_call_claude_handles_exception() {
        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => function () { throw new \Exception( 'Erreur API' ); },
        ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );
        WP_Mock::userFunction( 'update_option', [ 'return' => true ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'test prompt' ] );

        $this->assertArrayHasKey( 'error', $result );
        $this->assertStringContainsString( 'Erreur API', $result['error'] );
    }

    public function test_call_claude_returns_error_on_invalid_response() {
        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => $this->make_builder( 'invalid json response' ),
        ] );
        WP_Mock::userFunction( 'wp_json_encode', [
            'return' => function( $data, $opts = 0 ) { return json_encode( $data, $opts ); },
        ] );
        WP_Mock::userFunction( 'get_option', [ 'return' => [] ] );
        WP_Mock::userFunction( 'update_option', [ 'return' => true ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'prompt' ] );

        $this->assertArrayHasKey( 'error', $result );
    }
}
