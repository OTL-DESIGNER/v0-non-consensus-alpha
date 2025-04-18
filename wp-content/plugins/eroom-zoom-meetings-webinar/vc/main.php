<?php
add_action( 'vc_after_init', 'stm_zoom_meeting' );

function stm_zoom_meeting()
{
    vc_map( array(
        'name' => esc_html__( 'Zoom Meeting', 'eroom-zoom-meetings-webinar' ),
        'base' => 'stm_zoom_meeting',
        'icon' => 'fas fa-video',
        'html_template' => STM_ZOOM_PATH . '/vc/templates/stm_zoom_meeting.php',
        'category' => array(
            esc_html__( 'Content', 'eroom-zoom-meetings-webinar' ),
        ),
        'params' => array(
            array(
                'type' => 'dropdown',
                'heading' => __( 'Meeting', 'eroom-zoom-meetings-webinar' ),
                'param_name' => 'meeting_id',
                'value' => array_flip( get_meetings() )
            )
        )
    ) );

    vc_map( array(
        'name' => esc_html__( 'Zoom Webinar', 'eroom-zoom-meetings-webinar' ),
        'base' => 'stm_zoom_webinar',
        'icon' => 'fas fa-video',
        'html_template' => STM_ZOOM_PATH . '/vc/templates/stm_zoom_webinar.php',
        'category' => array(
            esc_html__( 'Content', 'eroom-zoom-meetings-webinar' ),
        ),
        'params' => array(
            array(
                'type' => 'dropdown',
                'heading' => __( 'Webinar', 'eroom-zoom-meetings-webinar' ),
                'param_name' => 'webinar_id',
                'value' => array_flip( get_webinars() )
            )
        )
    ) );

    $grid_params = array(
        array(
            'type' => 'textfield',
            'heading' => __( 'Count of meetings', 'eroom-zoom-meetings-webinar' ),
            'param_name' => 'count',
            'value' => '3',
        ),
        array(
            'type' => 'dropdown',
            'heading' => __( 'Meetings per row', 'eroom-zoom-meetings-webinar' ),
            'param_name' => 'per_row',
            'value' => array(
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ),
            'std' => '3'
        )
    );

    if ( class_exists( 'StmZoomPro' ) ) {
        $grid_params[] =             array(
            'type' => 'dropdown',
            'heading' => __( 'Meeting type', 'eroom-zoom-meetings-webinar' ),
            'param_name' => 'post_type',
            'value' => array(
                esc_html__('Products', 'eroom-zoom-meetings-webinar') => 'product',
                esc_html__('Meetings', 'eroom-zoom-meetings-webinar') => 'stm-zoom',
                esc_html__('Webinars', 'eroom-zoom-meetings-webinar') => 'stm-zoom-webinar'
            ),
            'std' => 'stm-zoom'
        );
    }

    vc_map( array(
        'name' => esc_html__( 'Zoom Meetings/Webinars Grid', 'eroom-zoom-meetings-webinar' ),
        'base' => 'stm_zoom_meeting',
        'icon' => 'fas fa-video',
        'html_template' => STM_ZOOM_PATH . '/vc/templates/stm_zoom_meetings_grid.php',
        'category' => array(
            esc_html__( 'Content', 'eroom-zoom-meetings-webinar' ),
        ),
        'params' => $grid_params
    ) );
}

if( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_Stm_Zoom_Meeting extends WPBakeryShortCode
    {
    }
    class WPBakeryShortCode_Stm_Zoom_Webinar extends WPBakeryShortCode
    {
    }
    class WPBakeryShortCode_Stm_Zoom_Meetings_Grid extends WPBakeryShortCode
    {
    }
}