<?php

class StmZoomAdminNotices {

	/**
	 * @return StmZoomAdminNotices constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'wp_ajax_stm_zoom_ajax_add_feedback', array( $this, 'add_feedback' ) );

		// Add Pro Banner under Zoom Settings
		add_action(
			'wpcfto_settings_screen_stm_zoom_settings_after',
			function() {
				if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
					include STM_ZOOM_PATH . '/admin_templates/notices/pro_banner.php';
				}
			}
		);

		add_action( 'stm_zoom_after_create_meeting', array( $this, 'stm_zoom_after_create_meeting' ) );

		add_action( 'stm_admin_notice_rate_eroom-zoom-meetings-webinar_single', array( $this, 'stm_zoom_admin_notice_single' ) );
	}

	/**
	 * Show Pro Notices
	 */
	public function admin_notices() {
		if ( ! empty( $_GET['post_type'] ) && ( 'stm-zoom' === $_GET['post_type'] || 'stm-zoom-webinar' === $_GET['post_type'] ) ) {
			include STM_ZOOM_PATH . '/admin_templates/notices/feedback.php';
			include STM_ZOOM_PATH . '/admin_templates/notices/pro_popup.php';
			include STM_ZOOM_PATH . '/admin_templates/notices/top_bar.php';
		}

	}

	/**
	 * Add Feedback
	 */
	public function add_feedback() {
		update_option( 'stm_zoom_feedback_added', true );
	}

	public function stm_zoom_after_create_meeting() {

		$created = get_option( 'stm_eroom_meeting_created', false );

		if ( ! $created ) {
			$data = array(
				'show_time'   => time(),
				'step'        => 0,
				'prev_action' => '',
			);
			set_transient( 'stm_eroom-zoom-meetings-webinar_single_notice_setting', $data );
			update_option( 'stm_eroom_meeting_created', true );
		}
	}

	public static function stm_zoom_admin_notice_single( $data ) {
		if ( is_array( $data ) ) {
			$data['title']   = 'Hooray!';
			$data['content'] = 'The first meeting has been created successfully. We are asking you to do a favor by rating <strong>eRoom 5 Stars up!</strong>';
		}

		return $data;
	}

}
