<?php

class Migration {
	public static $instance = null;

	public static function get_instance() {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	public function __construct() {

		add_action( 'wp_ajax_nopriv_stm_zoom_migration_action', array( $this, 'stm_zoom_migration' ) );
		add_action( 'wp_ajax_stm_zoom_migration_action', array( $this, 'stm_zoom_migration' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'migration_enqueue_scripts' ), 100 );
		add_action( 'admin_footer', array( $this, 'load_template' ) );

	}

	public function stm_zoom_migration() {
		$nonce_verified = wp_verify_nonce( $_POST['nonce'], 'stm_zoom_migration_ajax' );

		$accountID    = $_POST['accountID'] ?? '';
		$clientID     = $_POST['clientID'] ?? '';
		$clientSecret = $_POST['clientSecret'] ?? '';
		$settings     = get_option( 'stm_zoom_settings', array() );

		if ( $nonce_verified ) {
			$result = \Zoom\Interfaces\S2SOAuth::get_instance()->generateAccessToken( $accountID, $clientID, $clientSecret );
			if ( ! is_wp_error( $result ) ) {
				$settings['auth_account_id']    = $accountID;
				$settings['auth_client_id']     = $clientID;
				$settings['auth_client_secret'] = $clientSecret;
				update_option( 'stm_zoom_settings', $settings );

				wp_send_json_success( array( 'message' => 'That\'s it, we\'re all done. Thank you for continuing to use eRoom with Zoom API.' ) );
			} else {
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() . ' Please double-check your credentials' ), 403 );
				} else {
					wp_send_json_error( array( 'message' => $result->get_error_message() . ' Please double-check your credentials' ), $result->get_error_code() );
				}
			}
		}
	}

	public function load_template() {
		load_template( STM_ZOOM_PATH . '/admin_templates/migration/migration.php' );
	}

	public function migration_enqueue_scripts() {
		wp_enqueue_style( 'stm-zoom-migration', STM_ZOOM_URL . 'assets/css/admin/migration.css', false, STM_ZOOM_VERSION );
		wp_enqueue_script( 'stm-zoom-migration', STM_ZOOM_URL . 'assets/js/admin/migration.js', false, STM_ZOOM_VERSION );
		wp_enqueue_style( 'stm_migration_admin', STM_ZOOM_URL . 'assets/css/admin/migration.css', false, STM_ZOOM_VERSION );
		wp_localize_script(
			'stm-zoom-migration',
			'stm_zoom_migration_demo_ajax_variable',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'stm_zoom_migration_ajax' ),
			)
		);
	}
}
