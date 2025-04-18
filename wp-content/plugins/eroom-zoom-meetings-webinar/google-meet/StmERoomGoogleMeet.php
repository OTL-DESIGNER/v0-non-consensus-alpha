<?php

class StmERoomGoogleMeet {
	public function __construct() {
		if ( is_admin() ) {
			if ( defined( 'STM_LMS_PRO_VERSION' ) ) {
				return false;
			}
			if ( ! $this->is_pro() ) {
				add_filter( 'stm_wpcfto_fields', array( $this, 'add_gm_fields' ), 10 );
				add_filter( 'stm_zoom_settings_fields', array( $this, 'options_page_setup' ), 400 );
			}
		}
	}

	public function is_pro() {
		if ( function_exists( 'eroom_fs' ) && eroom_fs()->is__premium_only() ) {
			return true;
		}
		return false;
	}

	public function add_gm_fields( $fields ) {
		foreach ( array( 'stm_zoom_meeting', 'stm_zoom_webinar' ) as $post_type ) {
			$fields[ $post_type ]['tab_1']['fields'] = array_merge(
				array(
					'stm_select_gm_zoom' => array(
						'type'    => 'radio',
						'label'   => esc_html__( 'Provider', 'eroom-zoom-meetings-webinar' ),
						'options' => array(
							'zoom' => esc_html__( 'Zoom', 'eroom-zoom-meetings-webinar' ),
							'gm'   => esc_html__( 'Google Meet', 'eroom-zoom-meetings-webinar' ) . "&nbsp;<a target='_blank' class='go_to_pro_link' href='" . admin_url( 'admin.php?page=stm_zoom_go_pro' ) . "'><span>PRO</span></a>",
						),
						'default' => 'zoom',
						'disable' => 1,
					),
				),
				$fields[ $post_type ]['tab_1']['fields']
			);
		}
		return $fields;
	}

	public function options_page_setup( $setups ) {
		$setups['google_meet_pro'] = array(
			'name'   => esc_html__( 'Google Meet', 'eroom-zoom-meetings-webinar' ),
			'fields' => array(
				'go_pro' => array(
					'type'  => 'notice_banner',
					'label' => esc_html__( 'To use Google Meetings and other advanced features, upgrade to PRO plugin', 'eroom-zoom-meetings-webinar' ) .
						"&nbsp;<a target='_blank' class='go_to_pro_link' href='" . admin_url( 'admin.php?page=stm_zoom_go_pro' ) . "'><span>PRO</span></a>",
				),
			),
		);
		return $setups;
	}
}

