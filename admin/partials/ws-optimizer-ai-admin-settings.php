<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}
$settings   = get_option( 'wsoa_settings', [] );
$post_types = $settings['post_types'] ?? [ 'post', 'page' ];
$all_types  = get_post_types( [ 'public' => true ], 'objects' );
?>
<div class="wrap ws-admin-wrap">
  <?php include __DIR__ . '/ws-optimizer-ai-admin-header.php'; ?>
  <main class="ws-main">
    <h1 class="ws-page-title">
      <svg class="ws-title-logo" width="36" height="36" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="32" height="32" rx="7" fill="#221D32"/><rect x="7" y="9" width="18" height="3" rx="1.5" fill="#7C5CBF"/><rect x="7" y="15" width="12" height="2" rx="1" fill="#9B8EC4"/><rect x="7" y="20" width="8" height="2" rx="1" fill="#4A4260"/><circle cx="24" cy="22" r="4" fill="#221D32" stroke="#7C5CBF" stroke-width="1.5"/><path d="M23 22l1 1 2-2" stroke="#9B8EC4" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      WS SEO Title AI <span><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></span>
    </h1>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="ws-notice ws-notice-ok"><?php esc_html_e( '✅ Réglages sauvegardés.', 'ws-optimizer-ai' ); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <?php wp_nonce_field( 'wsoa_save_settings' ); ?>
      <input type="hidden" name="action" value="wsoa_save_settings">

      <div class="ws-card">
        <div class="ws-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          <?php esc_html_e( 'Types de publication', 'ws-optimizer-ai' ); ?>
        </div>
        <p class="ws-field-hint" style="margin-bottom:12px;"><?php esc_html_e( 'Affiche la metabox d\'analyse dans l\'éditeur pour ces types de contenu.', 'ws-optimizer-ai' ); ?></p>
        <div class="ws-days-grid">
          <?php foreach ( $all_types as $pt ) : ?>
          <label class="ws-day-check">
            <input type="checkbox" name="wsoa_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>"
              <?php checked( in_array( $pt->name, $post_types, true ) ); ?>>
            <span><?php echo esc_html( $pt->labels->singular_name ); ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="ws-card dk">
        <div class="ws-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php esc_html_e( 'Prérequis', 'ws-optimizer-ai' ); ?>
        </div>
        <p class="ws-info-text"><?php esc_html_e( 'Ce plugin nécessite WordPress avec le module AI Client activé et un compte Anthropic configuré (Claude).', 'ws-optimizer-ai' ); ?></p>
        <a href="https://wordpress-freelance.com/plugins/ws-optimizer-ai/" target="_blank" rel="noopener" class="ws-filter-btn">
          <?php esc_html_e( 'Documentation →', 'ws-optimizer-ai' ); ?>
        </a>
      </div>

      <button type="submit" class="ws-btn-save">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        <?php esc_html_e( 'Sauvegarder les réglages', 'ws-optimizer-ai' ); ?>
      </button>
    </form>
  </main>
</div>
