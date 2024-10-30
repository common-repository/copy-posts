<div class="wrap ipg-admin-wrap">
	<h1><?php _e( 'iPressGo Copy Post', 'ipressgo-copy-post' ); ?></h1>
	<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die( '-1' );
	}
	// this controls which view is included based on the selected tab
	$tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'jobs';
?>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo IPGS_ADMIN_URL; ?>" class="nav-tab <?php if ( $tab == 'jobs' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Copy Posts', 'ipressgo-copy-post' ); ?></a>
		<a href="<?php echo IPGS_ADMIN_URL_SETTINGS; ?>&tab=settings" class="nav-tab <?php if ( $tab == 'settings' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Settings', 'ipressgo-copy-post' ); ?></a>
		<a href="<?php echo IPGS_ADMIN_URL; ?>&tab=support" class="nav-tab <?php if( $tab == 'support' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Support', 'ipressgo-copy-post' ); ?></a>
	</h2>
	<?php
	if ( $tab === 'settings' ) {
		require_once IPGS_PLUGIN_DIR.'templates/admin-settings.php';
	} elseif ( $tab === 'support' ) {
		require_once IPGS_PLUGIN_DIR.'templates/admin-support.php';
	} else {
		require_once IPGS_PLUGIN_DIR.'templates/admin-jobs.php';
	}
	?>

</div>