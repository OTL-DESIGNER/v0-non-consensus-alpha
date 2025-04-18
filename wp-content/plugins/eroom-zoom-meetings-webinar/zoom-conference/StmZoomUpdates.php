<?php

class StmZoomUpdates {

	private static $updates = array(
		'1.2.7' => array(
			'eroom_admin_notification_transient',
		),
	);

	public static function init() {
		if ( version_compare( get_option( 'eroom_version' ), STM_ZOOM_VERSION, '<' ) ) {
			self::update_version();
		}
	}

	public static function get_updates() {
		return self::$updates;
	}

	public static function update_version() {
		update_option( 'eroom_version', sanitize_text_field( STM_ZOOM_VERSION ), true );
		self::maybe_update_db_version();
	}

	public static function needs_to_update() {
		$update_versions    = array_keys( self::get_updates() );
		$current_db_version = get_option( 'eroom_db_updates', 1 );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	private static function maybe_update_db_version() {
		if ( self::needs_to_update() ) {
			$updates          = self::get_updates();
			$eroom_db_updates = get_option( 'eroom_db_updates' );

			foreach ( $updates as $version => $callback_arr ) {
				if ( version_compare( $eroom_db_updates, $version, '<' ) ) {
					foreach ( $callback_arr as $callback ) {
						call_user_func( array( 'StmZoomUpdatesCallbacks', $callback ) );
					}
				}
			}
		}
		update_option( 'eroom_db_updates', sanitize_text_field( STM_ZOOM_DB_VERSION ), true );
	}

}
