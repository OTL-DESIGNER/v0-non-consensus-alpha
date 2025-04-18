<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
if ( empty( $count ) ) {
    $count = 3;
}
if ( empty( $per_row ) ) {
    $per_row = '3';
}
if ( empty( $post_type ) ) {
    $post_type = 'stm-zoom';
}
echo do_shortcode( '[stm_zoom_conference_grid count="' . esc_attr( $count ) . '" post_type="' . esc_attr( $post_type ) . '" per_row="' . esc_attr( $per_row ) . '"]' );