<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class IPGS_Admin
 *
 * Manages settings pages and saving of options
 *
 * @since 1.0
 */

class IPGS_Admin {

	private $job_settings_progress;

	/**
	 *
	 * Create the basic admin pages
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );
		add_action( 'wp_ajax_ipgs_ajax', array( $this, 'ipgs_ajax' ) );
		add_action( 'wp_ajax_ipgs_ajax_blog_url', array( $this, 'ipgs_ajax_blog_url' ) );
		add_action( 'wp_ajax_ipgs_ajax_step_two', array( $this, 'ipgs_ajax_step_two' ) );
		add_action( 'wp_ajax_ipgs_ajax_new_job', array( $this, 'ipgs_ajax_new_job' ) );
		add_action( 'wp_ajax_ipgs_ajax_process_selected_posts', array( $this, 'ipgs_ajax_process_selected_posts' ) );
	}

	/**
	 *
	 * @since 1.0
	 */
	public function add_menu_item() {
		$menu_title = __( 'Copy Post', 'ipressgo-copy-post' );

		add_menu_page(
			$menu_title,
			$menu_title,
			'manage_options',
			'ipressgo-copy-post',
			array( $this, 'create_options_page' ),
			IPGS_PLUGIN_URL . 'assets/ipressgo-icon.png',
			99
		);

		add_submenu_page(
			'',
			esc_html__( 'Settings', 'ipressgo-copy-post' ),
			esc_html__( 'Settings', 'ipressgo-copy-post' ),
			'manage_options',
			'ipressgo-copy-post-settings',
			array( $this, 'create_options_page' )
		);
	}

	public function blank() {
		return;
	}

	public function create_options_page() {
		require_once IPGS_PLUGIN_DIR . 'templates/admin-main.php';
	}

	/**
	 * Makes creating settings easier
	 *
	 * @param array $args extra arguments to create parts of the form fields
	 */
	public function create_settings_field( $args = array() ) {
		add_settings_field(
			$args['name'],
			$args['title'],
			array( $this, $args['callback'] ),
			$args['page'],
			$args['section'],
			$args
		);
	}

	private function set_job_settings_progress( $ipgs_jobs ) {
		$job_settings_progress = false;

		if ( ! empty( $ipgs_jobs ) ) {

			foreach ( $ipgs_jobs as $job => $settings ) {

				if ( isset( $settings['blog_url'] ) ) {
					$job_settings_progress[ $job ]['has_url'] = true;
				} else {
					$job_settings_progress[ $job ]['has_url'] = false;
				}

				if ( isset( $settings['start_date'] ) ) {
					$job_settings_progress[ $job ]['has_schedule'] = true;
				} else {
					$job_settings_progress[ $job ]['has_schedule'] = false;
				}

				if ( isset( $settings['last_save_new_post_id'] ) ) {
					$job_settings_progress[ $job ]['has_started'] = true;
				} else {
					$job_settings_progress[ $job ]['has_started'] = false;
				}

			}

		}

		$this->job_settings_progress = $job_settings_progress;
	}

	public function inputs( $identifier, $job_id, $api_settings ) {
		?>
        <input id="ipgs-blog-url-<?php echo $identifier; ?>"
               name="<?php echo 'ipgs_jobs[' . $job_id . '][blog_url]'; ?>" type="hidden"
               value="<?php echo esc_attr( $api_settings['blog_url'] ); ?>"/>
        <input class="ipgs-job_id-input" type="hidden" name="<?php echo 'ipgs_jobs['.$job_id .'][id]'; ?>" value="<?php esc_attr_e( $job_id ); ?>" >

		<?php
	}

	public function intro( $stage ) {
		if ( $stage === 'step1' ) : ?>
            <div id="ipgs-job-intro">
                <p><?php _e( 'Step 1: Enter the URL of the website you would like to connect with.', 'ipressgo-copy-post' ); ?></p>
            </div>
		<?php elseif ( $stage === 'step2' ) : ?>
            <div id="ipgs-job-intro">
                <p class="ipgs-alert"><?php _e( 'You were successfully connected!', 'ipressgo-copy-post' ); ?></p>
                <p><?php _e( 'Step 2: Configure settings for this job.', 'ipressgo-copy-post' ); ?></p>
            </div>
		<?php elseif ( $stage === 'step3' ) : ?>
            <div id="ipgs-job-intro">
                <p class="ipgs-alert"><?php _e( 'Job was successfully created. Visit this page to view it\'s progress or make changes.', 'ipressgo-copy-post' ); ?></p>
            </div>
		<?php
		endif;
	}

	public function before_body( $api_settings ) {
	    if ( ! empty( $api_settings['blog_url'] ) ) :
		?>
        <div class="ipgs-job-setting-wrap ipgs-connected-to">
            <span><?php _e( 'Connected to', 'ipressgo-copy-post' ); ?>: </span><br/><strong><?php esc_html_e( $api_settings['blog_url'] ); ?></strong>
        </div>
            <?php
        else :
            echo '<div class="ipgs-top-border"></div>';
        endif;
        ?>
        <div class="ipgs-job-wrap">
		<?php
	}

	public function after_body() {
		?>
        <div class="spinner"></div>
        </div>
		<?php
	}

	public function step_two_tabs( $selected ) {
    ?>
        <h2 class="nav-tab-wrapper ipgs-subtabs">
            <a href="JavaScript:void(0);" class="nav-tab<?php if( $selected === 'schedule' ) echo ' nav-tab-active'; ?>" id="ipgs-nav-schedule"><?php _e( 'Schedule Job', 'ipressgo-copy-post' ); ?></a>
            <a href="JavaScript:void(0);" class="nav-tab<?php if( $selected === 'post' ) echo ' nav-tab-active'; ?>" id="ipgs-nav-post"><?php _e( 'Select Posts', 'ipressgo-copy-post' ); ?></a>
        </h2>
        <?php
    }

	public function step_two_body( $identifier, $job_id, $api_settings, $schedule_settings, $category_data_array, $selected_category, $date_format ) {
	    $tz_offset = ipgs_get_tz_offset();
		$this->step_two_tabs( 'schedule' );
		?>
        <div class="ipgs-job-setting-wrap">
            <label for="ipgs-category-<?php echo $identifier; ?>"><?php _e( 'Select Category', 'ipressgo-copy-post' ); ?></label>
            <select class="ipgs-category-input" id="ipgs-category-<?php echo $identifier; ?>"
                    name="<?php echo 'ipgs_jobs[' . $job_id . '][params][categories][]'; ?>">
                <option value="any"<?php if ( $selected_category === 'any' ) {
					echo ' selected';
				} ?>><?php _e( 'Any', 'ipressgo-copy-post' ); ?></option>
				<?php foreach ( $category_data_array as $category ) : ?>
                    <option value="<?php esc_attr_e( $category['id'] ); ?>"<?php if ( $selected_category == $category['id'] ) {
						echo ' selected';
					} ?>><?php esc_html_e( $category['name'] ); ?></option>
				<?php endforeach; ?>
            </select>
            <a class="ipgs-tooltip-link" href="JavaScript:void(0);"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <div class="ipgs-tooltip">
                <p><?php _e( 'You can select a category filter to only copy posts from a specific category. Select "Any" to show posts from all categories.', 'ipressgo-copy-post' ); ?>
            </div>
        </div>
        <div class="ipgs-job-setting-wrap">
            <input class="ipgs-limit-posts-checkbox" type="checkbox" id="ipgs-limit-posts-'<?php echo $identifier; ?>"
                   name="<?php echo 'ipgs_jobs[' . $job_id . '][limit_posts]'; ?>" <?php if ( $schedule_settings['limit_posts'] ) {
				echo 'checked';
			} ?>>
            <label class="ipgs-checkbox-label"
                   for="ipgs-limit-posts-'<?php echo $identifier; ?>"><?php _e( 'Limit Posts to Copy', 'ipressgo-copy-post' ); ?></label>
            <a class="ipgs-tooltip-link" href="JavaScript:void(0);"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <div class="ipgs-tooltip">
                <p><?php _e( 'Limit the total number of posts to include in this job. Once the limit is reached, no more posts will be copied.', 'ipressgo-copy-post' ); ?>
            </div>
            <br/>
            <div class="ipgs-num-copy-wrap ipgs-js-hide">
                <label for="ipgs-max-posts-<?php echo $identifier; ?>"><?php _e( 'Total Posts to Copy', 'ipressgo-copy-post' ); ?></label>
                <input class="ipgs-max_posts-input" type="number" min="1" id="ipgs-max-posts-'<?php echo $identifier; ?>"
                       name="<?php echo 'ipgs_jobs[' . $job_id . '][max_posts]'; ?>"
                       value="<?php echo esc_attr( $schedule_settings['max_posts'] ); ?>">
            </div>
        </div>
        <div class="ipgs-job-setting-wrap">
            <label for="ipgs-int-val-<?php echo $identifier; ?>"><?php _e( 'Copy Frequency', 'ipressgo-copy-post' ); ?></label>
            <span><?php _e( 'Copy new post every', 'ipressgo-copy-post' ); ?></span>
            <input type="number" min="1" class="ipgs-num-input" id="ipgs-int-val-<?php echo $identifier; ?>"
                   name="<?php echo 'ipgs_jobs[' . $job_id . '][interval_val]'; ?>"
                   value="<?php echo esc_attr( $schedule_settings['interval_val'] ); ?>">
            <select class="ipgs-interval_unit-input" id="ipgs-int-unit-<?php echo $identifier; ?>"
                    name="<?php echo 'ipgs_jobs[' . $job_id . '][interval_unit]'; ?>">
                <option value="3600" <?php if ( $schedule_settings['interval_unit'] == 3600 )
					echo 'selected' ?>><?php _e( 'Hours', 'ipressgo-copy-post' ); ?></option>
                <option value="86400" <?php if ( $schedule_settings['interval_unit'] == 86400 )
					echo 'selected' ?>><?php _e( 'Days', 'ipressgo-copy-post' ); ?></option>
            </select>
            <a class="ipgs-tooltip-link" href="JavaScript:void(0);"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <div class="ipgs-tooltip">
                <p><?php _e( 'Use this setting to change how often a single post is copied.', 'ipressgo-copy-post' ); ?>
            </div>
        </div>
        <div class="ipgs-job-setting-wrap">
            <label for="ipgs-start-date-<?php echo $identifier; ?>"><?php _e( 'Start Date', 'ipressgo-copy-post' ); ?></label>
            <input type="text" id="ipgs-start-date-<?php echo $identifier; ?>"
                   name="<?php echo 'ipgs_jobs[' . $job_id . '][start_date]'; ?>"
                   value="<?php echo esc_attr( date( $date_format, $schedule_settings['start_date'] + $tz_offset ) ); ?>"
                   class="ipgs-date-picker">
            <input autocomplete="off" tabindex="2001" type="text" class="ipgs-time-picker tribe-timepicker tribe-field-end_time ui-timepicker-input" name="<?php echo 'ipgs_jobs[' . $job_id . '][start_time]'; ?>" id="ipgs-time-picker" data-step="60" data-round="" value="<?php echo date( "H:i:s", $schedule_settings['start_date'] + $tz_offset ); ?>" style="width: 120px;">
            <a class="ipgs-tooltip-link" href="JavaScript:void(0);"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <div class="ipgs-tooltip">
                <p><?php _e( 'Select the date you would like this job will begin. To copy the first post immediately, select today\'s date and a time earlier than right now. Actual first copy occurs within a half hour your selected start time.', 'ipressgo-copy-post' ); ?>
            </div>
        </div>
        <div class="ipgs-job-setting-wrap">
            <label for="ipgs-post-status-<?php echo $identifier; ?>"><?php _e( 'Post Status After Copy', 'ipressgo-copy-post' ); ?></label>
            <select class="ipgs-post_status-input" id="ipgs-post-status-<?php echo $identifier; ?>"
                    name="<?php echo 'ipgs_jobs[' . $job_id . '][post_status]'; ?>">
                <option value="publish" <?php if ( $schedule_settings['post_status'] === 'publish' )
					echo 'selected' ?>><?php _e( 'Published', 'ipressgo-copy-post' ); ?></option>
                <option value="draft" <?php if ( $schedule_settings['post_status'] === 'draft' )
					echo 'selected' ?>><?php _e( 'Draft', 'ipressgo-copy-post' ); ?></option>
            </select>
            <a class="ipgs-tooltip-link" href="JavaScript:void(0);"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <div class="ipgs-tooltip">
                <p><?php _e( 'Copy posts as "published" to display posts in your blog right away. Use "draft" if you would like to review and make changes before they are posted on your site.', 'ipressgo-copy-post' ); ?>
            </div>
            <a href="JavaScript:void(0);" class="ipgs-tool ipgs-delete-job-tool"><span class="dashicons dashicons-trash"></span><?php _e( 'End This Job', 'ipressgo-copy-post' ); ?></a>
        </div>
        <input class="button-primary ipgs-start-job" type="submit" name="save" value="<?php esc_attr_e( 'Start Job', 'ipressgo-copy-post' ); ?>" />
		<?php

	}

	public function step_two_body_single( $identifier, $job_id, $api_settings, $schedule_settings, $category_data_array, $search_term = '' ) {
		$this->step_two_tabs( 'post' );
		$api_settings['params']['per_page'] = 10;
		if ( isset( $api_settings['params'] ) ) {

			foreach ( $api_settings['params'] as $param => $value ) {
				$params[ $param ] = $value;
			}

		}

		$posts_api_connect = new IPGS_Rest_Connect( $api_settings['blog_url'], 'posts', $params );
		$posts_api_connect->connect();
		$response_body = $posts_api_connect->get_response_body();
        $pag_disabled =  count( $response_body ) < $api_settings['params']['per_page'] || isset( $api_settings['params']['search'] ) ? true : false;

		$ipgs_db = new IPGS_Db();
		$post_ids_in_job = $ipgs_db->get_post_ids_for_job( $api_settings['blog_url'] );
		$submit_disabled = '';

		if ( isset( $api_settings['params']['search'] ) ) {
		    echo '<input type="hidden" class="ipgs-is-search">';
		}
		?>
		<div class="ipgs-job-setting-wrap">
		    <input type="search" class="ipgs-post-search" value="<?php esc_attr_e( $search_term ); ?>"><button class="ipgs-post-search-go"><?php _e( 'Search', 'ipressgo-copy-post' ); ?></button>
        </div>
        <div class="ipgs-job-setting-wrap">
            <?php if ( isset( $response_body[0]->id ) ) : foreach ( $response_body as $post_data ) :
                $disabled = in_array( (string)$post_data->id, $post_ids_in_job, true ) ? true : false;
                ?>
            <div class="ipgs-post-selection-wrap<?php if ( $disabled ) echo ' ipgs-post-selection-disabled'; ?>">
                <input type="checkbox" name="ipgs-post-selection" id="ipgs-post-selection-<?php echo $post_data->id; ?>" value="<?php echo $post_data->id; ?>"<?php if ( $disabled ) echo ' disabled'; ?>><label for="ipgs-post-selection-<?php echo $post_data->id; ?>"><?php esc_html_e( $post_data->title->rendered ); ?></label>
	            <?php if ( $disabled ) echo '<span><span class="dashicons dashicons-yes"></span>' . __( 'copied', 'ipressgo-copy-post' ); ?></span>
            </div>
            <?php endforeach; else:
            $submit_disabled = ' disabled';
            $pag_disabled = true;
                echo '<p>' . __( 'No results.', 'ipressgo-copy-post' ) . '</p>';
                echo '<a href="JavaScript:void(0);" id="ipgs-nav-post" class="ipgs-tool">' . __( 'Restart', 'ipressgo-copy-post' ) . '</a>';
             endif; ?>
        </div>
        <div class="ipgs-job-setting-wrap">
        <?php if ( (isset( $api_settings['params']['page'] ) && $api_settings['params']['page'] != 1) ) : ?>
        <a href="JavaScript:void(0);" id="ipgs-nav-post" class="ipgs-tool ipgs-post-pagination ipgs-post-pagination-prev" data-page="<?php echo $api_settings['params']['page'] - 1; ?>"><span class="dashicons dashicons-arrow-left-alt2"></span><?php _e( 'Prev', 'ipressgo-copy-post' ) ?></a>
        <?php endif; ?>
        <?php if ( ! $pag_disabled ) :
        $page = isset( $api_settings['params']['page'] ) ? $api_settings['params']['page'] + 1 : 2;
        ?>
        <a href="JavaScript:void(0);" id="ipgs-nav-post" class="ipgs-tool ipgs-post-pagination ipgs-post-pagination-next" data-page="<?php echo $page; ?>"><?php _e( 'Next', 'ipressgo-copy-post' ) ?><span class="dashicons dashicons-arrow-right-alt2"></span></a>
        <?php endif; ?>
        </div>
        <input class="button-primary ipgs-copy-selected" type="submit" name="save" value="<?php esc_attr_e( 'Copy Selected Posts', 'ipressgo-copy-post' ); ?>"<?php echo $submit_disabled; ?> />
		<?php

	}

	public function step_three_body( $next_post_title, $job_id, $will_post_on, $schedule_settings, $posts_remaining, $job_progress )
    {
        ?>
        <div class="ipgs-job-setting-wrap">
            <div class="ipgs-job-status-item">
			    <?php if ( $this->job_settings_progress[ $job_id ]['has_started'] && ! empty( get_the_title( $schedule_settings['last_save_new_post_id'] ) ) ) :
				    $last_post = $schedule_settings['last_save_new_post_id'];

				    if ( $last_post !== 0 ) {
					    $last_post_html = '<a href="'.get_the_permalink( $last_post ).'" target="_blank">'.get_the_title( $last_post ).'</a>';
				    }
				    ?>
                    <span class="ipgs-job-status-col-1"><?php _e( 'Last Post Copied', 'ipressgo-copy-post' ); ?></span><span class="ipgs-job-status-col-2"><?php echo $last_post_html; ?></span>
			    <?php endif; ?>
                <?php if ( ! empty( $next_post_title ) && $job_progress !== 'complete' ) : ?>
                    <span class="ipgs-job-status-col-1"><?php _e( 'Next Scheduled Post', 'ipressgo-copy-post' ); ?></span><span class="ipgs-job-status-col-2"><?php echo $next_post_title; ?></span>
                <?php endif; ?>
	            <?php if ( ! empty( $will_post_on ) && $job_progress !== 'complete' ) : ?>
                    <span class="ipgs-job-status-col-1"><?php _e( 'Will be Copied On', 'ipressgo-copy-post' ); ?></span><span class="ipgs-job-status-col-2"><?php echo $will_post_on; ?></span>
	            <?php endif; ?>
            <?php if ( $job_progress !== 'complete' ) : ?>
                <span class="ipgs-job-status-col-1"><?php _e( 'Posts Remaining', 'ipressgo-copy-post' ); ?></span><span class="ipgs-job-status-col-2"><?php echo $posts_remaining; ?></span>
            <?php endif; ?>

            </div>
        </div>
        <div class="ipgs-job-setting-wrap ipgs-job-progress">
            <p class="ipgs-job-progress-<?php esc_attr_e( $job_progress ); ?>"><?php echo $this->job_progress_message( $job_progress ); ?>
        </div>
        <div class="ipgs-tools-wrap">
            <a href="JavaScript:void(0);" class="ipgs-tool ipgs-job-settings-tool"><span class="dashicons dashicons-admin-generic"></span><?php _e( 'Options', 'ipressgo-copy-post' ); ?></a> <a href="JavaScript:void(0);" class="ipgs-tool ipgs-delete-job-tool"><span class="dashicons dashicons-trash"></span><?php _e( 'End This Job', 'ipressgo-copy-post' ); ?></a>
        </div>
        <?php

    }

    public function job_progress_message( $job_porgress ) {

	    if ( $job_porgress === 'on_hold' ) {
            return '<span class="dashicons dashicons-warning"></span>' . __( 'On Hold (no new posts found)', 'ipressgo-copy-post' );
	    } elseif ( $job_porgress === 'complete' ) {
		    return '<span class="dashicons dashicons-yes"></span>' . __( 'Complete', 'ipressgo-copy-post' );
	    } else {
	        return '';
        }

    }

    public function single_job( $job_id_input, $ipgs_jobs, $i, $new = false )
    {
	    $job_id = isset( $ipgs_jobs[ $job_id_input ]['id'] ) ? $ipgs_jobs[ $job_id_input ]['id']  : $i . '_' . time();
	    $identifier = $i;
	    $date_format = get_option( 'date_format', 'F j, Y' );

	    $ipgs_job = new IPGS_Job( $ipgs_jobs, $job_id );
	    $schedule_settings = $ipgs_job->get_schedule_settings();
	    $api_settings = $ipgs_job->get_api_settings();
	    $category_data_array = array();

	    if ( ! empty( $api_settings['blog_url'] ) ) {
		    $selected_category = isset( $api_settings['params']['categories'][0] ) ? $api_settings['params']['categories'][0] : 'any';

		    $schedule_settings['job_id'] = $job_id;

		    $category_data_array = $ipgs_job->get_available_category_data();
	    }

	    $new_class = $new ? ' ipgs-new' : '';

	    ?>
        <div id="ipgs-job-<?php echo $identifier; ?>" class="ipgs-single-job<?php echo $new_class; ?>">
	        <?php if ( $this->job_settings_progress === false ) : ?>
		        <?php $this->intro( 'step1' ); ?>
	        <?php endif; ?>
		    <?php if ( $this->job_settings_progress === false || ! $this->job_settings_progress[ $job_id ]['has_url'] ) : ?>
			    <?php $this->before_body( $api_settings ); ?>

                <div class="ipgs-job-setting-wrap ipgs-blog-url">
                    <label for="ipgs-blog-url-<?php echo $identifier; ?>"><?php _e( 'Blog URL', 'ipressgo-copy-post' ); ?>:</label>
                    <input class="ipgs-blog_url-input" id="ipgs-blog-url-<?php echo $identifier; ?>" name="<?php echo 'ipgs_jobs['.$job_id .'][blog_url]'; ?>" type="text" value="<?php echo esc_attr( $api_settings['blog_url'] ); ?>" />
                    <input class="ipgs-job_id-input" type="hidden" name="<?php echo 'ipgs_jobs['.$job_id .'][id]'; ?>" value="<?php esc_attr_e( $job_id ); ?>" >
                    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Connect', 'ipressgo-copy-post' ); ?>" />
                    <br /><span class="description"><?php _e( 'Example', 'ipressgo-copy-post' ); ?>: http://demo.wp-api.org/</span>
	        <?php if ( $i > 0 ) : ?>
                <div class="ipgs-tools-wrap">
                    <a href="JavaScript:void(0);" class="ipgs-tool ipgs-delete-job-tool"><span class="dashicons dashicons-trash"></span><?php _e( 'End this Job', 'ipressgo-copy-post' ); ?></a>
                </div>
            <?php endif; ?>
            </div>
			    <?php $this->after_body(); ?>

		    <?php elseif ( ! $this->job_settings_progress[ $job_id ]['has_schedule'] ) :
			    ?>
			    <?php if ( $new ) $this->intro( 'step2' ); ?>

			    <?php $this->inputs( $identifier, $job_id, $api_settings ); ?>

			    <?php $this->before_body( $api_settings ); ?>

			    <?php $this->step_two_body( $identifier, $job_id, $api_settings, $schedule_settings, $category_data_array, $selected_category, $date_format ); ?>

			    <?php $this->after_body(); ?>

		    <?php else :

			    $next_post = $ipgs_job->get_next_post();
			    $next_post_title = '';
			    $will_post_on = '';
			    if ( isset( $next_post->title ) ) {
				    $next_post_title = $next_post->title->rendered;

				    $next_execution = max( $schedule_settings['start_date'], $ipgs_job->get_next_job_execution_time() );

				    if ( $next_execution - time() < (24 * HOUR_IN_SECONDS) ) {
					    $time_format = get_option( 'time_format' ) ? get_option( 'time_format' ) : 'g:i a';
					    $date_format .= ' ' . $time_format;
				    }

				    $tz_offset = ipgs_get_tz_offset();

				    $raw_time = $next_execution + $tz_offset + 3600;
				    $minutes = $raw_time % 3600;
				    $time = $raw_time - $minutes;
				    $time = $next_execution + $tz_offset;

				    $will_post_on = date_i18n( $date_format, $time );
			    } elseif ( $ipgs_job->get_job_progress() !== 'complete' ) {
			        $will_post_on = __( 'On Hold (no new posts found)', 'ipressgo-copy-post' );
                }

			    $posts_remaining = $ipgs_job->get_posts_remaining();

			    if ( $posts_remaining === 'no_limit' ) {
				    $posts_remaining = __( 'No Limit', 'ipressgo-copy-post' );
                }

			    $job_progress = $ipgs_job->get_job_progress();
			    ?>
			    <?php if ( $new ) $this->intro( 'step3' ); ?>

			    <?php $this->inputs( $identifier, $job_id, $api_settings ); ?>

			    <?php $this->before_body( $api_settings ); ?>

			    <?php $this->step_three_body( $next_post_title, $job_id, $will_post_on, $schedule_settings, $posts_remaining, $job_progress ); ?>

			    <?php $this->after_body(); ?>

		    <?php endif; ?>
        </div>
	    <?php
    }

	public function job_creator( $args )
	{
		// get option 'text_string' value from the database
		global $ipgs_jobs;

		//var_dump( $ipgs_jobs );
		//delete_option( 'ipgs_jobs');
		if ( isset( $_GET['getnext'] ) ) {
			ipgs_process_jobs();
        }

        $this->set_job_settings_progress( $ipgs_jobs );

		$i = 0;
		if ( empty( $ipgs_jobs ) ) {
			$job_id = $i . '_' . time();
			$ipgs_jobs = array( $job_id => array( 'id' => $job_id ) );
        }
		foreach( $ipgs_jobs as $job ) {
			$job_id = isset( $job['id'] ) ? $job['id'] : $i . '_' . time();
			$this->single_job( $job_id, $ipgs_jobs, $i );
			$i++;
		}
		?>
        <button id="ipgs-add-new-job" class="ipgs-button"><span class="dashicons dashicons-plus"></span><?php esc_attr_e( 'Add Another Job', 'ipressgo-copy-post' ); ?></button>
        <?php
	}

	public function default_checkbox( $args )
	{
		$options = get_option( $args['option'] );
		$option_checked = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $args['default'];
		?>
        <input name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="ipgs_<?php echo $args['name']; ?>" type="checkbox" <?php if ( $option_checked == true ) echo "checked"; ?> />
        <br><?php $this->the_description( $args['description'] ); ?>
		<?php
	}

	public function the_description( $description ) {
		?>
        <span class="description"><?php echo esc_html( $description ); ?></span>
		<?php
	}

	/**
	 * Validate and sanitize form entries
	 *
	 * This is used for settings not involved in email
	 *
	 * @param array $input raw input data from the user
	 * @return array valid and sanitized data
	 * @since 1.0
	 */
	public function validate_options( $inputs ) {
		$updated_options   = get_option( 'ipgs_options', false );
		$checkbox_settings = array( 'localize_images' );
		$leave_spaces      = array();

		foreach ( $checkbox_settings as $checkbox_setting ) {
			$updated_options[ $checkbox_setting ] = false;
		}

		foreach ( $inputs as $key => $value ) {

			if ( in_array( $key, $checkbox_settings, true ) ) {

				if ( $value == 'on' ) {
					$updated_options[ $key ] = true;
				}

			} elseif ( in_array( $key, $leave_spaces, true ) ) {
				$updated_options[ $key ] = $value;
			} else {
				$updated_options[ $key ] = sanitize_text_field( $value );
			}

		}

		return $updated_options;
	}

	public function schedule_jobs( $inputs ) {
		$updated_options   = array();

		$i = 0;

		foreach ( $inputs as $job ) {
			$job_id = isset( $job['id'] ) ? sanitize_text_field( $job['id'] ) : $i;
			foreach ( $job as $key => $value ) {
			    if ( is_array( $value ) ) {
				    foreach ( $value as $key2 => $value2 ) {
					    if ( is_array( $value2 ) ) {
						    foreach ( $value2 as $key3 => $value3 ) {
							    $updated_options[ $job_id ][ $key ][ $key2 ][ $key3 ] = sanitize_text_field( $value3 );
						    }
					    } else {
						    $updated_options[ $job_id ][ $key ][ $key2 ] = sanitize_text_field( $value2 );
					    }
				    }
                } else {
				    $updated_options[ $job_id ][ $key ] = sanitize_text_field( $value );
			    }
			}
			$i++;
		}

		return $updated_options;
	}

	public function ipgs_ajax() {
		$nonce = $_POST['ipgs_nonce'];
		global $ipgs_jobs;

		if ( ! wp_verify_nonce( $nonce, 'ipgs_nonce' ) ) {
			die ( 'You did not do this the right way!' );
		}

		if ( isset( $_POST['context'] ) ) {
			$job_id = sanitize_text_field( $_POST['job_id'] );
            if ( $_POST['context'] === 'delete' ) {
                unset( $ipgs_jobs[ $job_id ] );

	            update_option( 'ipgs_jobs', $ipgs_jobs );
	            ?>
                <div id="ipgs-job-intro">
                    <p class="ipgs-alert"><?php _e( 'Job ended.', 'ipressgo-copy-post' ); ?></p>
                </div>
	            <?php
            } elseif ( $_POST['context'] === 'job_settings' ) {
                $identifier = 200;
                $date_format = get_option( 'date_format', 'F j, Y' );

                $ipgs_job = new IPGS_Job( $ipgs_jobs, $job_id );
                $schedule_settings = $ipgs_job->get_schedule_settings();
                $api_settings = $ipgs_job->get_api_settings();
                $category_data_array = array();

                if ( ! empty( $api_settings['blog_url'] ) ) {
                    $selected_category = isset( $api_settings['params']['categories'][0] ) ? $api_settings['params']['categories'][0] : 'any';

                    $schedule_settings['job_id'] = $job_id;

                    $category_data_array = $ipgs_job->get_available_category_data();
                }

                $new_class = ' ipgs-new';

                ?>
                <div id="ipgs-job-<?php echo $identifier; ?>" class="ipgs-single-job<?php echo $new_class; ?>">
                <?php
                    $this->inputs( $identifier, $job_id, $api_settings );

                    $this->before_body( $api_settings );

                    $this->step_two_body( $identifier, $job_id, $api_settings, $schedule_settings, $category_data_array, $selected_category, $date_format );

                    $this->after_body();
            } elseif ( $_POST['context'] === 'single_job' ) {
	            $identifier = 200;
	            $date_format = get_option( 'date_format', 'F j, Y' );

	            $ipgs_job = new IPGS_Job( $ipgs_jobs, $job_id );
	            $schedule_settings = $ipgs_job->get_schedule_settings();
	            $api_settings = $ipgs_job->get_api_settings();
	            $api_settings['params'] = array();
	            $api_settings['params']['page'] = isset( $_POST['page'] ) ? (int)$_POST['page'] : 1;

	            $new_class = ' ipgs-new';

	            ?>
                <div id="ipgs-job-<?php echo $identifier; ?>" class="ipgs-single-job<?php echo $new_class; ?>">
	            <?php
	            $this->inputs( $identifier, $job_id, $api_settings );

	            $this->before_body( $api_settings );

	            $this->step_two_body_single( $identifier, $job_id, $api_settings, $schedule_settings, array(), '' );

	            $this->after_body();
            }
			?>
                </div>
			<?php
        }

        die();
    }

	public function ipgs_ajax_process_selected_posts() {
		$nonce = $_POST['ipgs_nonce'];
		global $ipgs_jobs;

		if ( ! wp_verify_nonce( $nonce, 'ipgs_nonce' ) ) {
			die ( 'You did not do this the right way!' );
		}
		$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( $_POST['job_id'] ) : false;
		$is_search = isset( $_POST['is_search'] ) ? $_POST['is_search'] === 'true' : false;
		$from_search = isset( $_POST['from_search'] ) ? $_POST['from_search'] === 'true' : false;
		$search_term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$post_ids = array();
		foreach( $_POST['selected_ids'] as $post_id ) {
			$post_ids[] = (int)$post_id;
        }
		$params = array(
		        'include' => $post_ids
        );
		$posts_api_connect = new IPGS_Rest_Connect( $ipgs_jobs[ $job_id ]['blog_url'], 'posts', $params );
		$posts_api_connect->connect();

		if ( $posts_api_connect->is_successful_connection() ) {
			$response_body = $posts_api_connect->get_response_body();
			$ipgs_db = new IPGS_Db();
            $ipgs_options = get_option( 'ipgs_options', array() );
            $localize_images = isset( $ipgs_options['localize_images'] ) ? $ipgs_options['localize_images'] : false;

            if ( $localize_images ) {
                add_filter( 'ipgs_before_save_post_content', array( 'IPGS_Db', 'localize_images_in_html' ) );
            }

			$schedule_settings = array( 'job_id' => $ipgs_jobs[ $job_id ]['blog_url'] );
			if ( ! $is_search ) {
                foreach ( $response_body as $post ) {
				    $last_save_id = $ipgs_db->save_new_post( $post, $schedule_settings, false );
			    }
			}

			$identifier = 200;
            $new_class = ' ipgs-new';
            $api_settings['blog_url'] = $ipgs_jobs[ $job_id ]['blog_url'];
			$api_settings['params'] = array();
			if ( $is_search || $from_search == 0 ) {
			    $api_settings['params']['search'] = $search_term;
			} else {
			   $api_settings['params']['page'] = isset( $_POST['page'] ) ? (int)$_POST['page'] : 1;
			}
			?>
			<div id="ipgs-job-<?php echo $identifier; ?>" class="ipgs-single-job<?php echo $new_class; ?>">
	            <?php
	            $this->inputs( $identifier, $job_id, $api_settings );

	            $this->before_body( $api_settings );
                $this->step_two_body_single( 200, $job_id, $api_settings, $schedule_settings, array(), $search_term, $from_search );
	            $this->after_body();
	            ?>
            </div>
                    <?php
		} else {
			$json = array(
				'error' => __( 'No results', 'ipressgo-copy-post' ),
				'error_message' => ''
			);
			echo json_encode( $json );
		}
		die();
	}

	public function ipgs_ajax_blog_url() {
		$nonce = $_POST['ipgs_nonce'];
		global $ipgs_jobs;

		if ( ! wp_verify_nonce( $nonce, 'ipgs_nonce' ) ) {
			die ( 'You did not do this the right way!' );
		}

		if ( isset( $_POST['job_id'] ) ) {
		    $job_id = sanitize_text_field( $_POST['job_id'] );
			$ipgs_jobs[ $job_id ]['id'] = sanitize_text_field( $_POST['job_id'] );
			$ipgs_jobs[ $job_id ]['blog_url'] = sanitize_text_field( $_POST['blog_url'] );

			$posts_api_connect = new IPGS_Rest_Connect( $ipgs_jobs[ $job_id ]['blog_url'], 'posts', array() );
			$posts_api_connect->connect();

			if ( $posts_api_connect->is_successful_connection() ) {
				update_option( 'ipgs_jobs', $ipgs_jobs );

				$this->set_job_settings_progress( $ipgs_jobs );

				$this->single_job( $job_id, $ipgs_jobs, 0, true );
            } else {
			    $json = array(
			            'error' => sprintf( __( 'Could not connect to %s', 'ipressgo-copy-post' ), esc_html( stripslashes( $posts_api_connect->get_url() ) ) ),
                        'error_message' => $posts_api_connect->get_response_error() . ' - ' . __( 'Make sure that this blog uses WordPress and is available for a connection.', 'ipressgo-copy-post' )
                );
			    echo json_encode( $json );
            }
        }

		die();
	}

	public function ipgs_ajax_step_two() {
		$nonce = $_POST['ipgs_nonce'];
		global $ipgs_jobs;

		if ( ! wp_verify_nonce( $nonce, 'ipgs_nonce' ) ) {
			die ( 'You did not do this the right way!' );
		}

		if ( isset( $_POST['job_id'] ) ) {
			$job_id = sanitize_text_field( $_POST['job_id'] );
			$ipgs_jobs[ $job_id ]['params']['categories'][0] = sanitize_text_field( $_POST['category'] );
			$ipgs_jobs[ $job_id ]['limit_posts'] = ($_POST['limit_posts'] === 'true');
			$ipgs_jobs[ $job_id ]['max_posts'] = sanitize_text_field( $_POST['max_posts'] );
			$ipgs_jobs[ $job_id ]['interval_val'] = sanitize_text_field( $_POST['interval_val'] );
			$ipgs_jobs[ $job_id ]['interval_unit'] = sanitize_text_field( $_POST['interval_unit'] );
			$ipgs_jobs[ $job_id ]['post_status'] = sanitize_text_field( $_POST['post_status'] );

			$start_timestamp = strtotime( $_POST['start_date'] . ' ' . $_POST['start_time'] ) - ipgs_get_tz_offset();

			if ( $start_timestamp < time() && ! isset( $ipgs_jobs[ $job_id ]['last_save_new_post_id'] ) ) {
				$ipgs_jobs[ $job_id ]['start_date'] = date( 'Y-m-d H:i:s', time() );
				$ipgs_job = new IPGS_Job( $ipgs_jobs, $job_id );

                $schedule_settings = $ipgs_job->get_schedule_settings();
                $next_post         = $ipgs_job->get_next_post();

                $ipgs_db = new IPGS_Db();

                $schedule_settings['job_id'] = $job_id;
				$ipgs_options = get_option( 'ipgs_options', array() );
				$localize_images = isset( $ipgs_options['localize_images'] ) ? $ipgs_options['localize_images'] : false;

				if ( $localize_images ) {
					add_filter( 'ipgs_before_save_post_content', array( $ipgs_db, 'localize_images_in_html' ) );
				}

                $last_save_id = $ipgs_db->save_new_post( $next_post, $schedule_settings );
            } else {
				$ipgs_jobs[ $job_id ]['start_date'] = date( 'Y-m-d H:i:s', $start_timestamp );
			}

            update_option( 'ipgs_jobs', $ipgs_jobs );

			$this->set_job_settings_progress( $ipgs_jobs );

			$this->single_job( $job_id, $ipgs_jobs, 0, true );
		}


		die();
	}

	public function ipgs_ajax_new_job() {
		$nonce = $_POST['ipgs_nonce'];
		global $ipgs_jobs;

		if ( ! wp_verify_nonce( $nonce, 'ipgs_nonce' ) ) {
			die ( 'You did not do this the right way!' );
		}

		$iterator = count( $ipgs_jobs );

		$job_id = $iterator . '_' . time();

		$ipgs_jobs[ $job_id ] = array( 'id' => $job_id );

        update_option( 'ipgs_jobs', $ipgs_jobs );

        $this->set_job_settings_progress( $ipgs_jobs );

        $this->single_job( $job_id, $ipgs_jobs, 0, true );

		die();
	}

	/**
	 * Adds JS and CSS files to the page when on the iPressGo Syndication settings pages
	 *
	 * @since 1.0
	 */
	public function admin_scripts_and_styles() {

		if ( isset( $_GET['page'] ) && $_GET['page'] === IPGS_MENU_SLUG ) {
			wp_enqueue_style( 'ipgs_admin_styles', trailingslashit( IPGS_PLUGIN_URL ) . 'assets/ipgs-admin-styles.css', array(), IPGS_VERSION );

			wp_enqueue_script( 'ipgs_admin_scripts', trailingslashit( IPGS_PLUGIN_URL ) . 'assets/ipgs-admin-scripts.js', array(
				'jquery',
				'jquery-ui-datepicker'
			), IPGS_VERSION, false );
			wp_localize_script( 'ipgs_admin_scripts', 'ipgsAdminScript',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'ipgs_nonce' => wp_create_nonce( 'ipgs_nonce' ),
                    'confirmEnd' => __( 'End this Job? This cannot be undone.', 'ipressgo-copy-post' )
				)
			);
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker' );
		}

	}
}