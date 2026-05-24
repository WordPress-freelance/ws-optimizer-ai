<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}
?>
<div class="wrap wsoa-wrap">
    <h1><?php esc_html_e( 'WS SEO Title AI', 'ws-optimizer-ai' ); ?></h1>
    <p class="wsoa-subtitle"><?php esc_html_e( 'Analysez vos titres SEO avec Claude directement dans l\'éditeur.', 'ws-optimizer-ai' ); ?></p>

    <?php settings_errors( 'wsoa_settings_group' ); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wsoa_settings_group' );
        do_settings_sections( 'ws-optimizer-ai' );
        submit_button( __( 'Enregistrer les réglages', 'ws-optimizer-ai' ) );
        ?>
    </form>

    <div class="wsoa-info-box">
        <h3><?php esc_html_e( 'Prérequis', 'ws-optimizer-ai' ); ?></h3>
        <p><?php esc_html_e( 'Ce plugin nécessite WordPress avec le module AI Client activé et un compte Anthropic configuré (Claude).', 'ws-optimizer-ai' ); ?></p>
        <p>
            <a href="https://wordpress-freelance.com/plugins/ws-optimizer-ai/" target="_blank" rel="noopener">
                <?php esc_html_e( 'Documentation →', 'ws-optimizer-ai' ); ?>
            </a>
        </p>
    </div>
</div>
