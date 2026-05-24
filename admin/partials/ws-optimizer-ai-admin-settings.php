<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}
?>
<div class="wrap ws-admin-wrap">
  <?php include __DIR__ . '/ws-optimizer-ai-admin-header.php'; ?>
  <main class="ws-main">
    <h1 class="ws-page-title">
      <svg class="ws-title-logo" width="36" height="36" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="32" height="32" rx="7" fill="#221D32"/><circle cx="16" cy="13" r="6" stroke="#7C5CBF" stroke-width="1.5"/><path d="M13 13h6M16 10v6" stroke="#9B8EC4" stroke-width="1.5" stroke-linecap="round"/><path d="M8 24c0-3 3.5-5 8-5s8 2 8 5" stroke="#7C5CBF" stroke-width="1.5" stroke-linecap="round"/><circle cx="24" cy="9" r="2" fill="#9B8EC4"/></svg>
      <?php esc_html_e( 'WS SEO Title AI', 'ws-optimizer-ai' ); ?> <span><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></span>
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
  </main>
</div>
