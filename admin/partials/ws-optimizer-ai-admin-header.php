<?php defined( 'ABSPATH' ) || exit;
$wsoa_page = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
$wsoa_base = admin_url( 'options-general.php?page=ws-optimizer-ai' );
?>
<div class="ws-adminbar">
  <div class="ws-adminbar-logo">
    <svg class="ws-logo-mark" viewBox="0 0 46 30" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="wsoaLogoC" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="#A899D4"/><stop offset="1" stop-color="#7C5CBF"/>
        </linearGradient>
        <linearGradient id="wsoaLogoS" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="#6E5FC0"/><stop offset="1" stop-color="#463A78"/>
        </linearGradient>
      </defs>
      <polygon points="2,26 10,8 10,26"   fill="#463A78"/>
      <polygon points="10,8 10,26 18,26"  fill="#5B4D9C"/>
      <polygon points="13,26 23,4 23,26"  fill="url(#wsoaLogoS)"/>
      <polygon points="23,4 23,26 33,26"  fill="url(#wsoaLogoC)"/>
      <polygon points="28,26 36,8 36,26"  fill="#5B4D9C"/>
      <polygon points="36,8 36,26 44,26"  fill="#463A78"/>
    </svg>
    <span>WebStrategy</span>
  </div>
  <nav class="ws-adminbar-links">
    <a class="ws-alink <?php echo $wsoa_page === 'settings' ? 'active' : ''; ?>"
       href="<?php echo esc_url( $wsoa_base ); ?>"><?php esc_html_e( 'Réglages', 'ws-optimizer-ai' ); ?></a>
    <a class="ws-alink <?php echo $wsoa_page === 'logs' ? 'active' : ''; ?>"
       href="<?php echo esc_url( $wsoa_base . '&tab=logs' ); ?>"><?php esc_html_e( 'AI Logs', 'ws-optimizer-ai' ); ?></a>
  </nav>
  <span class="ws-adminbar-sep"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
</div>
