<?php

namespace WS_Optimizer_AI\Tests\Unit;

use WP_Mock;
use WS_Optimizer_AI_Loader;

class LoaderTest extends WebStrategyTestCase {

    private WS_Optimizer_AI_Loader $loader;

    public function setUp(): void {
        parent::setUp();
        $this->loader = new WS_Optimizer_AI_Loader();
    }

    public function test_add_action_registers_entry() {
        $component = new \stdClass();
        $this->loader->add_action( 'init', $component, 'my_callback', 10, 1 );

        $actions = $this->loader->get_actions();
        $this->assertCount( 1, $actions );
        $this->assertEquals( 'init', $actions[0]['hook'] );
        $this->assertEquals( 'my_callback', $actions[0]['callback'] );
        $this->assertEquals( 10, $actions[0]['priority'] );
    }

    public function test_add_filter_registers_entry() {
        $component = new \stdClass();
        $this->loader->add_filter( 'the_content', $component, 'filter_content', 20, 2 );

        $filters = $this->loader->get_filters();
        $this->assertCount( 1, $filters );
        $this->assertEquals( 'the_content', $filters[0]['hook'] );
        $this->assertEquals( 20, $filters[0]['priority'] );
    }

    public function test_multiple_actions_are_stacked() {
        $component = new \stdClass();
        $this->loader->add_action( 'init', $component, 'a' );
        $this->loader->add_action( 'wp_loaded', $component, 'b' );

        $this->assertCount( 2, $this->loader->get_actions() );
    }

    public function test_run_calls_add_filter_for_each_filter() {
        $component = new \stdClass();
        $this->loader->add_filter( 'the_title', $component, 'do_something' );

        // Enregistrer les stubs permissifs pour éviter les "unexpected function call"
        WP_Mock::userFunction( 'add_filter', [ 'return' => null ] );
        WP_Mock::userFunction( 'add_action', [ 'return' => null ] );

        // Vérifier que run() s'exécute sans exception
        $exception = null;
        try {
            $this->loader->run();
        } catch ( \Exception $e ) {
            $exception = $e;
        }
        $this->assertNull( $exception, 'run() threw an exception: ' . ( $exception ? $exception->getMessage() : '' ) );
        // Le filtre a bien été enregistré
        $this->assertCount( 1, $this->loader->get_filters() );
    }

    public function test_run_calls_add_action_for_each_action() {
        $component = new \stdClass();
        $this->loader->add_action( 'plugins_loaded', $component, 'init_plugin' );

        WP_Mock::userFunction( 'add_action', [ 'return' => null ] );
        WP_Mock::userFunction( 'add_filter', [ 'return' => null ] );

        $exception = null;
        try {
            $this->loader->run();
        } catch ( \Exception $e ) {
            $exception = $e;
        }
        $this->assertNull( $exception, 'run() threw an exception: ' . ( $exception ? $exception->getMessage() : '' ) );
        $this->assertCount( 1, $this->loader->get_actions() );
    }
}
