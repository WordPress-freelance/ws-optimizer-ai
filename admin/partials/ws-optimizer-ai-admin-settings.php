<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Accès refusé.', 'ws-optimizer-ai' ) );
}

$log  = get_option( 'wsoa_debug_log', [] );
$log  = array_reverse( $log ); // newest first
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

    <div class="wsoa-debug-section">
        <div class="wsoa-debug-header">
            <h2><?php esc_html_e( 'Debug log', 'ws-optimizer-ai' ); ?></h2>
            <?php if ( ! empty( $log ) ) : ?>
            <button type="button" id="wsoa-clear-log" class="wsoa-btn wsoa-btn--small">
                <?php esc_html_e( 'Vider les logs', 'ws-optimizer-ai' ); ?>
            </button>
            <?php endif; ?>
        </div>

        <?php if ( empty( $log ) ) : ?>
        <p class="wsoa-debug-empty">
            <?php esc_html_e( 'Aucune entrée. Cliquez sur "Analyser" dans un article pour générer des logs.', 'ws-optimizer-ai' ); ?>
        </p>
        <?php else : ?>
        <div class="wsoa-debug-log">
            <?php foreach ( $log as $entry ) :
                $is_error = in_array( $entry['type'], [ 'exception', 'error' ], true );
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
            .then(function(res) {
                if (res.success) { window.location.reload(); }
                else { btn.disabled = false; }
            })
            .catch(function() { btn.disabled = false; });
    });
}());
</script>
