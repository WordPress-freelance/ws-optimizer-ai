<?php
/**
 * Autoloader manuel — remplace composer install (bloqué par proxy).
 * Ordre critique : Patchwork → Hamcrest → Mockery → WP_Mock → tests.
 */

// 1. Patchwork — DOIT être chargé en premier (avant tout stub WP)
require_once __DIR__ . '/antecedent/patchwork/Patchwork.php';

// 2. Hamcrest
require_once __DIR__ . '/hamcrest/hamcrest-php/hamcrest/Hamcrest.php';

// 3. Mockery
require_once __DIR__ . '/mockery/mockery/library/Mockery.php';
require_once __DIR__ . '/mockery/mockery/library/helpers.php';

// 4. WP_Mock
require_once __DIR__ . '/10up/wp_mock/php/WP_Mock.php';

// ── PSR-4 autoloader ──────────────────────────────────────────────────────────

spl_autoload_register( function ( $class ) {
    // WP_Mock namespace
    if ( strpos( $class, 'WP_Mock\\' ) === 0 ) {
        $rel  = str_replace( 'WP_Mock\\', '', $class );
        $file = __DIR__ . '/10up/wp_mock/php/WP_Mock/' . str_replace( '\\', '/', $rel ) . '.php';
        if ( file_exists( $file ) ) require_once $file;
        return;
    }

    // Mockery namespace
    if ( strpos( $class, 'Mockery\\' ) === 0 ) {
        $rel  = str_replace( 'Mockery\\', '', $class );
        $file = __DIR__ . '/mockery/mockery/library/Mockery/' . str_replace( '\\', '/', $rel ) . '.php';
        if ( file_exists( $file ) ) require_once $file;
        return;
    }

    // Hamcrest legacy underscore classes
    if ( strpos( $class, 'Hamcrest_' ) === 0 ) {
        $file = __DIR__ . '/hamcrest/hamcrest-php/hamcrest/' . str_replace( '_', '/', $class ) . '.php';
        if ( file_exists( $file ) ) require_once $file;
        return;
    }
    if ( strpos( $class, 'Hamcrest\\' ) === 0 ) {
        $rel  = str_replace( 'Hamcrest\\', '', $class );
        $file = __DIR__ . '/hamcrest/hamcrest-php/hamcrest/Hamcrest/' . str_replace( '\\', '/', $rel ) . '.php';
        if ( file_exists( $file ) ) require_once $file;
        return;
    }
} );

// Tests namespace PSR-4
spl_autoload_register( function ( $class ) {
    $prefix = 'WS_Optimizer_AI\\Tests\\Unit\\';
    if ( strpos( $class, $prefix ) !== 0 ) return;
    $rel  = str_replace( $prefix, '', $class );
    $file = dirname( __DIR__ ) . '/tests/unit/' . str_replace( '\\', '/', $rel ) . '.php';
    if ( file_exists( $file ) ) require_once $file;
} );
