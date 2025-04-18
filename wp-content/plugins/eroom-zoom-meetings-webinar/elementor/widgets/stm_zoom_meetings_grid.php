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
class StmZoomMeetingsGrid extends Widget_Base {


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
		return 'stm_zoom_meetings_grid';
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
		return __( 'Zoom Meetings/Webinars Grid', 'eroom-zoom-meetings-webinar' );
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
		if ( class_exists( 'StmZoomPro' ) ) {
			$this->add_control(
				'post_type',
				array(
					'name'        => 'post_type',
					'label'       => __( 'Meeting type', 'eroom-zoom-meetings-webinar' ),
					'type'        => \Elementor\Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => array(
						'product'          => __( 'Products', 'eroom-zoom-meetings-webinar' ),
						'stm-zoom'         => __( 'Meetings', 'eroom-zoom-meetings-webinar' ),
						'stm-zoom-webinar' => __( 'Webinars', 'eroom-zoom-meetings-webinar' ),
					),
					'default'     => 'stm-zoom',
				)
			);
		}

		$this->add_control(
			'per_row',
			array(
				'name'        => 'per_row',
				'label'       => __( 'Meetings per row', 'eroom-zoom-meetings-webinar' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'default'     => '3',
			)
		);

		$this->add_control(
			'count',
			array(
				'name'        => 'count',
				'label'       => __( 'Count of meetings', 'eroom-zoom-meetings-webinar' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => 3,
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

		$count     = ! empty( $settings['count'] ) ? $settings['count'] : 3;
		$post_type = ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'stm-zoom';
		$per_row   = ! empty( $settings['per_row'] ) ? $settings['per_row'] : '3';

		echo do_shortcode( '[stm_zoom_conference_grid count="' . esc_attr( $count ) . '" post_type="' . esc_attr( $post_type ) . '" per_row="' . esc_attr( $per_row ) . '"]' );
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



