<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( isset( $_POST['ipgs_options_page'] ) && $_POST['ipgs_options_page'] === 'Y' && isset( $_POST['ipgs_nonce'] ) && wp_verify_nonce( $_POST['ipgs_nonce'], 'ipgs_nonce' ) ) {
	$ipgs = IPGS();
	$the_settings = get_option( 'ipgs_options' );
	$ipgs_options = isset( $_POST['ipgs_options'] ) ? $_POST['ipgs_options'] : array();
	$sanitized_settings = $ipgs->admin->validate_options( $ipgs_options );
	update_option( 'ipgs_options', $sanitized_settings );
	$the_settings = $sanitized_settings;
?>
<div class="updated"><p><strong><?php _e( 'Settings saved.', 'ipressgo-copy-post' ); ?></strong></p></div>
<?php
} else {
	$the_settings = get_option( 'ipgs_options' );
}
settings_errors(); ?>
<h1><?php _e( 'Settings', 'ipressgo-copy-post' ); ?></h1>
<form method="post" action="">
    <input type="hidden" name="ipgs_options_page" value="Y">
	<?php wp_nonce_field( 'ipgs_nonce', 'ipgs_nonce' ); ?>
    <table class="form-table">
        <tbody>
        <tr class="large-text">
            <th scope="row"><label for="ipgs_localize_images"><?php _e( 'Localize Images', 'ipressgo-copy-post' ); ?></label></th>
            <td><input name="ipgs_options[localize_images]" id="ipgs_localize_images" type="checkbox"<?php if( $the_settings['localize_images'] ) echo ' checked'; ?>>
                <br>
                <span class="description"><?php _e( 'Before copying a new post, all images will be copied to your local server and the source of images in the post content will be changed to the local image source.', 'ipressgo-copy-post' ) ?></span>
            </td>
        </tr>
        </tbody>
    </table>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
	<hr>
</form>