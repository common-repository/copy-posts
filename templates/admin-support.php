<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?><h1><?php _e( 'Need Help?', 'ipressgo-copy-post' ); ?></h1>

<p>
	<span class="ipgs-support-title"><span class="dashicons dashicons-sos"></span>&nbsp; <a href="https://support.ipressgo.com/portal/kb/ipressgo-copy-post-plug-in/install" target="_blank"><?php _e( 'Setup Directions', 'ipressgo-copy-post' ); ?></a></span>
	<br /><?php _e( 'A step-by-step guide on how to setup and use the plugin.', 'ipressgo-copy-post' ); ?>
</p>
<p>
	<span class="ipgs-support-title"><span class="dashicons dashicons-editor-help"></span>&nbsp; <a href="https://support.ipressgo.com/portal/kb/ipressgo-copy-post-plug-in/faq" target="_blank"><?php _e( 'FAQs and Documentation', 'ipressgo-copy-post' ); ?></a></span>
	<br /><?php _e( 'You might find some help with our FAQs and troubleshooting guides.', 'ipressgo-copy-post' ); ?>
</p>
<p>
	<span class="ipgs-support-title"><span class="dashicons dashicons-email"></span>&nbsp; <a href="https://support.ipressgo.com/portal/newticket" target="_blank"><?php _e( 'Request Support', 'ipressgo-copy-post' ); ?></a></span>
	<br /><?php _e( 'Have a problem? Submit a support ticket on our website. Please include your <strong>System Info</strong> below with support requests.', 'ipressgo-copy-post' ); ?>
</p>

<br />

<h2><?php _e( 'System Info', 'ipressgo-copy-post' ); ?></h2>
<p><?php _e( 'Click the text below to select all', 'ipressgo-copy-post' ); ?></p>

<textarea readonly="readonly" onclick="this.focus();this.select()" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)." style="width: 70%; height: 500px; white-space: pre; font-family: Menlo,Monaco,monospace;">
## SITE/SERVER INFO: ##
Plugin Version:           <?php echo IPGS_TITLE . ' v' . IPGS_VERSION. "\n"; ?>
Site URL:                 <?php echo site_url() . "\n"; ?>
Home URL:                 <?php echo home_url() . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
JSON:                     <?php echo function_exists( "json_decode" ) ? "Yes" . "\n" : "No" . "\n" ?>

## ACTIVE PLUGINS: ##
<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
// If the plugin isn't active, don't show it.
if ( in_array( $plugin_path, $active_plugins ) ) {
echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}
}
?>

## JOBS: ##
<?php
$timestamp = wp_next_scheduled( 'ipgs_twicehourly' );
echo 'Next cron event: ' . date( 'Y-m-d H:i:s', $timestamp ) . "\n\n";
echo "Scheduled job details:\n";
global $ipgs_jobs;

foreach ( $ipgs_jobs as $key => $val ) {
$ipgs_job = new IPGS_Job( $ipgs_jobs, $val['id'] );
$ipgs_job->get_next_post();
echo 'Job ID: '. esc_html( $key ) . "\n";
echo 'Current connection URL: '. $ipgs_job->get_current_connection_url() . "\n";
echo 'Details: ';
var_dump( $val );
echo "\n\n";
}
?>
# REPORT: #
<?php
$ipgs_report = get_option( 'ipgs_report', array() );
if ( ! empty( $ipgs_report ) ) {
var_dump( $ipgs_report );
} else {
	echo 'no report';
	echo "\n";
}
?>
</textarea>