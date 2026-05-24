<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_i18n {

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'ws-optimizer-ai',
            false,
            dirname( plugin_basename( WS_OPTIMIZER_AI_FILE ) ) . '/languages/'
        );
    }
}
