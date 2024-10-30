<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class IPGS_Db
 *
 *
 *
 * @since 1.0
 */

class IPGS_Db {

	public function save_new_post( $post_data, $save_settings, $update = true )
	{
		$post_status = isset( $save_settings['post_status'] ) ? $save_settings['post_status'] : 'publish';
		$post_title = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $post_data->title->rendered );
		$post_name = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $post_data->slug );

		$post_content = apply_filters( 'ipgs_before_save_post_content', $post_data->content->rendered );
		$post_content = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $post_content );

		$args = array(
			'comment_status'	=>	'closed',
			'ping_status'		=>	'closed',
			'post_name'		    =>	$post_name,
			'post_title'		=>	$post_title,
			'post_status'		=>	$post_status,
			'post_content'      =>  $post_content
		);
		$new_post_id = wp_insert_post( $args, true );

		if ( $new_post_id instanceof WP_Error ) {
			return false;
		} else {
			$this->save_new_post_meta( $new_post_id, $save_settings, $post_data );

			if ( $update ) {
				$this->update_job_meta( $new_post_id, $save_settings['job_id'] );
			}

			return $new_post_id;
		}
	}

	public function get_num_copied_posts_for_job( $job_id )
	{
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='_IPGSjobID' AND meta_value=%s", $job_id ) );
	}

	public function get_post_ids_for_job( $job_id )
	{
		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_IPGSoriginalID' AND post_id in (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_IPGSjobID' AND meta_value=%s);", $job_id ) );
	}

	public static function localize_images_in_html( $post_content )
	{
		if ( ! class_exists('DOMDocument' ) ) {
			return $post_content;
		}

		$html = $post_content;
		$uploaddir = wp_upload_dir();
		$doc = new DOMDocument();
		$doc->loadHTML($html);

		$images = $doc->getElementsByTagName( 'img' );

		foreach ( $images as $image ) {
			$source_file = $image->getAttribute( 'src' );
			$filename = basename( $source_file );
			$uploadfile = $uploaddir['path'] . '/' . $filename; // C:\xampp\htdocs\development/wp-content/uploads/2018/05/example.jpg
			$attachment_save_id = IPGS_Db::insert_new_attachment( $source_file, $uploadfile, $filename );

			if ( isset( $attachment_save_id ) && $attachment_save_id > 0 ) {
				$image_url = wp_get_attachment_image_src( $attachment_save_id, 'full' );
				$html = str_replace( $source_file, $image_url[0] , $html );
			}

		}

		return $html;
	}

	private function save_new_post_meta( $new_post_id, $save_settings, $post_data )
	{
		update_post_meta( $new_post_id, '_IPGScreatedOnDateUTC', date( 'Y-m-d H:i:s' ) );
		update_post_meta( $new_post_id, '_IPGSjobID', $save_settings['job_id'] );
		update_post_meta( $new_post_id, '_IPGSoriginalID', $post_data->id );
	}

	private function update_job_meta( $new_post_id, $job_id )
	{
		global $ipgs_jobs;

		$ipgs_jobs[ $job_id ]['last_save_new_post_id'] = $new_post_id;

		update_option( 'ipgs_jobs', $ipgs_jobs );
	}

	private static function insert_new_attachment( $source_file, $uploadfile, $filename )
	{
		$contents = file_get_contents( $source_file );
		$savefile = fopen( $uploadfile, 'w' );
		fwrite( $savefile, $contents );
		fclose( $savefile );

		$wp_filetype = wp_check_filetype( basename( $source_file ), null );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $filename,
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $uploadfile ); // 0 for failure

		$imagenew = get_post( $attach_id );
		$fullsizepath = get_attached_file( $imagenew->ID );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

}