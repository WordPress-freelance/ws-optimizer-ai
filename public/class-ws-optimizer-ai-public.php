<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        // Pas de styles front-end pour ce plugin.
    }

    public function enqueue_scripts() {
        // Pas de scripts front-end pour ce plugin.
    }
}
