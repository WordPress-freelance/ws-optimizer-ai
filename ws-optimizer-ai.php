<?php
/**
 * Plugin Name:       WS SEO Title AI
 * Plugin URI:        https://wordpress-freelance.com/plugins/ws-optimizer-ai/
 * Description:       Analyze your SEO titles with Claude AI directly in the post editor. Requires WordPress 7.0+ with AI Client enabled.
 * Version:           2.2.1
 * Author:            WebStrategy
 * Author URI:        https://wordpress-freelance.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ws-optimizer-ai
 * Domain Path:       /languages
 * Requires at least: 6.7
 * Requires PHP:      7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'WS_OPTIMIZER_AI_VERSION', '2.2.1' );
define( 'WS_OPTIMIZER_AI_SLUG',    'ws-optimizer-ai' );
define( 'WS_OPTIMIZER_AI_FILE',    __FILE__ );
define( 'WS_OPTIMIZER_AI_PATH',    plugin_dir_path( __FILE__ ) );
define( 'WS_OPTIMIZER_AI_URL',     plugin_dir_url( __FILE__ ) );

require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-activator.php';
require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai-deactivator.php';

register_activation_hook( __FILE__,   [ 'WS_Optimizer_AI_Activator',   'activate' ] );
register_deactivation_hook( __FILE__, [ 'WS_Optimizer_AI_Deactivator', 'deactivate' ] );

function ws_optimizer_ai_run() {
    require_once WS_OPTIMIZER_AI_PATH . 'includes/class-ws-optimizer-ai.php';
    $plugin = new WS_Optimizer_AI();
    $plugin->run();
}
add_action( 'plugins_loaded', 'ws_optimizer_ai_run' );
