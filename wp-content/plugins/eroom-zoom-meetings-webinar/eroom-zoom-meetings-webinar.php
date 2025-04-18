<?php
/**
 * Plugin Name: eRoom - Zoom Meetings & Webinars
 * Plugin URI: https://wordpress.org/plugins/zoom-video-conference/
 * Description: eRoom Zoom Meetings & Webinars WordPress Plugin provides you with great functionality of managing Zoom meetings, scheduling options, and users directly from your WordPress dashboard.
 * The plugin is a free yet robust and reliable extension that enables direct integration of the world's leading video conferencing tool Zoom with your WordPress website.
 * Author: StylemixThemes
 * Author URI: https://stylemixthemes.com/
 * Text Domain: eroom-zoom-meetings-webinar
 * Version: 1.4.27
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} //Exit if accessed directly

define( 'STM_ZOOM_VERSION', '1.4.27' );
define( 'STM_ZOOM_DB_VERSION', '1.2.9' );
define( 'STM_ZOOM_FILE', __FILE__ );
define( 'STM_ZOOM_DIR', __DIR__ );
define( 'STM_ZOOM_PATH', dirname( STM_ZOOM_FILE ) );
define( 'STM_ZOOM_URL', plugin_dir_url( STM_ZOOM_FILE ) );
define( 'EROOM_WP_TESTED_UP', '6.4' );

/*** mailchimp integration ***/
if ( is_admin() ) {
	if ( file_exists( STM_ZOOM_DIR . '/libs/stm-mailchimp-integration/stm-mailchimp.php' ) ) {
		require_once STM_ZOOM_DIR . '/libs/stm-mailchimp-integration/stm-mailchimp.php';

		$plugin_pages      = array(
			'stm_zoom_users',
			'stm_zoom_add_user',
			'stm_zoom_reports',
			'stm_zoom_assign_host_id',
			'stm_zoom_settings',
			'stm_zoom_go_pro',
		);
		$plugin_post_types = array( 'stm-zoom', 'stm-zoom-webinar' );
		$plugin_actions    = array(
			'stm_mailchimp_integration_add_eroom-zoom-meetings-webinar',
			'stm_mailchimp_integration_not_allowed_eroom-zoom-meetings-webinar',
			'stm_mailchimp_integration_remove_eroom-zoom-meetings-webinar',
			'stm_mailchimp_integration_not_allowed_eroom-zoom-meetings-webinar',
		);

		if ( stm_mailchimp_is_show_page( $plugin_actions, $plugin_pages, $plugin_post_types ) !== false ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			add_action( 'plugins_loaded', 'init_eroom_mailchimp', 10, 1 );
			function init_eroom_mailchimp() {
				$installed_plugins = get_plugins();
				$pro_slug          = 'eroom-zoom-meetings-webinar-pro/eroom-zoom-meetings-webinar-pro.php';
				$is_pro_exist      = array_key_exists( $pro_slug, $installed_plugins ) || in_array( $pro_slug, $installed_plugins, true );

				$init_data = array(
					'plugin_title' => 'Eroom',
					'plugin_name'  => 'eroom-zoom-meetings-webinar',
					'is_pro'       => $is_pro_exist,
				);
				if ( function_exists( 'wp_get_current_user' ) ) {
					stm_mailchimp_admin_init( $init_data );
				}
			}
		}
	}
}
/*** mailchimp integration | end ***/

if ( ! is_textdomain_loaded( 'eroom-zoom-meetings-webinar' ) ) {
	load_plugin_textdomain(
		'eroom-zoom-meetings-webinar',
		false,
		'eroom-zoom-meetings-webinar/languages'
	);
}

require_once STM_ZOOM_PATH . '/zoom-app/vendor/autoload.php';
require_once STM_ZOOM_PATH . '/includes/helpers.php';
require_once STM_ZOOM_PATH . '/nuxy/NUXY.php';
require_once STM_ZOOM_PATH . '/zoom-conference/init.php';
require_once STM_ZOOM_PATH . '/vc/main.php';

if ( did_action( 'elementor/loaded' ) ) {
	require STM_ZOOM_PATH . '/elementor/StmZoomElementor.php';
}

if ( is_admin() ) {

	require_once STM_ZOOM_PATH . '/includes/item-announcements.php';
	require_once STM_ZOOM_PATH . '/includes/conflux.php';
	require_once STM_ZOOM_PATH . '/libs/admin-notices/admin-notices.php';
	require_once STM_ZOOM_PATH . '/libs/admin-notifications-popup/admin-notification-popup.php';
	require_once STM_ZOOM_PATH . '/includes/migration/migration.php';
	require_once STM_ZOOM_PATH . '/admin_templates/notices/required_fields.php';
	require_once STM_ZOOM_PATH . '/google-meet/StmERoomGoogleMeet.php';

	new StmERoomGoogleMeet();
}
