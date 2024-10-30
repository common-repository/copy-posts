<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class IPGS_Job
 *
 *
 *
 * @since 1.0
 */

class IPGS_Job {

	private $posts_api_connect;

	private $next_post;

	private $job_id;

	private $api_settings;

	private $schedule_settings;

	private $category_data_array;

	private $job_progress;

	private $posts_remaining;

	/**
	 * IPGS_Job constructor.
	 *
	 * Create the basic admin pages
	 */
	public function __construct( $ipgs_jobs, $job_id )
	{
		$api_settings = array();
		$schedule_settings = array();
		$max_per_page = apply_filters( 'ipgs_max_per_page', 30 );
		
		$api_settings['blog_url'] = isset( $ipgs_jobs[ $job_id ]['blog_url'] ) ? $ipgs_jobs[ $job_id ]['blog_url'] : '';
		$schedule_settings['max_posts'] = isset( $ipgs_jobs[ $job_id ]['max_posts'] ) ? $ipgs_jobs[ $job_id ]['max_posts'] : 5;
		$schedule_settings['limit_posts'] = isset( $ipgs_jobs[ $job_id ]['limit_posts'] ) ? $ipgs_jobs[ $job_id ]['limit_posts'] : false;

		$api_settings['params']['categories'][0] = isset( $ipgs_jobs[ $job_id ]['params']['categories'][0] ) ? $ipgs_jobs[ $job_id ]['params']['categories'][0] : 'any';

		if ( $api_settings['params']['categories'][0] == 'any' ) {
			unset( $api_settings['params']['categories'] );
		}

		if ( $schedule_settings['limit_posts'] ) {
			$api_settings['params']['per_page'] = min( $schedule_settings['max_posts'], $max_per_page );
		} else {
			$api_settings['params']['per_page'] = $max_per_page;
		}

		$schedule_settings['start_date'] = isset( $ipgs_jobs[ $job_id ]['start_date'] ) ? strtotime( $ipgs_jobs[ $job_id ]['start_date'] ) : time();
		$schedule_settings['interval_val'] = isset( $ipgs_jobs[ $job_id ]['interval_val'] ) ? (int)$ipgs_jobs[ $job_id ]['interval_val'] : 7;
		$schedule_settings['interval_unit'] = isset( $ipgs_jobs[ $job_id ]['interval_unit'] ) ? (int)$ipgs_jobs[ $job_id ]['interval_unit'] : 86400;
		$schedule_settings['post_status'] = isset( $ipgs_jobs[ $job_id ]['post_status'] ) ? $ipgs_jobs[ $job_id ]['post_status'] : 'publish';
		$schedule_settings['last_save_new_post_id'] = isset( $ipgs_jobs[ $job_id ]['last_save_new_post_id'] ) ? $ipgs_jobs[ $job_id ]['last_save_new_post_id'] : 0;

		$this->job_id = $job_id;
		$this->api_settings = $api_settings;
		$this->schedule_settings = $schedule_settings;

		$num_copied = $this->get_num_copied_posts();
		$this->posts_remaining = $schedule_settings['limit_posts'] ? (int)$schedule_settings['max_posts'] - $num_copied : 'no_limit';

		if ( (int)$num_copied === 0 ) {
			$this->job_progress = 'no_progress';
		} elseif ( $this->posts_remaining === 'no_limit' || $this->posts_remaining > 0 ) {
			$this->job_progress = 'current';
		} else {
			$this->job_progress = 'complete';
		}

	}

	public function get_available_category_data()
	{
		if ( ! isset( $this->category_data_array ) ) {
			$this->set_categories();
		}

		return $this->category_data_array;
	}

	public function get_next_post()
	{
		if ( ! isset( $this->next_post ) ) {
			$this->set_next_post();
		}

		return $this->next_post;
	}

	public function get_num_copied_posts()
	{
		$ipgs_db = new IPGS_Db();

		return $ipgs_db->get_num_copied_posts_for_job( $this->job_id );
	}

	public function get_api_settings()
	{
		return $this->api_settings;
	}

	public function get_schedule_settings()
	{
		return $this->schedule_settings;
	}

	public function get_current_connection_url()
	{
		if ( isset( $this->posts_api_connect ) ) {
			return $this->posts_api_connect->get_url();
		}

		return false;
	}

	public function get_job_progress()
	{
		if ( $this->job_progress === 'current' && $this->get_next_post() === false ) {
			return 'on_hold';
		} else {
			return $this->job_progress;
		}
	}

	public function get_posts_remaining()
	{
		return $this->posts_remaining;
	}

	public function get_next_job_execution_time()
	{
		$last_post_created_on_date = ! empty( $this->schedule_settings['last_save_new_post_id'] ) ? get_post_meta( $this->schedule_settings['last_save_new_post_id'], '_IPGScreatedOnDateUTC' ) : false;
		$last_post_timestamp = $last_post_created_on_date ? strtotime( $last_post_created_on_date[0] ) : false;

		if ( $last_post_timestamp ) {
			$will_post_on = ((int)$this->schedule_settings['interval_val'] * (int)$this->schedule_settings['interval_unit']) + $last_post_timestamp;
		} else {
			$will_post_on = $this->schedule_settings['start_date'];
		}

		return $will_post_on;
	}

	public function start_time_has_passed()
	{
		return $this->schedule_settings['start_date'] < time();
	}

	private function set_next_post()
	{
		$next_post = false;

		if ( $this->job_progress !== 'complete' ) {
			$params = array();

			if ( isset( $this->api_settings['params'] ) ) {

				foreach ( $this->api_settings['params'] as $param => $value ) {
					$params[ $param ] = $value;
				}

			}

			$this->posts_api_connect = new IPGS_Rest_Connect( $this->api_settings['blog_url'], 'posts', $params );
			$this->posts_api_connect->connect();
			$response_body = $this->posts_api_connect->get_response_body();

			$ipgs_db = new IPGS_Db();
			$post_ids_in_job = $ipgs_db->get_post_ids_for_job( $this->job_id );

			$i = 0;

			while ( $next_post === false && isset( $response_body[ $i ] ) ) {

				if ( ! in_array( strval( $response_body[ $i ]->id ), $post_ids_in_job, true ) ) {
					$next_post = $response_body[ $i ];
				}

				$i++;
			}
		}

		$this->next_post = $next_post;
	}

	private function set_categories()
	{
		$ipgs_category_connect = new IPGS_Rest_Connect( $this->api_settings['blog_url'], 'categories' );
		$ipgs_category_connect->connect();
		$response_body = $ipgs_category_connect->get_response_body();

		$cat_data = array();

		foreach ( $response_body as $category ) {
			$cat_data[] = array(
				'id' => $category->id,
				'name' => $category->name
			);
		}

		$this->category_data_array = $cat_data;
	}

}