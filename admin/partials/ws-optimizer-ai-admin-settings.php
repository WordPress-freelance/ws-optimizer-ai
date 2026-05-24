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

    <header class="ws-page-head">
      <h1 class="ws-page-title">
        <svg class="ws-title-logo" viewBox="0 0 34 34" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <rect width="34" height="34" rx="9" fill="#221D32"/>
          <rect x="7" y="9" width="15" height="2.6" rx="1.3" fill="#7C5CBF"/>
          <rect x="7" y="14.6" width="11" height="2.2" rx="1.1" fill="#5B4D9C"/>
          <rect x="7" y="19.6" width="13" height="2.2" rx="1.1" fill="#463A78"/>
          <path d="M26 6.6l1.45 3.25L30.7 11.3l-3.25 1.45L26 16l-1.45-3.25L21.3 11.3l3.25-1.45z" fill="#A899D4"/>
        </svg>
        <span class="ws-page-title__name">WS SEO Title AI</span>
        <span class="ws-page-title__section"><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></span>
      </h1>
      <p class="ws-page-sub"><?php esc_html_e( 'Analyse de vos titres SEO avec Claude, directement dans l\'éditeur.', 'ws-optimizer-ai' ); ?></p>
    </header>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="ws-notice ws-notice-ok">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
      <?php esc_html_e( 'Réglages sauvegardés.', 'ws-optimizer-ai' ); ?>
    </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <?php wp_nonce_field( 'wsoa_save_settings' ); ?>
      <input type="hidden" name="action" value="wsoa_save_settings">

      <section class="ws-card">
        <div class="ws-card-head">
          <span class="ws-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          </span>
          <div>
            <h2 class="ws-card-title"><?php esc_html_e( 'Types de publication', 'ws-optimizer-ai' ); ?></h2>
            <p class="ws-card-desc"><?php esc_html_e( 'Affiche la metabox d\'analyse dans l\'éditeur pour ces types de contenu.', 'ws-optimizer-ai' ); ?></p>
          </div>
        </div>
        <div class="ws-pills">
          <?php foreach ( $all_types as $pt ) : ?>
          <label class="ws-pill">
            <input type="checkbox" name="wsoa_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>"
              <?php checked( in_array( $pt->name, $post_types, true ) ); ?>>
            <span><?php echo esc_html( $pt->labels->singular_name ); ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="ws-card ws-card--dk">
        <div class="ws-card-head">
          <span class="ws-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
          <div>
            <h2 class="ws-card-title"><?php esc_html_e( 'Prérequis', 'ws-optimizer-ai' ); ?></h2>
            <p class="ws-card-desc"><?php esc_html_e( 'Ce plugin nécessite WordPress avec le module AI Client activé et un compte Anthropic configuré (Claude).', 'ws-optimizer-ai' ); ?></p>
          </div>
        </div>
        <a href="https://wordpress-freelance.com/plugins/ws-optimizer-ai/" target="_blank" rel="noopener" class="ws-link-btn">
          <?php esc_html_e( 'Documentation', 'ws-optimizer-ai' ); ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
        </a>
      </section>

      <button type="submit" class="ws-btn-save">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        <?php esc_html_e( 'Sauvegarder les réglages', 'ws-optimizer-ai' ); ?>
      </button>
    </form>
  </main>
</div>
