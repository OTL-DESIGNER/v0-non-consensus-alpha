<div class="stm_zoom_reports">

	<div class="reports-wr">
		<h1><?php esc_html_e( 'Statistics', 'eroom-zoom-meetings-webinar' ); ?></h1>
		<div class="reports-filter-wr">
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php?page=stm_zoom_reports' ) ); ?>">
				<input type="hidden" name="page" value="stm_zoom_reports">
				<?php
				$years        = array();
				$current_year = (int) gmdate( 'Y' );
				$offset       = 10;
				for ( $i = $current_year; $i > $current_year - $offset; $i-- ) {
					$years[] = $i;
				}
				$selected_year = isset( $_GET['year'] ) ? (int) $_GET['year'] : $current_year;// phpcs:ignore
				?>
				<select name="year" id="report-filter-year">
					<?php foreach ( $years as $_year ) : ?>
						<option value="<?php echo esc_attr( $_year ); ?>"
							<?php
							if ( $_year === $selected_year ) {
								echo 'selected';
							}
							?>
						><?php echo esc_html( $_year ); ?></option>
					<?php endforeach; ?>
				</select>

				<?php
				$months         = array(
					esc_html__( 'All months', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'January', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'February', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'March', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'April', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'May', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'June', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'July', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'August', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'September', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'October', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'November', 'eroom-zoom-meetings-webinar' ),
					esc_html__( 'December', 'eroom-zoom-meetings-webinar' ),
				);
				$current_month  = (int) gmdate( 'n' );
				$selected_month = isset( $_GET['month'] ) ? (int) $_GET['month'] : 0;// phpcs:ignore
				?>
				<select name="month" id="report-filter-month">
					<?php foreach ( $months as $k => $month ) : ?>
						<?php
						if ( $k > $current_month && $current_year === $selected_year ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $k ); ?>"
							<?php
							if ( $k === $selected_month ) {
								echo 'selected';
							}
							?>
						><?php echo esc_html( $month ); ?></option>
					<?php endforeach; ?>
				</select>

				<button type="submit"
					class="button button-primary"><?php esc_html_e( 'Show', 'eroom-zoom-meetings-webinar' ); ?></button>
			</form>
		</div>

		<?php
		$settings = get_option( 'stm_zoom_settings', array() );

		$auth_account_id    = ! empty( $settings['auth_account_id'] ) ? $settings['auth_account_id'] : '';
		$auth_client_id     = ! empty( $settings['auth_client_id'] ) ? $settings['auth_client_id'] : '';
		$auth_client_secret = ! empty( $settings['auth_client_secret'] ) ? $settings['auth_client_secret'] : '';
		$month              = sprintf( '%02d', $selected_month );


		$zoom = new \Zoom\Endpoint\Reports();

		if ( '0' === $month ) {
			$dates = array();
			for ( $i = 1;$i <= 12;$i++ ) {
				$query = array(
					'year'  => $selected_year,
					'month' => $i,
				);
				$temp  = $zoom->dailyReports( $query );
				if ( ! empty( $temp['dates'] ) ) {
					$dates = array_merge( $dates, $temp['dates'] );
				}
			}
			$reports['dates'] = $dates;
		} else {
			$query   = array(
				'year'  => $selected_year,
				'month' => $month,
			);
			$reports = $zoom->dailyReports( $query );
		}
		?>
		<?php if ( ! empty( $reports ) && ! empty( $reports['message'] ) ) : ?>
			<div class="stm_zoom_nonce error">
				<p>
					<?php
					if ( array_key_exists( 'code', $reports ) && 4700 === $reports['code'] ) {
						echo sprintf( '%1s <a href="https://zoom.us/pricing" target="_blank">%2s</a>', esc_html__( 'To view the following reports and other advanced features, upgrade to', 'eroom-zoom-meetings-webinar' ), esc_html__( 'Zoom Pro plan.', 'eroom-zoom-meetings-webinar' ) );
					} else {
						echo esc_html( $reports['message'] );
					}
					?>
				</p>
			</div>
		<?php endif; ?>


		<?php if ( ! empty( $reports ) && ! empty( $reports['dates'] ) ) : ?>
			<div class="reports-table-wr">
			<table class="">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'eroom-zoom-meetings-webinar' ); ?></th>
						<th><?php esc_html_e( 'Meetings', 'eroom-zoom-meetings-webinar' ); ?></th>
						<th><?php esc_html_e( 'Provider', 'eroom-zoom-meetings-webinar' ); ?></th>
						<th><?php esc_html_e( 'Participants', 'eroom-zoom-meetings-webinar' ); ?></th>
						<th><?php esc_html_e( 'New Users', 'eroom-zoom-meetings-webinar' ); ?></th>
						<th><?php esc_html_e( 'Meeting minutes', 'eroom-zoom-meetings-webinar' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ( $reports['dates'] as $date ) : ?>
						<tr>
							<td>
								<a style="display: none" href="#"><i class="report-chevron-open"></i></a>
								<i class="report-calendar-icon"></i>
								<span><?php echo esc_html( $date['date'] ); ?></span>
							</td>
							<td>
								<i class="report-meeting-icon"></i>
								<?php if ( empty( (int) $date['meetings'] ) ) : ?>
									<span class="report-empty-field"><?php echo esc_html( $date['meetings'] ); ?></span>
								<?php else : ?>
									<span><?php echo esc_html( $date['meetings'] ); ?></span>
								<?php endif ?>
							</td>
							<td>
								<?php if ( empty( $date['type'] ) ) : ?>
									<i class="report-zoom-icon" title="Zoom"></i>
								<?php else : ?>
									<i class="report-gm-icon" title="Google meet"></i>
								<?php endif ?>
							</td>
							<td>
								<i class="report-participants-icon"></i>
								<?php if ( empty( (int) $date['participants'] ) ) : ?>
									<span class="report-empty-field"><?php echo esc_html( $date['participants'] ); ?></span>
								<?php else : ?>
									<span><?php echo esc_html( $date['participants'] ); ?></span>
								<?php endif ?>
							</td>
							<td>
								<?php if ( empty( (int) $date['new_users'] ) ) : ?>
									<span class="report-empty-field"><?php echo esc_html( $date['new_users'] ); ?></span>
								<?php else : ?>
									<?php echo esc_html( $date['new_users'] ); ?>
								<?php endif ?>
							</td>
							<td>
								<?php if ( empty( (int) $date['meeting_minutes'] ) ) : ?>
									<span class="report-empty-field"><?php echo esc_html( $date['meeting_minutes'] ); ?> min</span>
								<?php else : ?>
									<?php echo esc_html( $date['meeting_minutes'] ); ?> min
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
	(function($) {
		$(document).ready(function () {
			$('.reports-table-wr a').on('click', function(event){
				event.preventDefault();
			})
		});
	})(jQuery)
</script>
