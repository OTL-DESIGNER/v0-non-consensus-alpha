<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
if ( ! empty( $webinar_id ) ) {
    echo do_shortcode( '[stm_zoom_webinar post_id="' . $webinar_id . '"]' );
}