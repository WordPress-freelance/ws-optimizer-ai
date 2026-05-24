<?php
// Security check — must be called by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Remove plugin settings
delete_option( 'wsoa_settings' );

// Remove all post meta stored by the plugin
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wsoa_%'" );
