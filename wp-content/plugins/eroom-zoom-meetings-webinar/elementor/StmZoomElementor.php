<?php

namespace StmZoomElementor;

use StmZoomElementor\Widgets\StmZoomMeeting;
use StmZoomElementor\Widgets\StmZoomWebinar;
use StmZoomElementor\Widgets\StmZoomMeetingsGrid;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Main Plugin Class
 *
 * Register new elementor widget.
 *
 * @since 1.0.0
 */
class Plugin {


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		 $this->add_actions();
	}

	/**
	 * Add Actions
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function add_actions() {
		add_action( 'elementor/widgets/register', array( $this, 'on_widgets_registered' ) );
	}


	/**
	 * On Widgets Registered
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function on_widgets_registered() {
		$this->includes();
		$this->register_widget();
	}

	/**
	 * Includes
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function includes() {
		require STM_ZOOM_PATH . '/elementor/widgets/stm_zoom_meeting.php';
		require STM_ZOOM_PATH . '/elementor/widgets/stm_zoom_webinar.php';
		require STM_ZOOM_PATH . '/elementor/widgets/stm_zoom_meetings_grid.php';
	}

	/**
	 * Register Widget
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function register_widget() {
		\Elementor\Plugin::instance()->widgets_manager->register( new StmZoomMeeting() );
		\Elementor\Plugin::instance()->widgets_manager->register( new StmZoomWebinar() );
		\Elementor\Plugin::instance()->widgets_manager->register( new StmZoomMeetingsGrid() );
	}
}

new Plugin();
