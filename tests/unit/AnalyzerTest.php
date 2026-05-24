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

    public function test_call_claude_returns_parsed_data_on_success() {
        $json_str = '{"score":90,"verdict":"🏆 Parfait","strengths":["Optimal"],"issues":[],"recommendations":[],"analysis":"Excellent."}';

        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => [ 'content' => [ [ 'text' => $json_str ] ] ],
        ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'test prompt' ] );

        $this->assertTrue( $result['success'] );
        $this->assertEquals( 90, $result['data']['score'] );
    }

    public function test_call_claude_handles_exception() {
        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => function () { throw new \Exception( 'Erreur API' ); },
        ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'test prompt' ] );

        $this->assertArrayHasKey( 'error', $result );
        $this->assertStringContainsString( 'Erreur API', $result['error'] );
    }

    public function test_call_claude_returns_error_on_invalid_response() {
        WP_Mock::userFunction( 'wp_ai_client_prompt', [
            'return' => [ 'content' => [ [ 'text' => 'invalid json response' ] ] ],
        ] );

        $result = $this->invoke_method( $this->analyzer, 'call_claude', [ 'prompt' ] );

        $this->assertArrayHasKey( 'error', $result );
    }
}
