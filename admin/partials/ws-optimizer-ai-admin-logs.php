<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}

$log          = get_option( 'wsoa_debug_log', [] );
$log          = array_reverse( $log );
$capture_logs = get_option( 'wsoa_capture_logs', false );
$base_url     = admin_url( 'options-general.php?page=ws-optimizer-ai' );
?>
<div class="wrap wsoa-wrap">
    <h1><?php esc_html_e( 'WS SEO Title AI — AI Logs', 'ws-optimizer-ai' ); ?></h1>
    <nav class="wsoa-tabs">
        <a href="<?php echo esc_url( $base_url ); ?>" class="wsoa-tab">
            <?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?>
        </a>
        <a href="<?php echo esc_url( $base_url . '&tab=logs' ); ?>" class="wsoa-tab wsoa-tab--active">
            <?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?>
        </a>
    </nav>

    <p class="wsoa-subtitle"><?php esc_html_e( 'Capture des échanges avec WordPress AI Client pour diagnostic.', 'ws-optimizer-ai' ); ?></p>

    <div class="wsoa-logs-toolbar">
        <form method="post" action="options.php" class="wsoa-logs-capture-form">
            <?php settings_fields( 'wsoa_logs_settings_group' ); ?>
            <label class="wsoa-toggle">
                <input type="checkbox" name="wsoa_capture_logs" value="1" <?php checked( $capture_logs, true ); ?>>
                <span class="wsoa-toggle__label"><?php esc_html_e( 'Capturer les logs IA', 'ws-optimizer-ai' ); ?></span>
            </label>
            <?php submit_button( __( 'Enregistrer', 'ws-optimizer-ai' ), 'secondary wsoa-btn-save-capture', 'submit', false ); ?>
        </form>

        <?php if ( ! empty( $log ) ) : ?>
        <button type="button" id="wsoa-clear-log" class="wsoa-btn wsoa-btn--small">
            <?php esc_html_e( 'Vider les logs', 'ws-optimizer-ai' ); ?>
        </button>
        <?php endif; ?>
    </div>

    <?php if ( ! $capture_logs ) : ?>
    <div class="wsoa-logs-notice">
        <span>⚠️</span> <?php esc_html_e( 'La capture est désactivée. Activez "Capturer les logs IA" pour enregistrer les échanges.', 'ws-optimizer-ai' ); ?>
    </div>
    <?php endif; ?>

    <?php if ( empty( $log ) ) : ?>
    <p class="wsoa-debug-empty">
        <?php esc_html_e( 'Aucune entrée. Activez la capture puis cliquez sur "Analyser" dans un article.', 'ws-optimizer-ai' ); ?>
    </p>
    <?php else : ?>
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
    <?php endif; ?>
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
