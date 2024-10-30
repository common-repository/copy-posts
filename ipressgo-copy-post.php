<?php
/*
Plugin Name: Copy Post
Description: Duplicate posts from other WordPress blogs easily with Copy Post. Also, schedule posts to duplicate automatically. Syndicate content like a pro.
Version: 1.0
Author: ipressgo
Author URI: https://ipressgo.com/
License: GPLv2 or later
Text Domain: ipressgo-copy-post
*/

/*
Copyright 2017 by iPressGo LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
/**
 * @package IPGS
 * @author iPressGo
 * @version 1.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'IPressGo_Syndication' ) ) :

	/**
	 * Main IPressGo_Syndication Class.
	 *
	 * Design pattern inspired by Pippin Williamson's Easy Digital Downloads
	 *
	 * @since 1.0
	 */
	final class IPressGo_Syndication {
		/** Singleton *************************************************************/
		/**
		 * @var IPressGo_Syndication
		 * @since 1.0
		 */
		private static $instance;

		public $admin;

		/**
		 * @var IPressGo_Syndication
		 * @since 1.0
		 * public $form;
		 */

		/**
		 * Main IPressGo_Syndication Instance.
		 *
		 * Only on instance of the form and functions at a time
		 *
		 * @since 1.0
		 * @return object|IPressGo_Syndication
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IPressGo_Syndication ) ) {
				self::$instance = new IPressGo_Syndication;
				self::$instance->constants();
				self::$instance->includes();
			}
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ipressgo-copy-post' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ipressgo-copy-post' ), '1.0' );
		}

		public static function install() {
			if ( ! wp_next_scheduled ( 'ipgs_twicehourly' ) ) {
				wp_schedule_event( time(), 'twicehourly', 'ipgs_twicehourly' );
			}
		}

		public static function deactivate() {
			wp_clear_scheduled_hook( 'ipgs_twicehourly' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function constants() {
			// Plugin version.
			if ( ! defined( 'IPGS_VERSION' ) ) {
				define( 'IPGS_VERSION', '1.0' );
			}
			// Plugin Folder Path.
			if ( ! defined( 'IPGS_PLUGIN_DIR' ) ) {
				define( 'IPGS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
			// Plugin Folder Path.
			if ( ! defined( 'IPGS_PLUGIN_URL' ) ) {
				define( 'IPGS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
			// Plugin Base Name
			if ( ! defined( 'IPGS_PLUGIN_BASENAME') ) {
				define( 'IPGS_PLUGIN_BASENAME', plugin_basename(__FILE__) );
			}
			// Plugin Title.
			if ( ! defined( 'IPGS_TITLE' ) ) {
				define( 'IPGS_TITLE' , 'iPressGo Copy Post' );
			}
			if ( ! defined( 'IPGS_ADMIN_URL' ) ) {
				define( 'IPGS_ADMIN_URL', 'admin.php?page=ipressgo-copy-post' );
			}
			if ( ! defined( 'IPGS_ADMIN_URL_SETTINGS' ) ) {
				define( 'IPGS_ADMIN_URL_SETTINGS', 'admin.php?page=ipressgo-copy-post-settings' );
			}
			if ( ! defined( 'IPGS_MENU_SLUG' ) ) {
				define( 'IPGS_MENU_SLUG', 'ipressgo-copy-post' );
			}
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function includes() {
			global $ipgs_options;
			global $ipgs_jobs;
			$ipgs_options = get_option( 'ipgs_options', array() );
			$ipgs_jobs = get_option( 'ipgs_jobs', array() );

			require_once IPGS_PLUGIN_DIR . 'inc/class-ipgs-job.php';
			require_once IPGS_PLUGIN_DIR . 'inc/class-ipgs-db.php';
			require_once IPGS_PLUGIN_DIR . 'inc/class-ipgs-rest-connect.php';

			if ( is_admin() ) {
				require_once IPGS_PLUGIN_DIR . 'inc/class-ipgs-admin.php';
				$this->admin = new IPGS_Admin;
			}
		}

	}
endif; // End if class_exists check.
register_activation_hook( __FILE__, array( 'IPressGo_Syndication', 'install' ) );
register_deactivation_hook( __FILE__, array( 'IPressGo_Syndication', 'deactivate' ) );

function ipgs_plugin_action_links( $links ) {
	$links[] = '<a href="'. esc_url( get_admin_url( null, IPGS_ADMIN_URL ) ) .'">' . __( 'Settings' ) . '</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ipgs_plugin_action_links' );

function ipgs_text_domain() {
	load_plugin_textdomain( 'ipressgo-copy-post', false, basename( dirname(__FILE__) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ipgs_text_domain' );

function ipgs_process_jobs() {
	$ipgs_jobs = get_option( 'ipgs_jobs', array() );
	$ipgs_options = get_option( 'ipgs_options', array() );
	$localize_images = isset( $ipgs_options['localize_images'] ) ? $ipgs_options['localize_images'] : false;
	include_once( ABSPATH . 'wp-admin/includes/image.php' );

	if ( $localize_images ) {
		add_filter( 'ipgs_before_save_post_content', array( 'IPGS_Db', 'localize_images_in_html' ) );
	}

	foreach ( $ipgs_jobs as $job ) {
		$job_id = $job['id'];
		$ipgs_job = new IPGS_Job( $ipgs_jobs, $job_id );

		if ( $ipgs_job->get_job_progress() !== 'complete' && $ipgs_job->get_next_job_execution_time() < (time() + 120) && $ipgs_job->start_time_has_passed() ) {
			$schedule_settings = $ipgs_job->get_schedule_settings();
			$next_post = $ipgs_job->get_next_post();

			$ipgs_db = new IPGS_Db();

			$schedule_settings['job_id'] = $job_id;

			$last_save_id = $ipgs_db->save_new_post( $next_post, $schedule_settings );

			if ( ! (int)$last_save_id > 0 ) {
				$ipgs_report = get_option( 'ipgs_report', array() );

				$ipgs_report[ $job_id ]['copy_error'] = array(
					'time' => time(),
					'schedule_settings' => $schedule_settings
				);
				update_option( 'ipgs_report', $ipgs_report, false );
			}

		} else {
			$ipgs_report = get_option( 'ipgs_report', array() );

			$ipgs_report[ $job_id ]['not_executing'] = array(
				'time' => time(),
				'progress' => $ipgs_job->get_job_progress(),
				'next_job_ex_time' => $ipgs_job->get_next_job_execution_time(),
				'start_time_passed' => $ipgs_job->start_time_has_passed()
			);
			update_option( 'ipgs_report', $ipgs_report, false );
		}

	}

}
add_action( 'ipgs_twicehourly', 'ipgs_process_jobs' );

function ipgs_cron_schedules( $schedules ){
	if( ! isset( $schedules['twicehourly'] ) ) {
		$schedules['twicehourly'] = array(
			'interval' => 30*60,
			'display' => __( 'Once every 30 minutes' ) );
	}
	return $schedules;
}
add_filter( 'cron_schedules','ipgs_cron_schedules' );

function ipgs_get_tz_offset() {
	$WP_offset = get_option( 'gmt_offset' );

	$tz_offset = 0;
	if ( ! empty( $WP_offset ) ) {
		$tz_offset = $WP_offset * HOUR_IN_SECONDS;
	}

	return $tz_offset;
}

/**
 * The main function for IPressGo_Syndication
 *
 * The main function responsible for returning the one true IPressGo_Syndication
 * Instance to functions everywhere.
 *
 * @since 1.0
 * @return object|IPressGo_Syndication The one true IPressGo_Syndication Instance.
 */
function IPGS() {
	return IPressGo_Syndication::instance();
}
// Get ipgs Running.
IPGS();
