<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'wsoa_settings' );

// Supprimer les post meta stockées
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_wsoa_last_analysis','_wsoa_last_analyzed_title','_wsoa_last_analysis_date')" );
