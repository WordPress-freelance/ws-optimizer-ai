<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}

$log          = get_option( 'wsoa_debug_log', [] );
$log          = array_reverse( $log );
$capture_logs = get_option( 'wsoa_capture_logs', false );
?>
<div class="ws-admin-wrap">
    <?php require __DIR__ . '/ws-optimizer-ai-admin-header.php'; ?>
    <div class="ws-shell">
        <div class="ws-main">
            <h1 class="ws-page-title">
                <img src="<?php echo esc_url( WS_OPTIMIZER_AI_URL . 'assets/icon-128x128.png' ); ?>" width="32" height="32" class="ws-title-logo" alt="">
                <?php esc_html_e( 'WS SEO Title AI', 'ws-optimizer-ai' ); ?>
                <span><?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?></span>
            </h1>

            <div class="ws-card">
                <form method="post" action="options.php" style="display:flex;align-items:center;gap:14px;">
                    <?php settings_fields( 'wsoa_logs_settings_group' ); ?>
                    <label class="wsoa-toggle">
                        <input type="checkbox" name="wsoa_capture_logs" value="1" <?php checked( $capture_logs, true ); ?>>
                        <span class="wsoa-toggle__label"><?php esc_html_e( 'Capturer les logs IA', 'ws-optimizer-ai' ); ?></span>
                    </label>
                    <button type="submit" class="ws-btn-save" style="margin-top:0;padding:7px 16px;"><?php esc_html_e( 'Enregistrer', 'ws-optimizer-ai' ); ?></button>
                </form>

                <?php if ( ! $capture_logs ) : ?>
                <p style="margin:12px 0 0;font-size:12px;color:var(--ws-t4);">
                    ⚠️ <?php esc_html_e( 'Capture désactivée — activez pour enregistrer les échanges.', 'ws-optimizer-ai' ); ?>
                </p>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $log ) ) : ?>
            <div class="ws-card" style="padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:11px;color:var(--ws-t4);"><?php printf( esc_html__( '%d entrée(s)', 'ws-optimizer-ai' ), count( $log ) ); ?></span>
                    <button type="button" id="wsoa-clear-log" class="ws-action-btn">
                        <?php esc_html_e( 'Vider les logs', 'ws-optimizer-ai' ); ?>
                    </button>
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
        </div>
    </div>
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
