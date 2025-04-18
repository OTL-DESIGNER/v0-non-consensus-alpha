<div class="zoom_popup">
	<div class="zoom_close"></div>
	<div class="stm-zoom-slider-pro">
		<?php if ( ! defined( 'STM_LMS_PRO_VERSION' ) ) : ?>
			<div class="stm-zoom-slider-pro__item">
				<div class="popup_title">
					<h2>Meetings via Google Meet</h2>
				</div>
				<div class="popup_subtitle">
					Integrate with Google Meet and create a streamlined virtual meeting experience. With this add-on, workflow processes are simple: teams can work together effectively.
				</div>
				<div class="popup_image">
					<img class="google-meet" src="<?php echo esc_url( STM_ZOOM_URL . 'assets/images/google-meet.jpg' ); ?>" />
				</div>
			</div>
		<?php endif ?>
		<div class="stm-zoom-slider-pro__item">
			<div class="popup_title">
				<h2>Purchasable Meetings</h2>
			</div>
			<div class="popup_subtitle">
				No extra tools are required, everything is at your hand. Easily sell your meetings online on the website.
			</div>
			<div class="popup_image">
				<img src="<?php echo esc_url( STM_ZOOM_URL . 'assets/images/popup_image.png' ); ?>" />
			</div>
		</div>
		<div class="stm-zoom-slider-pro__item">
			<div class="popup_title">
				<h2>Recurring Meetings</h2>
			</div>
			<div class="popup_subtitle">
				Schedule Zoom meetings to recur on a regular basis. Create just one meeting and repeat it at a specified time.
			</div>
			<div class="popup_image">
				<img src="<?php echo esc_url( STM_ZOOM_URL . 'assets/images/popup_image_recurring.png' ); ?>" />
			</div>
		</div>
	</div>

	<div class="popup_footer">
		<div class="text">
			<a href="https://stylemixthemes.com/zoom-meetings-webinar-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020" target="_blank">Get eRoom PRO</a> today and stay connected anywhere at any time!
		</div>
		<a href="https://stylemixthemes.com/zoom-meetings-webinar-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020" class="pro_button" target="_blank">
			More Details
		</a>
	</div>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
		tns({
			container: '.stm-zoom-slider-pro',
			items: 1,
			loop: true,
			controls: false,
			autoplay: true,
			autoplayButtonOutput: false,
			mouseDrag: true,
			navPosition: 'top',
		});
	});
</script>
