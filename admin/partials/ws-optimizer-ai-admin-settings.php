<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}
?>
<div class="ws-admin-wrap">
    <?php require __DIR__ . '/ws-optimizer-ai-admin-header.php'; ?>
    <div class="ws-shell">
        <div class="ws-main">
            <h1 class="ws-page-title">
                <img src="<?php echo esc_url( WS_OPTIMIZER_AI_URL . 'assets/icon-128x128.png' ); ?>" width="32" height="32" class="ws-title-logo" alt="">
                <?php esc_html_e( 'WS SEO Title AI', 'ws-optimizer-ai' ); ?>
                <span><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></span>
            </h1>

            <?php settings_errors( 'wsoa_settings_group' ); ?>

            <div class="ws-card">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'wsoa_settings_group' );
                    do_settings_sections( 'ws-optimizer-ai' );
                    ?>
                    <button type="submit" class="ws-btn-save"><?php esc_html_e( 'Enregistrer les réglages', 'ws-optimizer-ai' ); ?></button>
                </form>
            </div>

            <div class="ws-card dk">
                <div class="ws-card-title">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php esc_html_e( 'Prérequis', 'ws-optimizer-ai' ); ?>
                </div>
                <p class="ws-info-text"><?php esc_html_e( 'Ce plugin nécessite WordPress avec le module AI Client activé et un compte Anthropic configuré (Claude).', 'ws-optimizer-ai' ); ?></p>
                <a href="https://wordpress-freelance.com/plugins/ws-optimizer-ai/" target="_blank" rel="noopener" class="ws-filter-btn">
                    <?php esc_html_e( 'Documentation →', 'ws-optimizer-ai' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>
