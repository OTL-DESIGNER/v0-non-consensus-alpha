<?php
/**
 * Migration UI template.
 *
 * @var array $args
 */
?>
<div class="eroom-migration-screen-wrapper" style="display: none;">
	<div class="eroom-migration-screen-content">

		<a class="eroom-migration-close eroom-migration-screen-state">
			<img src="<?php echo esc_url( STM_ZOOM_URL . '/assets/images/cancel.svg' ); ?>" alt="close window">
		</a>
		<div class="eroom-migration-screen-container">
			<h3><?php esc_html_e( 'Migration Wizard', 'eroom-zoom-meetings-webinar' ); ?></h3>
			<p><?php esc_html_e( 'Migrate from JWT to Server to Server Oauth in easy steps', 'eroom-zoom-meetings-webinar' ); ?></p>
			<div class="eroom-migrate-to-s2sOauth--message error-message" style="display: none">
				<?php esc_html_e( 'Please check your credentials', 'eroom-zoom-meetings-webinar' ); ?>
			</div>
			<div class="migration-form-intro">
				<?php
				echo '<p>';
				echo sprintf( '%1s<a href="https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/" target="_blank">%2s</a> %3s', esc_html__( 'Follow the documentation ', 'eroom-zoom-meetings-webinar' ), esc_html__( 'Here', 'eroom-zoom-meetings-webinar' ), esc_html__( 'on how to generate Server to Server Oauth Credentials.', 'eroom-zoom-meetings-webinar' ) );
				echo '</p>';
				?>
				<div class="migration-fields">
					<div class="intro-wrapper-migration">
						<div class="account-id">
							<label><?php esc_html_e( 'Server-to-Server OAuth Account ID', 'eroom-zoom-meetings-webinar' ); ?></label>
							<input type="text" name="eroom_account_id" class="eroom_account_id_input"/>
						</div>
						<div class="client-id">
							<label><?php esc_html_e( 'Server-to-Server OAuth Client ID', 'eroom-zoom-meetings-webinar' ); ?></label>
							<input type="text" name="eroom_client_id" class="eroom_client_id_input"/>
						</div>
						<div class="client-secret">
							<label><?php esc_html_e( 'Server-to-Server OAuth Client Secret', 'eroom-zoom-meetings-webinar' ); ?></label>
							<input type="text" name="eroom_client_secret" class="eroom_client_secret_input"/>
						</div>
					</div>
					<button class="button check-migration-oauth">
						<span
							class="ui-button-text"><?php esc_html_e( 'Check and Save Credentials', 'eroom-zoom-meetings-webinar' ); ?></span>
						<img src="<?php echo esc_url( STM_ZOOM_URL . '/assets/images/refresh-icon.svg' ); ?>" alt=""
							class="installing">
						<i class="fa fa-exclamation-triangle error_migration_icon" aria-hidden="true"></i>
						<i class="fa fa-check downloaded" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="migration-form-success" style="display: none">
				<i class="fa fa-check downloaded" aria-hidden="true"></i>
			</div>
		</div>
	</div>
</div>
