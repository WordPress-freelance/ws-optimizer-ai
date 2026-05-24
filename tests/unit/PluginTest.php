<?php

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI;
use WS_Optimizer_AI_Loader;

class PluginTest extends WebStrategyTestCase {

    public function test_plugin_instantiates_correctly() {
        $plugin = new WS_Optimizer_AI();

        $this->assertInstanceOf( WS_Optimizer_AI::class, $plugin );
    }

    public function test_get_plugin_name() {
        $plugin = new WS_Optimizer_AI();
        $this->assertEquals( WS_OPTIMIZER_AI_SLUG, $plugin->get_plugin_name() );
    }

    public function test_get_version() {
        $plugin = new WS_Optimizer_AI();
        $this->assertEquals( WS_OPTIMIZER_AI_VERSION, $plugin->get_version() );
    }

    public function test_get_loader_returns_loader_instance() {
        $plugin = new WS_Optimizer_AI();
        $this->assertInstanceOf( WS_Optimizer_AI_Loader::class, $plugin->get_loader() );
    }

    public function test_loader_has_admin_hooks_registered() {
        $plugin  = new WS_Optimizer_AI();
        $loader  = $plugin->get_loader();
        $actions = $loader->get_actions();
        $hooks   = array_column( $actions, 'hook' );

        $this->assertContains( 'add_meta_boxes', $hooks );
        $this->assertContains( 'admin_menu', $hooks );
        $this->assertContains( 'wp_ajax_wsoa_analyze', $hooks );
    }

    public function test_loader_has_enqueue_hooks() {
        $plugin  = new WS_Optimizer_AI();
        $loader  = $plugin->get_loader();
        $hooks   = array_column( $loader->get_actions(), 'hook' );

        $this->assertContains( 'admin_enqueue_scripts', $hooks );
        $this->assertContains( 'wp_enqueue_scripts', $hooks );
    }

    public function test_loader_has_i18n_hook() {
        $plugin  = new WS_Optimizer_AI();
        $loader  = $plugin->get_loader();
        $hooks   = array_column( $loader->get_actions(), 'hook' );

        $this->assertContains( 'plugins_loaded', $hooks );
    }

    public function test_run_calls_loader_run() {
        WP_Mock::userFunction( 'add_filter', [ 'return' => true ] );
        WP_Mock::userFunction( 'add_action', [ 'return' => true ] );

        $plugin = new WS_Optimizer_AI();
        // run() ne doit pas lever d'exception
        $plugin->run();
        $this->assertTrue( true );
    }
}
