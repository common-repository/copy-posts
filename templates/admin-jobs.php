<?php
settings_errors();
$ipgs = IPGS();

if ( isset( $_POST['ipgs_jobs_page'] ) && isset( $_POST['ipgs_jobs'] ) && isset( $_POST['ipgs_nonce'] ) && wp_verify_nonce( $_POST['ipgs_nonce'], 'ipgs_nonce' ) ) {
    //var_dump( $_POST );
    global $ipgs_jobs;
    $sanitized_jobs = $ipgs->admin->schedule_jobs( $_POST['ipgs_jobs'] );
    update_option( 'ipgs_jobs', $sanitized_jobs );
	$ipgs_jobs = $sanitized_jobs;
}
global $ipgs_jobs;

?>
<h1><?php _e( 'Copy Posts and Set Up Copy Jobs', 'ipressgo-copy-post' ); ?></h1>
<div class="ipgs-admin">
    <form method="post" action="">
        <input type="hidden" name="ipgs_jobs_page" value="Y">
	    <?php wp_nonce_field( 'ipgs_nonce', 'ipgs_nonce' ); ?>
        <input name="action" type="hidden" value="ipgs_ajax"/>
        <?php $ipgs->admin->job_creator( array() ); ?>
    </form>
</div>