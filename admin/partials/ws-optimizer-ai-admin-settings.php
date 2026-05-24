<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}

$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
$base_url    = admin_url( 'options-general.php?page=ws-optimizer-ai' );
?>
<div class="wrap wsoa-wrap">
    <h1><?php esc_html_e( 'WS SEO Title AI', 'ws-optimizer-ai' ); ?></h1>

    <nav class="wsoa-tabs">
        <a href="<?php echo esc_url( $base_url ); ?>" class="wsoa-tab <?php echo $current_tab === 'settings' ? 'wsoa-tab--active' : ''; ?>">
            <?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=ws-optimizer-ai&tab=logs' ) ); ?>" class="wsoa-tab">
            <?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?>
        </a>
    </nav>

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
