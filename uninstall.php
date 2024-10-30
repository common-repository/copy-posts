<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'ipgs_jobs' );
delete_option( 'ipgs_options' );
delete_option( 'ipgs_report' );

global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->postmeta
	    WHERE `meta_key` LIKE ('%_IPGS%')" );
