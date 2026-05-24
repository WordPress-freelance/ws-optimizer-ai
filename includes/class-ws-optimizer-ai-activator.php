<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Activator {

    public static function activate() {
        if ( ! get_option( 'wsoa_settings' ) ) {
            update_option( 'wsoa_settings', [
                'post_types' => [ 'post', 'page' ],
                'model'      => 'claude-opus-4-6',
                'max_tokens' => 800,
            ] );
        }
    }
}
