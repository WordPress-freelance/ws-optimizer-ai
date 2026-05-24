<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}
$log          = array_reverse( get_option( 'wsoa_debug_log', [] ) );
$capture_logs = (bool) get_option( 'wsoa_capture_logs', false );
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
        <span class="ws-page-title__section"><?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?></span>
      </h1>
      <p class="ws-page-sub"><?php esc_html_e( 'Échanges bruts avec l\'AI Client pour le debug.', 'ws-optimizer-ai' ); ?></p>
    </header>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="ws-notice ws-notice-ok">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
      <?php esc_html_e( 'Réglage enregistré.', 'ws-optimizer-ai' ); ?>
    </div>
    <?php endif; ?>

    <section class="ws-card">
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ws-capture-form">
        <?php wp_nonce_field( 'wsoa_save_logs_settings' ); ?>
        <input type="hidden" name="action" value="wsoa_save_logs_settings">
        <label class="ws-switch">
          <input type="checkbox" name="wsoa_capture_logs" value="1" <?php checked( $capture_logs ); ?>>
          <span class="ws-switch__track"><span class="ws-switch__thumb"></span></span>
          <span class="ws-switch__label"><?php esc_html_e( 'Capturer les logs IA', 'ws-optimizer-ai' ); ?></span>
        </label>
        <button type="submit" class="ws-btn-inline"><?php esc_html_e( 'Enregistrer', 'ws-optimizer-ai' ); ?></button>
      </form>
      <?php if ( ! $capture_logs ) : ?>
      <p class="ws-card-desc ws-card-desc--warn">
        <?php esc_html_e( 'Capture désactivée — activez-la pour enregistrer les échanges avec l\'AI Client.', 'ws-optimizer-ai' ); ?>
      </p>
      <?php endif; ?>
    </section>

    <?php if ( ! empty( $log ) ) : ?>
    <section class="ws-card">
      <div class="ws-log-head">
        <span class="ws-log-count"><?php printf( esc_html__( '%d entrée(s)', 'ws-optimizer-ai' ), count( $log ) ); ?></span>
        <button type="button" id="wsoa-clear-log" class="ws-btn-ghost"><?php esc_html_e( 'Vider les logs', 'ws-optimizer-ai' ); ?></button>
      </div>
      <div class="wsoa-debug-log">
        <?php foreach ( $log as $entry ) :
          $is_error = in_array( $entry['type'], [ 'exception', 'error', 'wp_error' ], true );
        ?>
        <div class="wsoa-debug-entry <?php echo $is_error ? 'wsoa-debug-entry--error' : ''; ?>">
          <div class="wsoa-debug-entry__meta">
            <span class="wsoa-debug-entry__type"><?php echo esc_html( strtoupper( $entry['type'] ) ); ?></span>
            <span class="wsoa-debug-entry__time"><?php echo esc_html( $entry['time'] ); ?></span>
          </div>
          <pre class="wsoa-debug-entry__data"><?php echo esc_html( is_array( $entry['data'] ) ? wp_json_encode( $entry['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : $entry['data'] ); ?></pre>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php else : ?>
    <section class="ws-card">
      <div class="ws-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
        <p><?php esc_html_e( 'Aucune entrée. Activez la capture puis lancez une analyse depuis un article.', 'ws-optimizer-ai' ); ?></p>
      </div>
    </section>
    <?php endif; ?>
  </main>
</div>

<script>
(function() {
  var btn = document.getElementById('wsoa-clear-log');
  if (!btn) return;
  btn.addEventListener('click', function() {
    btn.disabled = true;
    var form = new FormData();
    form.append('action', 'wsoa_clear_log');
    form.append('nonce', <?php echo wp_json_encode( wp_create_nonce( 'wsoa_clear_log' ) ); ?>);
    fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', body: form })
      .then(function(r) { return r.json(); })
      .then(function(res) { if (res.success) window.location.reload(); else btn.disabled = false; })
      .catch(function() { btn.disabled = false; });
  });
}());
</script>
