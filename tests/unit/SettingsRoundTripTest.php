<?php
/**
 * Round-trip des réglages de l'admin : toute valeur sauvegardée doit être
 * réaffichée à l'identique au rechargement de la page.
 *
 * Chaque test simule le cycle complet :
 *   1. SAISIE   — $_POST + handler admin-post (persistance via update_option)
 *   2. REFRESH  — rendu du partial qui relit l'option et re-coche les champs
 *
 * Le store d'options est simulé en mémoire (closures get_option/update_option),
 * si bien que la valeur lue au rendu est exactement celle écrite par le handler.
 *
 * @package WS_Optimizer_AI
 */

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI_Admin;

/** Stoppe l'exécution du handler avant le `exit;` qui suit la redirection. */
class WSOA_RedirectHalt extends \Exception {}

class SettingsRoundTripTest extends WebStrategyTestCase {

    /** @var array Store d'options en mémoire partagé par get/update_option. */
    private $store = [];

    // -------------------------------------------------------------------------
    // Harnais
    // -------------------------------------------------------------------------

    /** Branche get_option/update_option sur un store mémoire mutable. */
    private function bind_options_store( array $initial = [] ) {
        $this->store = $initial;
        WP_Mock::userFunction( 'get_option', [
            'return' => function ( $key, $default = false ) {
                return array_key_exists( $key, $this->store ) ? $this->store[ $key ] : $default;
            },
        ] );
        WP_Mock::userFunction( 'update_option', [
            'return' => function ( $key, $value ) {
                $this->store[ $key ] = $value;
                return true;
            },
        ] );
    }

    /** Mocks de la redirection : on lève une exception pour ne pas atteindre exit. */
    private function halt_on_redirect() {
        WP_Mock::userFunction( 'add_query_arg', [ 'return' => '' ] );
        WP_Mock::userFunction( 'admin_url', [ 'return' => 'https://example.com/wp-admin/admin-post.php' ] );
        WP_Mock::userFunction( 'home_url', [ 'return' => 'https://example.com' ] );
        WP_Mock::userFunction( 'wp_parse_url', [ 'return' => 'example.com' ] );
        WP_Mock::userFunction( 'wp_safe_redirect', [
            'return' => function () { throw new WSOA_RedirectHalt(); },
        ] );
    }

    /** Faux types publics pour get_post_types(). */
    private function fake_public_post_types() {
        $types = [];
        foreach ( [ 'post', 'page', 'product', 'attachment' ] as $n ) {
            $o                        = new \stdClass();
            $o->name                  = $n;
            $o->labels                = new \stdClass();
            $o->labels->singular_name = ucfirst( $n );
            $types[ $n ]              = $o;
        }
        return $types;
    }

    /** Mocks nécessaires au rendu d'un partial (hors current_user_can). */
    private function bind_render_commons() {
        WP_Mock::userFunction( 'wp_nonce_field', [ 'return' => null ] );
        WP_Mock::userFunction( 'wp_create_nonce', [ 'return' => 'nonce' ] );
        WP_Mock::userFunction( 'wp_json_encode', [ 'return' => function ( $v ) { return json_encode( $v ); } ] );
        // Reproduit fidèlement le comportement de checked() de WordPress.
        WP_Mock::userFunction( 'checked', [
            'return' => function ( $checked, $current = true, $echo = true ) {
                $out = ( (string) $checked === (string) $current ) ? " checked='checked'" : '';
                if ( $echo ) {
                    echo $out;
                }
                return $out;
            },
        ] );
    }

    private function render( $partial ) {
        ob_start();
        include WS_OPTIMIZER_AI_PATH . 'admin/partials/' . $partial;
        return ob_get_clean();
    }

    /** Assert qu'une checkbox identifiée par sa value est cochée (ou non). */
    private function assertCheckbox( $html, $value, $expected, $msg = '' ) {
        $needle = 'value="' . $value . '"';
        $pos    = strpos( $html, $needle );
        $this->assertNotFalse( $pos, "Checkbox value=\"$value\" introuvable dans le rendu" );

        $end     = strpos( $html, '>', $pos );
        $segment = substr( $html, $pos, $end - $pos );

        if ( $expected ) {
            $this->assertStringContainsString( 'checked', $segment, $msg ?: "value=\"$value\" devrait être cochée après refresh" );
        } else {
            $this->assertStringNotContainsString( 'checked', $segment, $msg ?: "value=\"$value\" ne devrait pas être cochée après refresh" );
        }
    }

    // -------------------------------------------------------------------------
    // Réglages — types de publication
    // -------------------------------------------------------------------------

    public function test_settings_post_types_round_trip() {
        $admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '2.1.0' );

        $this->bind_options_store( [ 'wsoa_settings' => [] ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'check_admin_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        $this->halt_on_redirect();

        // 1. SAISIE : l'utilisateur coche post + product (et laisse page + attachment décochés).
        $_POST['wsoa_post_types'] = [ 'post', 'product' ];
        try {
            $admin->handle_save_settings();
            $this->fail( 'Le handler aurait dû rediriger.' );
        } catch ( WSOA_RedirectHalt $e ) {
            // attendu
        }

        // La persistance reflète exactement la saisie.
        $this->assertSame( [ 'post', 'product' ], $this->store['wsoa_settings']['post_types'] );

        // 2. REFRESH : le partial relit l'option et re-coche à l'identique.
        $_GET = [];
        $this->bind_render_commons();
        WP_Mock::userFunction( 'get_post_types', [ 'return' => $this->fake_public_post_types() ] );

        $html = $this->render( 'ws-optimizer-ai-admin-settings.php' );

        $this->assertCheckbox( $html, 'post', true );
        $this->assertCheckbox( $html, 'product', true );
        $this->assertCheckbox( $html, 'page', false );
        $this->assertCheckbox( $html, 'attachment', false );
    }

    public function test_settings_unchecking_all_persists_and_renders_empty() {
        $admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '2.1.0' );

        $this->bind_options_store( [ 'wsoa_settings' => [ 'post_types' => [ 'post', 'page' ] ] ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'check_admin_referer', [ 'return' => true ] );
        WP_Mock::userFunction( 'wp_unslash', [ 'return' => function ( $v ) { return $v; } ] );
        $this->halt_on_redirect();

        // SAISIE : tout décoché → le champ n'est pas envoyé.
        unset( $_POST['wsoa_post_types'] );
        try {
            $admin->handle_save_settings();
        } catch ( WSOA_RedirectHalt $e ) {
        }

        // L'état vide est persisté tel quel (pas de retour aux défauts).
        $this->assertSame( [], $this->store['wsoa_settings']['post_types'] );

        // REFRESH : aucune case cochée.
        $_GET = [];
        $this->bind_render_commons();
        WP_Mock::userFunction( 'get_post_types', [ 'return' => $this->fake_public_post_types() ] );

        $html = $this->render( 'ws-optimizer-ai-admin-settings.php' );

        $this->assertCheckbox( $html, 'post', false );
        $this->assertCheckbox( $html, 'page', false );
        $this->assertCheckbox( $html, 'product', false );
        $this->assertCheckbox( $html, 'attachment', false );
    }

    // -------------------------------------------------------------------------
    // AI Logs — toggle de capture
    // -------------------------------------------------------------------------

    public function test_logs_capture_toggle_on_round_trip() {
        $admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '2.1.0' );

        $this->bind_options_store( [ 'wsoa_capture_logs' => false, 'wsoa_debug_log' => [] ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'check_admin_referer', [ 'return' => true ] );
        $this->halt_on_redirect();

        // SAISIE : activation du toggle.
        $_POST['wsoa_capture_logs'] = '1';
        try {
            $admin->handle_save_logs_settings();
        } catch ( WSOA_RedirectHalt $e ) {
        }

        $this->assertTrue( $this->store['wsoa_capture_logs'] );

        // REFRESH : le toggle est ON.
        $_GET = [];
        $this->bind_render_commons();
        $html = $this->render( 'ws-optimizer-ai-admin-logs.php' );

        $this->assertCheckbox( $html, '1', true, 'Le toggle de capture doit rester ON après refresh' );
    }

    public function test_logs_capture_toggle_off_round_trip() {
        $admin = new WS_Optimizer_AI_Admin( 'ws-optimizer-ai', '2.1.0' );

        $this->bind_options_store( [ 'wsoa_capture_logs' => true, 'wsoa_debug_log' => [] ] );
        WP_Mock::userFunction( 'current_user_can', [ 'return' => true ] );
        WP_Mock::userFunction( 'check_admin_referer', [ 'return' => true ] );
        $this->halt_on_redirect();

        // SAISIE : désactivation (champ absent du POST).
        unset( $_POST['wsoa_capture_logs'] );
        try {
            $admin->handle_save_logs_settings();
        } catch ( WSOA_RedirectHalt $e ) {
        }

        $this->assertFalse( $this->store['wsoa_capture_logs'] );

        // REFRESH : le toggle est OFF.
        $_GET = [];
        $this->bind_render_commons();
        $html = $this->render( 'ws-optimizer-ai-admin-logs.php' );

        $this->assertCheckbox( $html, '1', false, 'Le toggle de capture doit rester OFF après refresh' );
    }
}
