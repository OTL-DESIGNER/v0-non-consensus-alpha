<?php

class StmZoomAdminMenus {

	/**
	 * @return StmZoomAdminMenus constructor.
	 */
	public function __construct() {
		add_action(
			'admin_menu',
			function () {
				add_menu_page( 'eRoom', 'eRoom', 'manage_options', 'stm_zoom', 'admin_pages', 'dashicons-video-alt2', 40 );
				self::admin_submenu_pages();
			},
			100
		);

		add_action( 'admin_menu', array( $this, 'admin_submenu_pages_gopro' ), 10001 );

		if ( is_admin() ) {
			self::admin_settings_page();
			add_filter(
				'stm_wpcfto_autocomplete_stm_alternative_hosts',
				array(
					$this,
					'get_autocomplete_users_options',
				),
				100
			);
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 100 );

		add_action( 'admin_head', array( $this, 'admin_head' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( STM_ZOOM_FILE ), array( $this, 'plugin_action_links' ) );

		add_action( 'admin_head-edit.php', array( $this, 'admin_meetings_webinars_scripts' ) );
	}

	/**
	 * Get Users for Autocomplete
	 *
	 * @return array
	 */
	public static function get_autocomplete_users_options() {
		$users  = StmZoom::get_users_options();
		$result = array();
		foreach ( $users as $id => $user ) {
			$result[] = array(
				'id'        => $id,
				'title'     => $user,
				'post_type' => '',
			);
		}

		return $result;
	}

	/**
	 * Creating Submenu Pages under Zoom menu
	 */
	public static function admin_submenu_pages() {
		$pages = array(
			array(
				'slug'      => 'stm_zoom_users',
				'menu_slug' => 'stm_zoom_users',
				'label'     => esc_html__( 'Users', 'eroom-zoom-meetings-webinar' ),
			),
			array(
				'slug'      => 'stm_zoom_add_user',
				'menu_slug' => 'stm_zoom_add_user',
				'label'     => esc_html__( 'Add user', 'eroom-zoom-meetings-webinar' ),
				'hidden'    => true,
			),
			array(
				'slug'      => 'stm_zoom_reports',
				'menu_slug' => 'stm_zoom_reports',
				'label'     => esc_html__( 'Statistics', 'eroom-zoom-meetings-webinar' ),
			),
			array(
				'slug'      => 'stm_zoom_assign_host_id',
				'menu_slug' => 'stm_zoom_assign_host_id',
				'label'     => esc_html__( 'Assign host id', 'eroom-zoom-meetings-webinar' ),
				'hidden'    => true,
			),
		);

		// Add Webinars submenu
		add_submenu_page(
			'stm_zoom',
			esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
			esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
			'manage_options',
			'edit.php?post_type=stm-zoom-webinar',
			false
		);

		foreach ( $pages as $page ) {
			/* Create Submenu */
			add_submenu_page(
				isset( $page['hidden'] ) && $page['hidden'] ? null : 'stm_zoom',
				$page['label'],
				$page['label'],
				'manage_options',
				$page['menu_slug'],
				'admin_pages'
			);
		}

		/* Remove original Submenu */
		remove_submenu_page( 'stm_zoom', 'stm_zoom' );

		do_action( 'stm_zoom_admin_submenu_pages' );
	}

	public static function admin_submenu_pages_gopro() {
		if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
			add_submenu_page(
				'stm_zoom',
				esc_html__( 'Upgrade', 'eroom-zoom-meetings-webinar' ),
				'<span style="color: #adff2f;"><span style="font-size: 15px;text-align: left;" class="dashicons dashicons-star-filled stm_go_pro_menu"></span>' . esc_html__( 'Upgrade', 'eroom-zoom-meetings-webinar' ) . '</span>',
				'manage_options',
				'stm_zoom_go_pro',
				'admin_pages'
			);
		}
	}

	/**
	 * Creating Plugin Settings
	 */
	public static function admin_settings_page() {
		add_filter(
			'wpcfto_options_page_setup',
			function ( $setup ) {
				$fields = array(
					'tab_1'      => array(
						'name'   => esc_html__( 'Main settings', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'auth_account_id'    => array(
								'type'        => 'text',
								'group_title' => esc_html__( 'Server to Server Oauth Credentials', 'eroom-zoom-meetings-webinar' ),
								'label'       => esc_html__( 'Account ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'group'       => 'started',
							),
							'auth_client_id'     => array(
								'type'        => 'text',
								'label'       => esc_html__( 'Client ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'description' => sprintf( '%1s <a href="https://docs.stylemixthemes.com/eroom/getting-started/how-to-obtain-apis#server-to-server-oauth-credentials" target="_blank">%2s</a>  %3s ', esc_html__( 'Please follow this ', 'eroom-zoom-meetings-webinar' ), esc_html__( 'guide', 'eroom-zoom-meetings-webinar' ), esc_html__( ' to generate API values from Zoom App Marketplace using your Zoom account.', 'eroom-zoom-meetings-webinar' ) ),
							),
							'auth_client_secret' => array(
								'type'  => 'text',
								'label' => esc_html__( 'Client Secret', 'eroom-zoom-meetings-webinar' ),
								'value' => '',
								'group' => 'ended',
							),
							'sdk_key'            => array(
								'group_title' => esc_html__( 'Meeting SDK credentials', 'eroom-zoom-meetings-webinar' ),
								'group'       => 'started',
								'type'        => 'text',
								'label'       => esc_html__( 'Client ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'description' => sprintf( '%1s <a href="https://docs.stylemixthemes.com/eroom/getting-started/how-to-obtain-apis#meeting-sdk-credentials" target="_blank">%2s</a> ', esc_html__( 'To make Join in Browser option work please generate API following this', 'eroom-zoom-meetings-webinar' ), esc_html__( 'guide', 'eroom-zoom-meetings-webinar' ) ),
							),
							'sdk_secret'         => array(
								'type'  => 'text',
								'label' => esc_html__( 'Client Secret', 'eroom-zoom-meetings-webinar' ),
								'value' => '',
								'group' => 'ended',
							),
							'generate_password'  => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Generate password', 'eroom-zoom-meetings-webinar' ),
								'value'       => false,
								'description' => esc_html__( 'Auto-generation of the password for meeting/webinar if left empty', 'eroom-zoom-meetings-webinar' ),
							),
						),
					),
					'shortcodes' => array(
						'name'   => esc_html__( 'Shortcodes', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'sc_meeting'                  => array(
								'label'    => esc_html__( 'Single Meeting', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference post_id="{post_id}"]',
							),
							'sc_webinar'                  => array(
								'label'    => esc_html__( 'Single Webinar', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_webinar post_id="{post_id}"]',
							),
							'sc_meetings_grid'            => array(
								'label'    => esc_html__( 'Meetings Grid', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom" count="3" per_row="3"]',
							),
							'sc_webinar_grid'             => array(
								'label'    => esc_html__( 'Webinar Grid', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom-webinar" count="3" per_row="3"]',
							),
							'sc_recurring_meeting'        => array(
								'label'    => esc_html__( 'Recurring Meeting Grid (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" recurring="1" count="3" per_row="3"]',
							),
							'sc_product_grid'             => array(
								'label'    => esc_html__( 'Meeting Product Grid (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" count="3" per_row="3"]',
							),
							'sc_product_grid_by_category' => array(
								'label'    => esc_html__( 'Meeting Product Grid by category (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" category="{category_id}, {category_id}" count="3" per_row="3"]',
							),
							'sc_general_grid'             => array(
								'label'    => esc_html__( 'General Grid (stm-zoom, stm-zoom-webinar, product in a single grid)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom, stm-zoom-webinar, product" count="3" per_row="3"]',
							),
						),
					),
				);

				if ( defined( 'BOOKIT_VERSION' ) ) {
					$fields['tab_1']['fields']['bookit_integration'] = array(
						'type'        => 'checkbox',
						'label'       => esc_html__( 'Bookit Appointment Integration', 'eroom-zoom-meetings-webinar' ),
						'value'       => false,
						'description' => esc_html__( 'Meeting will be created when someone books an Appointment', 'eroom-zoom-meetings-webinar' ),
					);
				}

				$setup[] = array(
					'option_name' => 'stm_zoom_settings',
					'page'        => array(
						'page_title'  => 'Zoom Settings',
						'menu_title'  => 'Zoom Settings',
						'menu_slug'   => 'stm_zoom_settings',
						'icon'        => 'dashicons-video-alt2',
						'position'    => 40,
						'parent_slug' => 'stm_zoom',
					),
					'fields'      => apply_filters( 'stm_zoom_settings_fields', $fields ),
				);

				return $setup;
			},
			200
		);
	}

	/**
	 * Enqueue Admin Styles & Scripts
	 */
	public function admin_enqueue() {
		wp_enqueue_style( 'stm_zoom_admin', STM_ZOOM_URL . 'assets/css/admin/main.css', false, STM_ZOOM_VERSION );
		wp_enqueue_style( 'stm_conflux_admin', STM_ZOOM_URL . 'assets/css/admin/conflux.css', false, STM_ZOOM_VERSION );

		if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
			wp_enqueue_style( 'stm_zoom_admin_gopro', STM_ZOOM_URL . 'assets/css/admin/gopro.css', false, STM_ZOOM_VERSION );

			// tiny slider Admin Popup Pro features
			wp_enqueue_style( 'stm_zoom_admin_slider', STM_ZOOM_URL . 'assets/css/admin/admin-style.css', false, STM_ZOOM_VERSION );
			wp_enqueue_script( 'stm_zoom_admin_slider', STM_ZOOM_URL . 'assets/js/admin/admin-script.js', array(), STM_ZOOM_VERSION, false );
		}

		wp_enqueue_script( 'stm_zoom_admin', STM_ZOOM_URL . 'assets/js/admin/main.js', array( 'jquery' ), STM_ZOOM_VERSION, false );
	}

	/**
	 * Define WP Admin Ajax URL
	 */
	public function admin_head() { ?>
		<script type="text/javascript">
			var stm_zoom_ajaxurl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
		</script>
		<?php
	}

	/**
	 * Add Custom Links to Plugins page
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=stm_zoom_settings' ), esc_html__( 'Settings', 'eroom-zoom-meetings-webinar' ) );
		array_unshift( $links, $settings_link );

		if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
			$links['get_pro'] = sprintf( '<a href="%1$s" target="_blank" class="eroom-get-pro">%2$s</a>', esc_url( 'https://stylemixthemes.com/zoom-meetings-webinar-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020' ), esc_html__( 'Upgrade to Pro', 'eroom-zoom-meetings-webinar' ) );
		}

		return $links;
	}

	/**
	 * Add Meetings & Webinars Synchronize Scripts
	 */
	public function admin_meetings_webinars_scripts() {
		global $current_screen;

		if ( ! in_array( $current_screen->post_type, array( 'stm-zoom', 'stm-zoom-webinar' ), true ) ) {
			return;
		}

		wp_enqueue_script( 'stm_zoom_admin_meetings_webinars', STM_ZOOM_URL . 'assets/js/admin/meetings_webinars.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
		wp_localize_script(
			'stm_zoom_admin_meetings_webinars',
			'zoom_sync',
			array(
				'nonce' => wp_create_nonce( 'zoom-sync-nonce' ),
			)
		);
	}
}
