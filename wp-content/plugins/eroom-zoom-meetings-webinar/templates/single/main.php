<?php
global $post;
if ( 'stm-zoom' === $post->post_type ) {
	$shortcode = '[stm_zoom_conference post_id="' . get_the_ID() . '" hide_content_before_start=""]';
} elseif ( 'stm-zoom-webinar' === $post->post_type ) {
	$shortcode = '[stm_zoom_webinar post_id="' . get_the_ID() . '" hide_content_before_start=""]';
}

get_header();
echo do_shortcode( apply_filters( 'stm_zoom_single_zoom_template_shortcode', $shortcode, get_the_ID() ) );
get_footer();
