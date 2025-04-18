<?php
add_action( 'admin_footer', 'stm_eroom_render_feature_request' );

function stm_eroom_render_feature_request() {
	echo '<a id="eroom-feature-request" href="https://stylemixthemes.cnflx.io/boards/eroom-zoom-meetings" target="_blank" style="display: none;">
		<img src="' . esc_url( STM_ZOOM_URL . 'assets/images/conflux/feature-request.svg' ) . '">
		<span>Create a roadmap with us:<br>Vote for next feature</span>
	</a>';
}
