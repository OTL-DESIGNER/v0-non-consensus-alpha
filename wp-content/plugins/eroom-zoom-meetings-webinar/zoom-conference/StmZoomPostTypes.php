<?php

class StmZoomPostTypes {

	/**
	 * @return StmZoomPostTypes constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'stm_zoom_register_post_type' ), 10 );

		if ( is_admin() ) {
			self::stm_zoom_metaboxes();
			add_filter( 'manage_stm-zoom_posts_columns', array( $this, 'stm_provider_column_title' ) );
			add_filter( 'manage_stm-zoom-webinar_posts_columns', array( $this, 'stm_provider_column_title' ) );
			add_action( 'manage_stm-zoom_posts_custom_column', array( $this, 'stm_provider_column' ), 5, 2 );
			add_action( 'manage_stm-zoom-webinar_posts_custom_column', array( $this, 'stm_provider_column' ), 5, 2 );

			add_action(
				'init',
				function() {
					add_filter( 'stm_wpcfto_fields', array( $this, 'stm_google_meet_active_disabled' ), 1000 );
				},
				1000
			);
		}

		add_action( 'add_meta_boxes', array( $this, 'stm_zoom_add_custom_box' ) );

		add_action( 'save_post', array( $this, 'update_meeting' ), 10 );

		add_action( 'before_delete_post', array( $this, 'stm_zoom_delete_meeting' ), 10 );

		add_filter( 'wp_ajax_stm_zoom_sync_meetings_webinars', array( $this, 'stm_zoom_sync_meetings_webinars' ) );

		add_action( 'bookit_appointment_status_changed', array( $this, 'stm_zoom_bookit_edit_add_meeting' ), 100, 1 );

		add_action( 'bookit_appointment_updated', array( $this, 'stm_zoom_bookit_edit_add_meeting' ), 100, 1 );

		add_action( 'save_post', array( $this, 'change_date_if_empty' ), 100, 1 );

		add_action( 'wp_ajax_stm_zoom_meeting_sign', array( $this, 'generate_signature' ) );

		add_action( 'wp_ajax_nopriv_stm_zoom_meeting_sign', array( $this, 'generate_signature' ) );
	}

	/**
	 * Generate Signature
	 */
	public function generate_signature() {

		$request = file_get_contents( 'php://input' );

		$request        = json_decode( $request );
		$api_key        = $request->api_key;
		$meeting_number = $request->meetingNumber;
		$role           = $request->role;
		$settings       = get_option( 'stm_zoom_settings', array() );
		$api_secret     = ! empty( $settings['api_secret'] ) ? $settings['api_secret'] : '';

		$time = time() * 1000 - 30000;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$data = base64_encode( $api_key . $meeting_number . $time . $role );
		$hash = hash_hmac( 'sha256', $data, $api_secret, true );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$_sig = $api_key . '.' . $meeting_number . '.' . $time . '.' . $role . '.' . base64_encode( $hash );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$res = rtrim( strtr( base64_encode( $_sig ), '+/', '-_' ), '=' );

		echo wp_json_encode( array( $res ) );

		wp_die();
	}

	/**
	 * @param $post_id
	 */
	public function change_date_if_empty( $post_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_type = sanitize_text_field( $_POST['post_type'] ?? '' );

		if ( empty( $post_type ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( 'stm-zoom' === $post_type || 'stm-zoom-webinar' === $post_type ) {
			$provider = get_post_meta( $post_id, 'stm_select_gm_zoom', true );
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$timezone = sanitize_text_field( $_POST['stm_timezone'] ?? '' );
			if ( empty( $provider ) || 'zoom' === $provider ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$start_date = apply_filters( 'eroom_sanitize_stm_date', $_POST['stm_date'] ?? '' );
				$start_date = $this->current_date( $start_date, $timezone );
				update_post_meta( $post_id, 'stm_date', $start_date );
			} else {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$start_date = apply_filters( 'eroom_sanitize_stm_start_date', $_POST['stm_start_date'] ?? '' );
				$start_date = $this->current_date( $start_date, $timezone );
				update_post_meta( $post_id, 'stm_start_date', $start_date );

				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$end_date = apply_filters( 'eroom_sanitize_stm_end_date', $_POST['stm_end_date'] ?? '' );
				$end_date = $this->current_date( $end_date, $timezone );
				update_post_meta( $post_id, 'stm_end_date', $end_date );
			}
		}

	}

	/**
	 * Registering Custom Post Type
	 */
	public function stm_zoom_register_post_type() {
		$meeting_args = array(
			'labels'              => array(
				'name'               => esc_html__( 'Meetings', 'eroom-zoom-meetings-webinar' ),
				'singular_name'      => esc_html__( 'Meeting', 'eroom-zoom-meetings-webinar' ),
				'add_new'            => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
				'add_new_item'       => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
				'edit_item'          => esc_html__( 'Edit meeting', 'eroom-zoom-meetings-webinar' ),
				'new_item'           => esc_html__( 'New meeting', 'eroom-zoom-meetings-webinar' ),
				'view_item'          => esc_html__( 'View meeting', 'eroom-zoom-meetings-webinar' ),
				'search_items'       => esc_html__( 'Search meeting', 'eroom-zoom-meetings-webinar' ),
				'not_found'          => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
				'not_found_in_trash' => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
				'menu_name'          => esc_html__( 'Meetings', 'eroom-zoom-meetings-webinar' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'stm_zoom',
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'author', 'thumbnail' ),
		);

		register_post_type( 'stm-zoom', $meeting_args ); /* Calling Register Post Type */

		$webinar_args = array(
			'labels'              => array(
				'name'               => esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
				'singular_name'      => esc_html__( 'Webinar', 'eroom-zoom-meetings-webinar' ),
				'add_new'            => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
				'add_new_item'       => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
				'edit_item'          => esc_html__( 'Edit webinar', 'eroom-zoom-meetings-webinar' ),
				'new_item'           => esc_html__( 'New webinar', 'eroom-zoom-meetings-webinar' ),
				'view_item'          => esc_html__( 'View webinar', 'eroom-zoom-meetings-webinar' ),
				'search_items'       => esc_html__( 'Search webinar', 'eroom-zoom-meetings-webinar' ),
				'not_found'          => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
				'not_found_in_trash' => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
				'menu_name'          => esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=stm-zoom-webinar',
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'author', 'thumbnail' ),
		);

		register_post_type( 'stm-zoom-webinar', $webinar_args ); /* Calling Register Post Type */
	}

	public function stm_google_meet_active_disabled( $fields ) {
		if ( ! defined( 'STM_GOOGLE_MEET_ADDON_STATUS' ) ) {
			if ( ! empty( $_GET['source'] ) && ! empty($_GET['action']) && 'stm_wpcfto_get_settings' === $_GET['action'] ) { // phpcs:ignore
				$type = get_post_meta( (int) $_GET['source'], 'stm_select_gm_zoom', true ); // phpcs:ignore
				if ( ! empty( $type ) && 'gm' === $type ) {
					$fields['stm_zoom_meeting'] = array(
						'tab_1' => array(
							'name'   => esc_html__( 'Meeting settings', 'eroom-zoom-meetings-webinar' ),
							'fields' => array(
								'stm_google_meet_enable' => array(
									'type'  => 'notice_banner',
									'label' => esc_html__( 'Enable Google Meet Addon!', 'eroom-zoom-meetings-webinar' ),
								),
							),
						),
					);
					$fields['stm_zoom_webinar'] = array(
						'tab_1' => array(
							'name'   => esc_html__( 'Webinar settings', 'eroom-zoom-meetings-webinar' ),
							'fields' => array(
								'stm_google_meet_enable' => array(
									'type'  => 'notice_banner',
									'label' => esc_html__( 'Enable Google Meet Addon!', 'eroom-zoom-meetings-webinar' ),
								),
							),
						),
					);
				}
			}
		}
		return $fields;
	}
	/**
	 * STM Zoom Post Type Settings - Post Meta Box & Fields
	 */
	public function stm_zoom_metaboxes() {
		/* Register Meta Boxes */
		add_filter(
			'stm_wpcfto_boxes',
			function( $boxes ) {

				/* Meeting Meta Box */
				$boxes['stm_zoom_meeting'] = array(
					'post_type' => array( 'stm-zoom' ),
					'label'     => esc_html__( 'Post settings', 'eroom-zoom-meetings-webinar' ),
				);

				/* Webinar Meta Box */
				$boxes['stm_zoom_webinar'] = array(
					'post_type' => array( 'stm-zoom-webinar' ),
					'label'     => esc_html__( 'Post settings', 'eroom-zoom-meetings-webinar' ),
				);

				return $boxes;
			}
		);

		/* Register Webinar Meta Box Fields */
		add_filter(
			'stm_wpcfto_fields',
			function( $fields ) {
				$country_list = self::stm_get_countries_code();

				$fields['stm_zoom_meeting'] = array(
					'tab_1' => array(
						'name'   => esc_html__( 'Meeting settings', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'stm_agenda'                   => array(
								'type'  => 'textarea',
								'label' => esc_html__( 'Meeting agenda', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_host'                     => array(
								'type'    => 'select',
								'label'   => esc_html__( 'Meeting host', 'eroom-zoom-meetings-webinar' ),
								'options' => StmZoom::get_users_options(),
							),
							'stm_select_approved_denied'   => array(
								'type'        => 'select',
								'label'       => esc_html__( 'Approved Or Denied countries', 'eroom-zoom-meetings-webinar' ),
								'options'     => array(
									1 => 'Approved countries',
									2 => 'Denied countries',
								),
								'description' => esc_html__( 'You only have to choose one option: Approved countries or Denied countries', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_multiselect_approved'     => array(
								'type'        => 'multiselect',
								'label'       => esc_html__( 'Approved Or Denied countries', 'eroom-zoom-meetings-webinar' ),
								'options'     => $country_list,
								'description' => esc_html__( 'You only have to choose one option: Approved countries or Denied countries', 'eroom-zoom-meetings-webinar' ),
								'dependency'  => array(
									'key'   => 'stm_select_approved_denied',
									'value' => 1,
								),
							),
							'stm_multiselect_denied'       => array(
								'type'        => 'multiselect',
								'label'       => esc_html__( 'Denied countries', 'eroom-zoom-meetings-webinar' ),
								'options'     => $country_list,
								'description' => esc_html__( 'You only have to choose one option: Approved countries or Denied countries', 'eroom-zoom-meetings-webinar' ),
								'dependency'  => array(
									'key'   => 'stm_select_approved_denied',
									'value' => 2,
								),
							),
							'stm_date'                     => array(
								'type'  => 'date',
								'label' => esc_html__( 'Meeting date', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_time'                     => array(
								'type'  => 'time',
								'label' => esc_html__( 'Meeting time', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_timezone'                 => array(
								'type'    => 'select',
								'label'   => esc_html__( 'Meeting timezone', 'eroom-zoom-meetings-webinar' ),
								'options' => stm_zoom_get_timezone_options(),
								'value'   => get_current_timezone(),
							),
							'stm_duration'                 => array(
								'type'  => 'number',
								'label' => esc_html__( 'Meeting duration (in min)', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_password'                 => array(
								'type'        => 'text',
								'label'       => esc_html__( 'Meeting password', 'eroom-zoom-meetings-webinar' ),
								'description' => esc_html__( 'Only users who have the invite link or passcode can join the meeting', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_waiting_room'             => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Waiting room', 'eroom-zoom-meetings-webinar' ),
								'description' => esc_html__( 'Only users admitted by the host can join the meeting', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_join_before_host'         => array(
								'type'       => 'checkbox',
								'label'      => esc_html__( 'Allow participants to join anytime', 'eroom-zoom-meetings-webinar' ),
								'dependency' => array(
									'key'   => 'stm_waiting_room',
									'value' => 'empty',
								),
							),
							'stm_host_join_start'          => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Host video', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_start_after_participants' => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Participants video', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_mute_participants'        => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Mute participants upon entry', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_enforce_login'            => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Require authentication to join: Sign in to Zoom', 'eroom-zoom-meetings-webinar' ),
								'description' => esc_html__( 'Only authenticated users can join meetings. This setting works only for Zoom accounts with Pro license or higher', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_alternative_hosts'        => array(
								'type'      => 'autocomplete',
								'label'     => esc_html__( 'Alternative hosts', 'eroom-zoom-meetings-webinar' ),
								'post_type' => array(),
							),
						),
					),
				);

				$fields['stm_zoom_webinar'] = array(
					'tab_1' => array(
						'name'   => esc_html__( 'Webinar settings', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'stm_agenda'                   => array(
								'type'  => 'textarea',
								'label' => esc_html__( 'Webinar agenda', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_host'                     => array(
								'type'    => 'select',
								'label'   => esc_html__( 'Webinar host', 'eroom-zoom-meetings-webinar' ),
								'options' => StmZoom::get_users_options(),
							),
							'stm_date'                     => array(
								'type'  => 'date',
								'label' => esc_html__( 'Webinar date', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_time'                     => array(
								'type'  => 'time',
								'label' => esc_html__( 'Webinar time', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_timezone'                 => array(
								'type'    => 'select',
								'label'   => esc_html__( 'Webinar timezone', 'eroom-zoom-meetings-webinar' ),
								'options' => stm_zoom_get_timezone_options(),
								'value'   => get_current_timezone(),
							),
							'stm_duration'                 => array(
								'type'  => 'number',
								'label' => esc_html__( 'Webinar duration (in min)', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_password'                 => array(
								'type'        => 'text',
								'label'       => esc_html__( 'Webinar password', 'eroom-zoom-meetings-webinar' ),
								'description' => esc_html__( 'Only users who have the invite link or passcode can join the webinar', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_waiting_room'             => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Waiting room', 'eroom-zoom-meetings-webinar' ),
								'description' => esc_html__( 'Only users admitted by the host can join the meeting', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_join_before_host'         => array(
								'type'       => 'checkbox',
								'label'      => esc_html__( 'Allow participants to join anytime', 'eroom-zoom-meetings-webinar' ),
								'dependency' => array(
									'key'   => 'stm_waiting_room',
									'value' => 'empty',
								),
							),
							'stm_host_join_start'          => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Host video', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_start_after_participants' => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Participants video', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_mute_participants'        => array(
								'type'  => 'checkbox',
								'label' => esc_html__( 'Mute participants upon entry', 'eroom-zoom-meetings-webinar' ),
							),
							'stm_alternative_hosts'        => array(
								'type'      => 'autocomplete',
								'label'     => esc_html__( 'Alternative hosts', 'eroom-zoom-meetings-webinar' ),
								'post_type' => array(),
							),
						),
					),
				);

				return $fields;
			}
		);
	}

	/**
	 * Adding STM Zoom Post Type Custom Box - Zoom meeting info
	 */
	public function stm_zoom_add_custom_box() {
		/* Meeting Meta Box for Shortcode */
		$meeting_screens = array( 'stm-zoom' );
		add_meta_box( 'stm_zoom_info', 'Meeting info', 'StmZoomPostTypes::meeting_info_template', $meeting_screens, 'side', 'high' );

		/* Webinar Meta Box for Shortcode */
		$webinar_screens = array( 'stm-zoom-webinar' );
		add_meta_box( 'stm_zoom_webinar_info', 'Webinar info', 'StmZoomPostTypes::webinar_info_template', $webinar_screens, 'side', 'high' );
	}

	/**
	 * Meeting shortcode template
	 *
	 * @param $post
	 * @param $meta
	 */
	public static function meeting_info_template( $post, $meta ) {
		$meeting_data = get_post_meta( $post->ID, 'stm_zoom_data', true );
		$html         = '';
		if ( ! empty( $meeting_data ) && ! empty( $meeting_data['id'] ) ) {
			$html .= '<p>' . esc_html__( 'Meeting shortcode', 'eroom-zoom-meetings-webinar' ) . '</p>';
			$html .= '<p><strong>[stm_zoom_conference post_id="' . esc_html( $post->ID ) . '"]</strong></p>';
		}
		echo wp_kses_post( apply_filters( 'stm_add_zoom_recurring_meeting_data_occurrences_html', $html, $post ) );
		do_action( 'stm_add_zoom_recurring_meeting_data_occurrences', $meeting_data );
	}

	/**
	 * Webinar shortcode template
	 *
	 * @param $post
	 * @param $meta
	 */
	public static function webinar_info_template( $post, $meta ) {
		$webinar_data = get_post_meta( $post->ID, 'stm_zoom_data', true );
		$html         = '';

		if ( ! empty( $webinar_data ) && ! empty( $webinar_data['id'] ) ) {
			$html .= '<p>' . esc_html__( 'Webinar shortcode', 'eroom-zoom-meetings-webinar' ) . '</p>';
			$html .= '<p><strong>[stm_zoom_webinar post_id="' . esc_html( $post->ID ) . '"]</strong></p>';
		} elseif ( ! empty( $webinar_data ) && ! empty( $webinar_data['message'] ) ) {
			$html .= '<p><strong style="color: #f00;">';
			$html .= wp_kses_post( apply_filters( 'stm_zoom_escape_output', $webinar_data['message'] ) );
			$html .= '</strong></p>';
		}

		echo wp_kses_post( apply_filters( 'stm_add_webinar_recurring_meeting_data_occurrences_html', $html, $post ) );
		do_action( 'stm_add_zoom_recurring_meeting_data_occurrences', $webinar_data );
	}

	/**
	 * Zoom & Bookit Integration
	 *
	 * @param $appointment_id
	 */
	public function stm_zoom_bookit_edit_add_meeting( $appointment_id ) {
		$settings = get_option( 'stm_zoom_settings', array() );
		if ( defined( 'BOOKIT_VERSION' ) && ! empty( $settings['bookit_integration'] ) && $settings['bookit_integration'] ) {
			$appointment       = \Bookit\Classes\Database\Appointments::get( 'id', $appointment_id );
			$appointment_posts = get_posts(
				array(
					'post_type'   => 'stm-zoom',
					'numberposts' => 1,
					'meta_key'    => 'appointment_id',
					'meta_value'  => $appointment_id,
				)
			);

			if ( \Bookit\Classes\Database\Appointments::$approved !== $appointment->status ) {
				if ( ! empty( $appointment_posts ) && ! empty( $appointment_posts[0] ) ) {
					wp_delete_post( intval( $appointment_posts[0]->ID ) );
				}
				return;
			}

			$customer = \Bookit\Classes\Database\Customers::get( 'id', $appointment->customer_id );
			$staff    = \Bookit\Classes\Database\Staff::get( 'id', $appointment->staff_id );
			$service  = \Bookit\Classes\Database\Services::get( 'id', $appointment->service_id );
			$hosts    = StmZoom::stm_zoom_get_users();
			$host_id  = '';

			if ( ! empty( $hosts ) ) {
				foreach ( $hosts as $host ) {
					if ( $host['email'] === $staff->email ) {
						$host_id = $host['id'];
					}
				}
				if ( empty( $host_id ) ) {
					$host_id = $hosts[0]['id'];
				}
			}

			$meeting = array(
				'post_title'  => sprintf( /* translators: %s: string, number */ __( 'Appointment #%1$s - %2$s', 'eroom-zoom-meetings-webinar' ), $appointment->id, $service->title ),
				'post_type'   => 'stm-zoom',
				'post_status' => 'publish',
				'post_author' => ( ! empty( $customer->wp_user_id ) ) ? $customer->wp_user_id : 1,
				'meta_input'  => array(
					'appointment_id' => $appointment_id,
					'stm_agenda'     => sprintf(
						/* translators: %s: string, number */
						__( 'Customer: %1$s, %2$s, %3$s. Payment via %4$s: %5$s', 'eroom-zoom-meetings-webinar' ),
						$customer->full_name,
						$customer->phone,
						$customer->email,
						$appointment->payment_method,
						$appointment->payment_status
					),
					'stm_host'       => $host_id,
					'stm_date'       => $appointment->date_timestamp * 1000,
					'stm_time'       => gmdate( 'H:i', $appointment->start_time ),
					'stm_timezone'   => get_current_timezone(),
					'stm_duration'   => intval( abs( $appointment->start_time - $appointment->end_time ) / 60 ),
				),
			);

			/**
			 * Create / Update Post
			 */
			if ( ! empty( $appointment_posts ) && ! empty( $appointment_posts[0] ) ) {
				$meeting['ID'] = $appointment_posts[0]->ID;
				$post_id       = wp_update_post( $meeting );
				update_post_meta( $post_id, 'stm_date', abs( $appointment->date_timestamp * 1000 ) );
			} else {
				$post_id = wp_insert_post( $meeting );
				update_post_meta( $post_id, 'stm_date', abs( $appointment->date_timestamp * 1000 ) );
			}

			/**
			 * Create / Update Zoom Meeting
			 */
			if ( ! empty( $post_id ) ) {
				$api_key            = ! empty( $settings['api_key'] ) ? $settings['api_key'] : '';
				$api_secret         = ! empty( $settings['api_secret'] ) ? $settings['api_secret'] : '';
				$auth_account_id    = ! empty( $settings['auth_account_id'] ) ? $settings['auth_account_id'] : '';
				$auth_client_id     = ! empty( $settings['auth_client_id'] ) ? $settings['auth_client_id'] : '';
				$auth_client_secret = ! empty( $settings['auth_client_secret'] ) ? $settings['auth_client_secret'] : '';

				$host_id    = sanitize_text_field( $host_id );
				$title      = sanitize_text_field( $meeting['post_title'] );
				$agenda     = sanitize_text_field( $meeting['meta_input']['stm_agenda'] );
				$start_date = apply_filters( 'eroom_sanitize_stm_date', $meeting['meta_input']['stm_date'] );
				$start_time = apply_filters( 'eroom_sanitize_stm_date', $meeting['meta_input']['stm_time'] );
				$timezone   = get_current_timezone();
				$duration   = intval( $meeting['meta_input']['stm_duration'] );

				$meeting_start = strtotime( 'today', ( ( $start_date ) / 1000 ) );
				if ( ! empty( $start_time ) ) {
					$time = explode( ':', $start_time );
					if ( is_array( $time ) && count( $time ) === 2 ) {
						$meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
					}
				}

				$meeting_start = gmdate( 'Y-m-d\TH:i:s', $meeting_start );
				$data          = array(
					'topic'      => $title,
					'type'       => 2,
					'start_time' => $meeting_start,
					'agenda'     => $agenda,
					'timezone'   => $timezone,
					'duration'   => $duration,
				);

				$password = get_post_meta( $post_id, 'stm_password', true );
				if ( empty( $password ) ) {
					$generate_password = ! empty( $settings['generate_password'] ) ? $settings['generate_password'] : false;
					if ( $generate_password ) {
						$password         = wp_generate_password( 8, false );
						$data['password'] = $password;

						update_post_meta( $post_id, 'stm_password', $password );
					}
				}

				$meeting_data = get_post_meta( $post_id, 'stm_zoom_data', true );

				if ( ( ( ! empty( $api_key ) && ! empty( $api_secret ) ) || ( ! empty( $auth_account_id ) && ! empty( $auth_client_id ) && ! empty( $auth_client_secret ) ) ) && ! empty( $host_id ) ) {
					remove_action( 'save_post', array( $this, 'update_meeting' ), 10 );
					remove_action( 'save_post', array( $this, 'change_date_if_empty' ), 10 );

					$zoom_endpoint = new \Zoom\Endpoint\Meetings();

					if ( empty( $meeting_data['id'] ) ) {
						$new_meeting = $zoom_endpoint->create( $host_id, $data );
						$meeting_id  = $new_meeting['id'];

						update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );

						do_action( 'stm_zoom_after_create_meeting', $post_id );
					} else {
						$meeting_id = $meeting_data['id'];

						$zoom_endpoint->update( $meeting_id, $data );

						do_action( 'stm_zoom_after_update_meeting', $post_id );
					}

					if ( ! empty( $customer->email ) ) {
						$message  = sprintf( /* translators: %s: string, number */ esc_html__( 'Hello, your meeting will begin at: %1$s, %2$s', 'eroom-zoom-meetings-webinar' ), $meeting['meta_input']['stm_time'], gmdate( 'F j, Y', $appointment->date_timestamp ) ) . '<br>';
						$message .= esc_html__( 'Your meeting url: ', 'eroom-zoom-meetings-webinar' );
						$message .= '<a href="https://zoom.us/j/' . esc_attr( $meeting_id ) . '" >' . esc_html( 'https://zoom.us/j/' . $meeting_id ) . '</a><br>';
						$message .= sprintf( /* translators: %s: string */ esc_html__( 'Your meeting password: %s', 'eroom-zoom-meetings-webinar' ), $password );

						$headers[] = 'Content-Type: text/html; charset=UTF-8';

						wp_mail( $customer->email, sprintf( /* translators: %s: string */ esc_html__( 'Meeting Notification: %s', 'eroom-zoom-meetings-webinar' ), $title ), $message, $headers );
					}
					if ( ! empty( $new_meeting['host_email'] ) ) {
						$message  = sprintf( /* translators: %s: string */ esc_html__( 'Hello, new meeting will begin at: %1$s, %2$s', 'eroom-zoom-meetings-webinar' ), $meeting['meta_input']['stm_time'], gmdate( 'F j, Y', $appointment->date_timestamp ) ) . '<br>';
						$message .= esc_html__( 'Meeting url: ', 'eroom-zoom-meetings-webinar' );
						$message .= '<a href="https://zoom.us/j/' . esc_attr( $meeting_id ) . '" >' . esc_html( 'https://zoom.us/j/' . $meeting_id ) . '</a><br>';
						$message .= sprintf( /* translators: %s: string */ esc_html__( 'Meeting password: %s', 'eroom-zoom-meetings-webinar' ), $password );

						$headers[] = 'Content-Type: text/html; charset=UTF-8';

						wp_mail( $new_meeting['host_email'], sprintf( /* translators: %s: string */ esc_html__( 'Meeting Notification: %s', 'eroom-zoom-meetings-webinar' ), $title ), $message, $headers );
					}
				}
			}
		}
	}

	/**
	 * Customize Update Meeting & Webinar Post Type data
	 *
	 * @param $post_id
	 */
	public function update_meeting( $post_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_type   = ! empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		$is_revision = wp_is_post_revision( $post_id );

		if ( ! empty( $is_revision ) ) {
			$post_id = $is_revision;
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( 'stm-zoom' === $post_type || 'stm-zoom-webinar' === $post_type ) {
			$settings           = get_option( 'stm_zoom_settings', array() );
			$api_key            = ! empty( $settings['api_key'] ) ? $settings['api_key'] : '';
			$api_secret         = ! empty( $settings['api_secret'] ) ? $settings['api_secret'] : '';
			$auth_account_id    = ! empty( $settings['auth_account_id'] ) ? $settings['auth_account_id'] : '';
			$auth_client_id     = ! empty( $settings['auth_client_id'] ) ? $settings['auth_client_id'] : '';
			$auth_client_secret = ! empty( $settings['auth_client_secret'] ) ? $settings['auth_client_secret'] : '';

			$host_id                   = sanitize_text_field( $_POST['stm_host'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$title                     = sanitize_text_field( $_POST['post_title'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$agenda                    = sanitize_text_field( $_POST['stm_agenda'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$start_date                = apply_filters( 'eroom_sanitize_stm_date', sanitize_text_field( $_POST['stm_date'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$start_time                = sanitize_text_field( $_POST['stm_time'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$timezone                  = sanitize_text_field( $_POST['stm_timezone'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$duration                  = intval( $_POST['stm_duration'] ?? 60 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$password                  = sanitize_text_field( $_POST['stm_password'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$waiting_room              = ! empty( $_POST['stm_waiting_room'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$join_before_host          = ! empty( $_POST['stm_join_before_host'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$host_join_start           = ! empty( $_POST['stm_host_join_start'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$start_after_participantst = ! empty( $_POST['stm_start_after_participants'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$mute_participants         = ! empty( $_POST['stm_mute_participants'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$enforce_login             = ! empty( $_POST['stm_enforce_login'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( empty( $password ) ) {
				$generate_password = ! empty( $settings['generate_password'] ) ? $settings['generate_password'] : false;
				if ( $generate_password ) {
					$password              = wp_generate_password( 8, false );
					$_POST['stm_password'] = $password;
				}
			}

			$start_date = $this->current_date( $start_date, $timezone );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$alternative_hosts = sanitize_text_field( $_POST['stm_alternative_hosts'] ?? '' );

			if ( is_array( $alternative_hosts ) && ! empty( $alternative_hosts ) ) {
				$alternative_hosts = implode( ',', $alternative_hosts );
			}

			$meeting_start = strtotime( 'today', ( ( $start_date ) / 1000 ) );
			if ( ! empty( $start_time ) ) {
				$time = explode( ':', $start_time );
				if ( is_array( $time ) && count( $time ) === 2 ) {
					$meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
				}
			}
			$meeting_start                 = gmdate( 'Y-m-d\TH:i:s', $meeting_start );
			$stm_approved_denied_countries = self::stm_approved_denied_countries();
			$data                          = array(
				'topic'      => $title,
				'type'       => 2,
				'start_time' => $meeting_start,
				'agenda'     => $agenda,
				'timezone'   => $timezone,
				'duration'   => $duration,
				'password'   => $password,
				'settings'   => array(
					'waiting_room'           => $waiting_room,
					'join_before_host'       => $join_before_host,
					'host_video'             => $host_join_start,
					'participant_video'      => $start_after_participantst,
					'mute_upon_entry'        => $mute_participants,
					'meeting_authentication' => $enforce_login,
					'alternative_hosts'      => $alternative_hosts,
				),
			);

			if ( $stm_approved_denied_countries ) {
				$data['settings'] = array_merge( $data['settings'], array( 'approved_or_denied_countries_or_regions' => $stm_approved_denied_countries ) );
			}

			$meeting_data = get_post_meta( $post_id, 'stm_zoom_data', true );

			if ( ( ( ! empty( $api_key ) && ! empty( $api_secret ) ) || ( ! empty( $auth_account_id ) && ! empty( $auth_client_id ) && ! empty( $auth_client_secret ) ) ) && ! empty( $host_id ) ) {
				remove_action( 'save_post', array( $this, 'update_meeting' ), 10 );

				if ( 'stm-zoom' === $post_type ) {
					$zoom_endpoint = new \Zoom\Endpoint\Meetings();
				} elseif ( 'stm-zoom-webinar' === $post_type ) {
					$zoom_endpoint = new \Zoom\Endpoint\Webinars();
				}

				$data = apply_filters( 'stm_add_zoom_recurring_meeting_data', $data );

				$option_recurring_ids = get_option( 'stm_recurring_meeting_ids', array() );
				$key                  = array_search( $post_id, $option_recurring_ids, true );

				if ( false !== $key ) {
					unset( $option_recurring_ids[ $key ] );
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST['stm_recurring_enabled'] ) && ( 'on' === $_POST['stm_recurring_enabled'] ) ) {
					$option_recurring_ids[] = $post_id;
				}

				update_option( 'stm_recurring_meeting_ids', $option_recurring_ids );

				if ( empty( $meeting_data['id'] ) ) {
					$new_meeting = $zoom_endpoint->create( $host_id, $data );
					update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );
					do_action( 'stm_zoom_after_create_meeting', $post_id );
				} else {
					$meeting_id     = $meeting_data['id'];
					$update_meeting = $zoom_endpoint->update( $meeting_id, $data );

					if ( isset( $update_meeting['code'] ) && ( 204 === $update_meeting['code'] ) ) {
						$zoom_meeting_data = $zoom_endpoint->meeting( $meeting_id );
						update_post_meta( $post_id, 'stm_zoom_data', $zoom_meeting_data );
					}

					do_action( 'stm_zoom_after_update_meeting', $post_id );
				}
			}
		}
	}

	/**
	 * Delete Meeting & Webinar from Zoom
	 *
	 * @param $post_id
	 */
	public function stm_zoom_delete_meeting( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( 'stm-zoom' === $post_type || 'stm-zoom-webinar' === $post_type ) {
			$settings     = get_option( 'stm_zoom_settings', array() );
			$meeting_data = get_post_meta( $post_id, 'stm_zoom_data', true );

			$api_key            = ! empty( $settings['api_key'] ) ? $settings['api_key'] : '';
			$api_secret         = ! empty( $settings['api_secret'] ) ? $settings['api_secret'] : '';
			$auth_account_id    = ! empty( $settings['auth_account_id'] ) ? $settings['auth_account_id'] : '';
			$auth_client_id     = ! empty( $settings['auth_client_id'] ) ? $settings['auth_client_id'] : '';
			$auth_client_secret = ! empty( $settings['auth_client_secret'] ) ? $settings['auth_client_secret'] : '';

			if ( ( ( ! empty( $api_key ) && ! empty( $api_secret ) ) || ( ! empty( $auth_account_id ) && ! empty( $auth_client_id ) && ! empty( $auth_client_secret ) ) ) && ! empty( $meeting_data['id'] ) ) {
				if ( 'stm-zoom' === $post_type ) {
					$zoom_endpoint = new \Zoom\Endpoint\Meetings();
				} elseif ( 'stm-zoom-webinar' === $post_type ) {
					$zoom_endpoint = new \Zoom\Endpoint\Webinars();
				}

				$zoom_endpoint->remove( $meeting_data['id'] );
			}
		}
	}

	/**
	 * Synchronize Zoom Meetings and Webinars
	 */
	public function stm_zoom_sync_meetings_webinars() {
		check_ajax_referer( 'zoom-sync-nonce', 'nonce' );
		$post_type = 'stm-zoom';
		if ( ! empty( $_POST['zoom_type'] ) ) {
			$post_type = $_POST['zoom_type'];
		}

		$settings           = get_option( 'stm_zoom_settings', array() );
		$api_key            = $settings['api_key'] ?? '';
		$api_secret         = $settings['api_secret'] ?? '';
		$auth_account_id    = $settings['auth_account_id'] ?? '';
		$auth_client_id     = $settings['auth_client_id'] ?? '';
		$auth_client_secret = $settings['auth_client_secret'] ?? '';
		$meeting_ids        = array();
		$zoom_type          = 'meetings';

		if ( ( ! empty( $api_key ) && ! empty( $api_secret ) ) || ( ! empty( $auth_account_id ) && ! empty( $auth_client_id ) && ! empty( $auth_client_secret ) ) ) {
			// Send Meetings / Webinars to Zoom Service.
			if ( 'stm-zoom' === $post_type ) {
				$zoom_endpoint = new \Zoom\Endpoint\Meetings();
			} elseif ( 'stm-zoom-webinar' === $post_type ) {
				$zoom_endpoint = new \Zoom\Endpoint\Webinars();
				$zoom_type     = 'webinars';
			}

			$args       = array(
				'numberposts' => -1,
				'post_type'   => $post_type,
			);
			$zoom_posts = get_posts( $args );

			foreach ( $zoom_posts as $post ) {
				$post_id                   = $post->ID;
				$meeting_data              = get_post_meta( $post_id, 'stm_zoom_data', true );
				$title                     = sanitize_text_field( $post->post_title );
				$agenda                    = sanitize_text_field( get_post_meta( $post_id, 'stm_agenda', true ) );
				$start_date                = ! empty( get_post_meta( $post_id, 'stm_date', true ) ) ? intval( get_post_meta( $post_id, 'stm_date', true ) ) : '';
				$start_time                = sanitize_text_field( get_post_meta( $post_id, 'stm_time', true ) );
				$timezone                  = sanitize_text_field( get_post_meta( $post_id, 'stm_timezone', true ) );
				$duration                  = ! empty( get_post_meta( $post_id, 'stm_duration', true ) ) ? intval( get_post_meta( $post_id, 'stm_duration', true ) ) : 60;
				$password                  = sanitize_text_field( get_post_meta( $post_id, 'stm_password', true ) );
				$waiting_room              = ! empty( get_post_meta( $post_id, 'stm_waiting_room', true ) );
				$join_before_host          = ! empty( get_post_meta( $post_id, 'stm_join_before_host', true ) );
				$host_join_start           = ! empty( get_post_meta( $post_id, 'stm_host_join_start', true ) );
				$start_after_participantst = ! empty( get_post_meta( $post_id, 'stm_start_after_participants', true ) );
				$mute_participants         = ! empty( get_post_meta( $post_id, 'stm_mute_participants', true ) );
				$enforce_login             = ! empty( get_post_meta( $post_id, 'stm_enforce_login', true ) );
				$host_id                   = sanitize_text_field( get_post_meta( $post_id, 'stm_host', true ) );
				$alternative_hosts         = sanitize_text_field( get_post_meta( $post_id, 'stm_alternative_hosts', true ) );

				if ( is_array( $alternative_hosts ) && ! empty( $alternative_hosts ) ) {
					$alternative_hosts = implode( ',', $alternative_hosts );
				}

				$meeting_start = strtotime( 'today', ( intval( $start_date ) / 1000 ) );
				if ( ! empty( $start_time ) ) {
					$time = explode( ':', $start_time );
					if ( is_array( $time ) && count( $time ) === 2 ) {
						$meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
					}
				}
				$meeting_start = gmdate( 'Y-m-d\TH:i:s', $meeting_start );

				$data = array(
					'topic'      => $title,
					'type'       => 2,
					'start_time' => $meeting_start,
					'agenda'     => $agenda,
					'timezone'   => $timezone,
					'duration'   => $duration,
					'password'   => $password,
					'settings'   => array(
						'waiting_room'           => $waiting_room,
						'join_before_host'       => $join_before_host,
						'host_video'             => $host_join_start,
						'participant_video'      => $start_after_participantst,
						'mute_upon_entry'        => $mute_participants,
						'meeting_authentication' => $enforce_login,
						'alternative_hosts'      => $alternative_hosts,
					),
				);

				$recurring_enabled = ! empty( get_post_meta( $post_id, 'stm_recurring_enabled', true ) ) ? true : false;

				if ( $recurring_enabled ) {
					$data = $this->syn_meeting_webinar_set_data( $post_id, $zoom_type, $data );
				}

				if ( empty( $meeting_data['id'] ) ) {
					$new_meeting = $zoom_endpoint->create( $host_id, $data );

					$meeting_ids[] = $new_meeting['id'];

					update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );

					do_action( 'stm_zoom_after_create_meeting', $post_id );
				} else {
					$meeting_id = $meeting_data['id'];

					$zoom_endpoint->update( $meeting_id, $data );

					$meeting_ids[] = $meeting_data['id'];

					do_action( 'stm_zoom_after_update_meeting', $post_id );
				}
			}

			wp_reset_postdata();

			// Get Meetings / Webinars from Zoom Service.
			$zoom_meetings = $zoom_endpoint->meetings_list( 'me', array( 'page_size' => 100 ) );

			if ( ! empty( $zoom_meetings[ $zoom_type ] ) ) {
				foreach ( $zoom_meetings[ $zoom_type ] as $meeting ) {
					if ( in_array( $meeting['id'], $meeting_ids, true ) ) {
						continue;
					}

					$zoom_meeting = $zoom_endpoint->meeting( $meeting['id'] );

					$meeting = array(
						'post_title'  => wp_strip_all_tags( $meeting['topic'] ),
						'post_status' => 'publish',
						'post_type'   => $post_type,
					);

					$new_post_id = wp_insert_post( $meeting );

					$stm_time = new DateTime( $zoom_meeting['start_time'], new DateTimeZone( 'UTC' ) );
					$stm_time->setTimezone( new DateTimeZone( $zoom_meeting['timezone'] ) );

					update_post_meta( $new_post_id, 'stm_zoom_data', $zoom_meeting );
					update_post_meta( $new_post_id, 'stm_agenda', $zoom_meeting['agenda'] );
					update_post_meta( $new_post_id, 'stm_date', intval( strtotime( gmdate( 'Y-m-d 00:00:00', strtotime( $zoom_meeting['start_time'] ) ) ) * 1000 ) );
					update_post_meta( $new_post_id, 'stm_time', $stm_time->format( 'H:i' ) );
					update_post_meta( $new_post_id, 'stm_timezone', $zoom_meeting['timezone'] );
					update_post_meta( $new_post_id, 'stm_duration', $zoom_meeting['duration'] );
					update_post_meta( $new_post_id, 'stm_host', $zoom_meeting['host_id'] );
					update_post_meta( $new_post_id, 'stm_alternative_hosts', $zoom_meeting['settings']['alternative_hosts'] );
					update_post_meta( $new_post_id, 'stm_password', $zoom_meeting['password'] );
					update_post_meta( $new_post_id, 'stm_waiting_room', $zoom_meeting['settings']['waiting_room'] );
					update_post_meta( $new_post_id, 'stm_join_before_host', $zoom_meeting['settings']['join_before_host'] );
					update_post_meta( $new_post_id, 'stm_host_join_start', $zoom_meeting['settings']['host_video'] );
					update_post_meta( $new_post_id, 'stm_start_after_participants', $zoom_meeting['settings']['participant_video'] );
					update_post_meta( $new_post_id, 'stm_mute_participants', $zoom_meeting['settings']['mute_upon_entry'] );
					update_post_meta( $new_post_id, 'stm_enforce_login', $zoom_meeting['settings']['enforce_login'] );

					if ( in_array( $zoom_meeting['type'], StmZoomAPITypes::TYPES_RECURRING_AND_NO_FIXED, true ) ) {
						$this->syn_meeting_webinar_update_data( $new_post_id, $zoom_meeting );
					}
				}
			}

			wp_send_json( 'done' );
		} else {
			wp_send_json( 'Please set your Zoom API keys.' );
		}
	}

	public function current_date( $start_date, $timezone ) {
		if ( is_numeric( $start_date ) && 0 !== $start_date ) {
			return $start_date;
		}

		return strtotime( 'today' ) . '000';
	}

	protected function syn_meeting_webinar_set_data( $post_id, $zoom_type, $data ) {
		$recurring_type = get_post_meta( $post_id, 'stm_recurring_type', true );

		if ( in_array( $recurring_type, StmZoomAPITypes::TYPES_RECURRENCE_ALL, true ) ) {
			switch ( $recurring_type ) {
				case StmZoomAPITypes::TYPE_RECURRENCE_DAILY:
					$repeat_interval = get_post_meta( $post_id, 'stm_recurring_daily_repeat_interval', true );
					break;
				case StmZoomAPITypes::TYPE_RECURRENCE_WEEKLY:
					$repeat_interval = get_post_meta( $post_id, 'stm_recurring_weekly_repeat_interval', true );
					$weekly_days     = get_post_meta( $post_id, 'stm_recurring_weekly_days', true );
					$weekly_days     = preg_replace( '/[^0-9,]/', '', $weekly_days );

					$data['recurrence']['weekly_days'] = $weekly_days;
					break;
				case StmZoomAPITypes::TYPE_RECURRENCE_MONTHLY:
					$repeat_interval               = get_post_meta( $post_id, 'stm_recurring_monthly_repeat_interval', true );
					$recurring_monthly_occurs_type = get_post_meta( $post_id, 'stm_recurring_monthly_occurs', true );

					if ( 'by_day' === $recurring_monthly_occurs_type ) {
						$recurring_monthly_day = get_post_meta( $post_id, 'stm_recurring_monthly_day', true );

						$data['recurrence']['monthly_day'] = intval( $recurring_monthly_day );
					} elseif ( 'by_weekdays' === $recurring_monthly_occurs_type ) {
						$recurring_monthly_week     = get_post_meta( $post_id, 'stm_recurring_monthly_week', true );
						$recurring_monthly_week_day = get_post_meta( $post_id, 'stm_recurring_monthly_week_day', true );

						$data['recurrence']['monthly_week']     = intval( $recurring_monthly_week );
						$data['recurrence']['monthly_week_day'] = intval( $recurring_monthly_week_day );
					}
					break;
				default:
					$repeat_interval = 1;
					break;
			}

			$data['type']                          = ( 'webinars' === $zoom_type ) ? StmZoomAPITypes::TYPE_WEBINAR_RECURRING : StmZoomAPITypes::TYPE_MEETING_RECURRING;
			$data['recurrence']['type']            = intval( $recurring_type );
			$data['recurrence']['repeat_interval'] = intval( $repeat_interval );

			$end_time_type = get_post_meta( $post_id, 'stm_recurring_end_time_type', true );

			if ( 'by_occurrences' === $end_time_type ) {
				$end_times = get_post_meta( $post_id, 'stm_recurring_end_times', true );

				$data['recurrence']['end_times'] = $end_times;
			} elseif ( 'by_date' === $end_time_type ) {
				$end_date_time = get_post_meta( $post_id, 'stm_recurring_end_date_time', true );

				$meeting_end                         = strtotime( 'today', ( ( $end_date_time ) / 1000 ) );
				$data['recurrence']['end_date_time'] = gmdate( 'Y-m-d\TH:i:s\Z', $meeting_end );
			}
		} elseif ( 'no_fixed_time' === $recurring_type ) {
			$data['type'] = ( 'webinars' === $zoom_type ) ? StmZoomAPITypes::TYPE_WEBINAR_NO_FIXED : StmZoomAPITypes::TYPE_MEETING_NO_FIXED;
		}

		return $data;
	}

	protected function syn_meeting_webinar_update_data( $new_post_id, $zoom_meeting ) {
		update_post_meta( $new_post_id, 'stm_recurring_enabled', true );

		if ( in_array( $zoom_meeting['type'], StmZoomAPITypes::TYPES_RECURRING, true ) && isset( $zoom_meeting['recurrence'] ) ) {
			update_post_meta( $new_post_id, 'stm_recurring_type', $zoom_meeting['recurrence']['type'] );
			switch ( $zoom_meeting['recurrence']['type'] ) {
				case StmZoomAPITypes::TYPE_RECURRENCE_DAILY:
					update_post_meta( $new_post_id, 'stm_recurring_daily_repeat_interval', $zoom_meeting['recurrence']['repeat_interval'] );
					break;
				case StmZoomAPITypes::TYPE_RECURRENCE_WEEKLY:
					update_post_meta( $new_post_id, 'stm_recurring_weekly_repeat_interval', $zoom_meeting['recurrence']['repeat_interval'] );
					$weekly_days        = explode( ',', $zoom_meeting['recurrence']['weekly_days'] );
					$weekly_days_encode = wp_json_encode( $weekly_days );
					update_post_meta( $new_post_id, 'stm_recurring_weekly_days', $weekly_days_encode );
					break;
				case StmZoomAPITypes::TYPE_RECURRENCE_MONTHLY:
					update_post_meta( $new_post_id, 'stm_recurring_monthly_repeat_interval', $zoom_meeting['recurrence']['repeat_interval'] );
					if ( $zoom_meeting['recurrence']['monthly_day'] ) {
						update_post_meta( $new_post_id, 'stm_recurring_monthly_occurs', 'by_day' );
						update_post_meta( $new_post_id, 'stm_recurring_monthly_day', $zoom_meeting['recurrence']['monthly_day'] );
					} elseif ( $zoom_meeting['recurrence']['monthly_week'] ) {
						update_post_meta( $new_post_id, 'stm_recurring_monthly_occurs', 'by_weekdays' );
						update_post_meta( $new_post_id, 'stm_recurring_monthly_week', $zoom_meeting['recurrence']['monthly_week'] );
						update_post_meta( $new_post_id, 'stm_recurring_monthly_week_day', $zoom_meeting['recurrence']['monthly_week_day'] );
					}
					break;
				default:
					break;
			}
			if ( isset( $zoom_meeting['recurrence']['end_date_time'] ) ) {
				update_post_meta( $new_post_id, 'stm_recurring_end_time_type', 'by_date' );
				update_post_meta( $new_post_id, 'stm_recurring_end_date_time', intval( strtotime( gmdate( 'Y-m-d 00:00:00', strtotime( $zoom_meeting['recurrence']['end_date_time'] ) ) ) * 1000 ) );
			} elseif ( isset( $zoom_meeting['recurrence']['end_times'] ) ) {
				update_post_meta( $new_post_id, 'stm_recurring_end_time_type', 'by_occurrences' );
				update_post_meta( $new_post_id, 'stm_recurring_end_times', $zoom_meeting['recurrence']['end_times'] );
			}
		} elseif ( in_array( $zoom_meeting['type'], StmZoomAPITypes::TYPES_NO_FIXED, true ) ) {
			update_post_meta( $new_post_id, 'stm_recurring_type', 'no_fixed_time' );
		}
	}

	public static function stm_approved_denied_countries() {

		$return = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['stm_multiselect_approved'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$approved = sanitize_text_field( $_POST['stm_multiselect_approved'] );
			$approved = json_decode( str_replace( array( '\\' ), '', $approved ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['stm_multiselect_denied'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$denied = sanitize_text_field( $_POST['stm_multiselect_denied'] );
			$denied = json_decode( str_replace( array( '\\' ), '', $denied ) );
		}

		if ( ! empty( $approved ) ) {
			$countries = $approved;
		} elseif ( ! empty( $denied ) ) {
			$countries = $denied;
		}

		if ( ! empty( $countries ) ) {
			$get_contries = array();

			foreach ( $countries as $item ) {
				$get_contries[] = $item->value;
			}

			$return['enable'] = true;
			$return['method'] = ! empty( $approved ) ? 'approve' : 'deny';

			if ( ! empty( $approved ) ) {
				$return['approved_list'] = $get_contries;
			} else {
				$return['denied_list'] = $get_contries;
			}
		}

		return $return;
	}

	public static function stm_get_countries_code() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$countries = json_decode( file_get_contents( STM_ZOOM_PATH . '/contry_list.json' ), true );

		if ( empty( $countries ) || ! count( $countries ) ) {
			return array();
		}

		$array = array();
		foreach ( $countries as $key => $country ) {
			$array[] = array(
				'label' => $country,
				'value' => $key,
			);
		}

		return $array;
	}

	public function stm_provider_column_title( $columns ) {
		$columns = array_slice( $columns, 0, 3, true ) +
			array( 'provider' => esc_html__( 'Provider', 'eroom-zoom-meetings-webinar' ) ) +
			array_slice( $columns, 3, count( $columns ) - 1, true );
		return $columns;
	}
	public function stm_provider_column( $column_key, $post_id ) {
		if ( 'provider' === $column_key ) {
			$provider = get_post_meta( $post_id, 'stm_select_gm_zoom', true );
			if ( 'zoom' === $provider || empty( $provider ) ) {
				echo '<i class="stm-zoom-icon" title="' . esc_attr__( 'Zoom', 'eroom-zoom-meetings-webinar' ) . '"></i>';
			} else {
				echo '<i class="stm-google-meet-icon" title="' . esc_attr__( 'Google Meet', 'eroom-zoom-meetings-webinar' ) . '"></i>';
			}
		}
	}
}
