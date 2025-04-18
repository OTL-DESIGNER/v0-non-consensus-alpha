(function ($) {
	$(document).ready(function () {
		$('.eroom-migration-screen-container .check-migration-oauth').on('click', function (e) {
			$('.check-migration-oauth .installing').css('display', 'block');
			e.preventDefault();
			var account_id_val = $(".intro-wrapper-migration .eroom_account_id_input").val();
			var client_id_val = $(".intro-wrapper-migration .eroom_client_id_input").val();
			var client_secret_val = $(".intro-wrapper-migration .eroom_client_secret_input").val();
			if (account_id_val.length === 0 || client_id_val.length === 0 || client_secret_val.length === 0) {
				$('.check-migration-oauth .installing').css('display', 'none');
				$('.eroom-migrate-to-s2sOauth--message').css('display', 'block');
				$(".eroom-migrate-to-s2sOauth--message").html('Some API credentials are missing. Please double-check your credentials!');
				$('.check-migration-oauth').css('border-color', '#d63638');
				$('.check-migration-oauth').css('color', '#d63638');
				$('.check-migration-oauth').css('box-shadow', '0 0 0 1px #d63638');
				$('.error_migration_icon').css('display', 'block');
			} else {
				$('.eroom-migrate-to-s2sOauth--message').css('display', 'none');
				$('.check-migration-oauth').css('border-color', '#2271b1');
				$('.check-migration-oauth').css('color', '#2271b1');
				$('.check-migration-oauth').css('box-shadow', '0 0 0 1px #2271b1');
				$('.error_migration_icon').css('display', 'none');
				$('.check-migration-oauth .installing').css('display', 'block');
				jqXHR = $.ajax({
					url: stm_zoom_migration_demo_ajax_variable.url,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'stm_zoom_migration_action',
						nonce: stm_zoom_migration_demo_ajax_variable.nonce,
						accountID: account_id_val,
						clientID: client_id_val,
						clientSecret: client_secret_val,
					},
					success(response) {
						$('.check-migration-oauth .installing').css('display', 'none');
						$('.check-migration-oauth .error_migration_icon').css('display', 'none');
						$('.eroom-migrate-to-s2sOauth--message').css('display', 'none');
						$('.check-migration-oauth').css('border-color', 'green');
						$('.check-migration-oauth').css('color', 'green');
						$('.check-migration-oauth').css('box-shadow', '0 0 0 1px green');
						$('.check-migration-oauth .downloaded').css('display', 'block');
						$('.migration-form-success').css('display', 'block');
						$(".migration-form-success").html(response.data.message);
					},
					error(xhr, ajaxOptions, thrownError) {
						$('.check-migration-oauth img.installing').css('display', 'none');
						$('.check-migration-oauth').css('border-color', '#d63638');
						$('.check-migration-oauth').css('color', '#d63638');
						$('.check-migration-oauth').css('box-shadow', '0 0 0 1px #d63638');
						$('.eroom-migrate-to-s2sOauth--message').css('display', 'block');
						$('.error_migration_icon').css('display', 'block');
						$(".eroom-migrate-to-s2sOauth--message").html(xhr.responseJSON.data.message);
					}
				});
			}
		});
		$('.eroom-migration-screen-state').on('click', function () {
			$('.eroom-migration-screen-wrapper').css('display', 'none');
			$('html').css('overflow-y', 'scroll');
		});
		$('.eroom-migration-wizard').on('click', function () {
			$('.eroom-migration-screen-wrapper').css('display', 'block');
		});
		$('body').on('click', '.eroom-migration-wizard', function () {
			$('.eroom-migration-screen-wrapper').css('display', 'block');
		});
	})

})(jQuery);
