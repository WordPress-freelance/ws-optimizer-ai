<?php defined( 'ABSPATH' ) || exit;
$wsoa_page = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
$wsoa_base = admin_url( 'options-general.php?page=ws-optimizer-ai' );
?>
<div class="ws-adminbar">
  <div class="ws-adminbar-logo">
    <div class="ws-mark">W</div>
    WebStrategy
  </div>
  <div class="ws-adminbar-links">
    <a class="ws-alink <?php echo $wsoa_page === 'settings' ? 'active' : ''; ?>"
       href="<?php echo esc_url( $wsoa_base ); ?>"><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></a>
    <a class="ws-alink <?php echo $wsoa_page === 'logs' ? 'active' : ''; ?>"
       href="<?php echo esc_url( $wsoa_base . '&tab=logs' ); ?>"><?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?></a>
  </div>
  <span class="ws-adminbar-sep"><?php echo esc_html( home_url() ); ?></span>
</div>
