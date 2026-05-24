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
    <h1 class="ws-page-title">
      <svg class="ws-title-logo" width="36" height="36" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="32" height="32" rx="7" fill="#221D32"/><rect x="7" y="9" width="18" height="3" rx="1.5" fill="#7C5CBF"/><rect x="7" y="15" width="12" height="2" rx="1" fill="#9B8EC4"/><rect x="7" y="20" width="8" height="2" rx="1" fill="#4A4260"/><circle cx="24" cy="22" r="4" fill="#221D32" stroke="#7C5CBF" stroke-width="1.5"/><path d="M23 22l1 1 2-2" stroke="#9B8EC4" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      WS SEO Title AI <span><?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?></span>
    </h1>

    <div class="ws-card">
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <?php wp_nonce_field( 'wsoa_save_logs_settings' ); ?>
        <input type="hidden" name="action" value="wsoa_save_logs_settings">
        <label class="ws-day-check" style="cursor:pointer;">
          <input type="checkbox" name="wsoa_capture_logs" value="1" <?php checked( $capture_logs ); ?>>
          <span><?php esc_html_e( 'Capturer les logs IA', 'ws-optimizer-ai' ); ?></span>
        </label>
        <button type="submit" class="ws-btn-save" style="margin-top:0;padding:7px 16px;font-size:11px;">
          <?php esc_html_e( 'Enregistrer', 'ws-optimizer-ai' ); ?>
        </button>
      </form>
      <?php if ( ! $capture_logs ) : ?>
      <p class="ws-field-hint" style="margin-top:10px;">
        ⚠️ <?php esc_html_e( 'Capture désactivée — activez pour enregistrer les échanges avec l\'AI Client.', 'ws-optimizer-ai' ); ?>
      </p>
      <?php endif; ?>
    </div>

    <?php if ( ! empty( $log ) ) : ?>
    <div class="ws-card">
      <div class="ws-card-title" style="justify-content:space-between;">
        <span><?php printf( esc_html__( '%d entrée(s)', 'ws-optimizer-ai' ), count( $log ) ); ?></span>
        <button type="button" id="wsoa-clear-log" class="ws-action-btn"><?php esc_html_e( 'Vider les logs', 'ws-optimizer-ai' ); ?></button>
      </div>
      <div class="wsoa-debug-log">
        <?php foreach ( $log as $entry ) :
          $is_error = in_array( $entry['type'], [ 'exception', 'error', 'wp_error' ], true );
        ?>
        <div class="wsoa-debug-entry <?php echo $is_error ? 'wsoa-debug-entry--error' : ''; ?>">
          <div class="wsoa-debug-entry__meta">
            <span class="wsoa-debug-entry__time"><?php echo esc_html( $entry['time'] ); ?></span>
            <span class="wsoa-debug-entry__type"><?php echo esc_html( strtoupper( $entry['type'] ) ); ?></span>
          </div>
          <pre class="wsoa-debug-entry__data"><?php echo esc_html( is_array( $entry['data'] ) ? wp_json_encode( $entry['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : $entry['data'] ); ?></pre>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else : ?>
    <div class="ws-card">
      <p class="ws-empty"><?php esc_html_e( 'Aucune entrée. Activez la capture puis cliquez sur "Analyser" dans un article.', 'ws-optimizer-ai' ); ?></p>
    </div>
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
