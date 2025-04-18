<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract( $atts );
if ( ! empty( $meeting_id ) ) {
    echo do_shortcode( '[stm_zoom_conference post_id="' . $meeting_id . '"]' );
}