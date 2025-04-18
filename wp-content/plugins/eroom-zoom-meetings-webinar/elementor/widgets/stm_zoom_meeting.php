<?php

namespace StmZoomElementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Base_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class StmZoomMeeting extends Widget_Base {


	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'stm_zoom_meeting';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Zoom Meeting', 'eroom-zoom-meetings-webinar' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fas fa-video';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'theme-elements' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'eroom-zoom-meetings-webinar' ),
			)
		);

		$this->add_control(
			'meeting_id',
			array(
				'name'        => 'meeting_id',
				'label'       => __( 'Meeting', 'eroom-zoom-meetings-webinar' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => get_meetings(),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! empty( $settings['meeting_id'] ) ) {
			echo do_shortcode( '[stm_zoom_conference post_id="' . $settings['meeting_id'] . '"]' );
		}
	}

	 /**
	  * Render the widget output in the editor.
	  *
	  * Written as a Backbone JavaScript template and used to generate the live preview.
	  *
	  * @since 1.0.0
	  *
	  * @access protected
	  */
	protected function content_template() {

	}
}



