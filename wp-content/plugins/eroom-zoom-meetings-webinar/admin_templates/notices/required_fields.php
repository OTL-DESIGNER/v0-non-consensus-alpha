<?php
function stm_eroom_general_admin_notice() {
	$settings = get_option( 'stm_zoom_settings', array() );
	if ( 'stm_zoom' === get_admin_page_parent() ) {
		if ( empty( $settings['sdk_key'] ) || empty( $settings['sdk_secret'] ) ) {
			$init_data = array(
				'notice_type'  => 'animate-triangle-notice only-title',
				'notice_logo'  => 'attent_triangle.svg',
				'notice_title' => esc_html__( 'Please add Meeting SDK to integrate Zoom Client functionalities and make Join In Browser work', 'eroom-zoom-meetings-webinar' ),
			);

			stm_admin_notices_init( $init_data );
		}
		if ( empty( $settings['auth_account_id'] ) || empty( $settings['auth_client_id'] ) || empty( $settings['auth_client_secret'] ) ) {
			$init_data = array(
				'notice_type'  => 'animate-triangle-notice only-title',
				'notice_logo'  => 'attent_triangle.svg',
				'notice_title' => esc_html__( 'Please complete all OAuth fields', 'eroom-zoom-meetings-webinar' ),
			);

			stm_admin_notices_init( $init_data );
		}
	}
}

add_action( 'admin_init', 'stm_eroom_general_admin_notice' );
$settings = get_option( 'stm_zoom_settings', array() );

if ( ! empty( $settings ) && ( empty( $settings['auth_account_id'] ) && empty( $settings['auth_client_id'] ) && empty( $settings['auth_client_secret'] ) ) ) {
	Migration::get_instance();
}
