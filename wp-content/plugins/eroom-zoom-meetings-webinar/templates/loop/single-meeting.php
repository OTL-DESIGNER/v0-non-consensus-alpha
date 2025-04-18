<?php
$_post_id   = get_the_ID();
$_post_type = get_post_type();
if ( 'product' === $_post_type ) {
	$_post_id = get_post_meta( $_post_id, '_meeting_id', true );
}
if ( ! empty( $_post_id ) ) {

	$host_id     = get_post_meta( $_post_id, 'stm_host', true );
	$timezone    = get_post_meta( $_post_id, 'stm_timezone', true );
	$_user_email = '';
	$first_name  = '';
	$last_name   = '';
	if ( empty( $timezone ) ) {
		$timezone = 'UTC';
	}

	$timezones = stm_zoom_get_timezone_options();
	if ( ! empty( $timezones[ $timezone ] ) ) {
		$timezone = $timezones[ $timezone ];
	}

	if ( ! empty( $host_id ) ) {
		$zoom_users = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$zoom_users[ $user['id'] ] = array(
					'email'      => $user['email'],
					'first_name' => $user['first_name'],
					'last_name'  => $user['last_name'],
				);
			}
		}
		if ( ! empty( $zoom_users[ $host_id ] ) ) {
			if ( ! empty( $zoom_users[ $host_id ]['email'] ) ) {
				$_user_email = $zoom_users[ $host_id ]['email'];
			}
			if ( ! empty( $zoom_users[ $host_id ]['first_name'] ) ) {
				$first_name = $zoom_users[ $host_id ]['first_name'];
			}
			if ( ! empty( $zoom_users[ $host_id ]['last_name'] ) ) {
				$last_name = $zoom_users[ $host_id ]['last_name'];
			}
		}
	} else {
		global $post;
		$meta_user   = get_user_by( 'id', $post->post_author );
		$_user_email = $meta_user->user_email;
		$first_name  = get_user_meta( $post->post_author, 'first_name', true );
		$last_name   = get_user_meta( $post->post_author, 'last_name', true );

	}

	?>
	<div class="stm_zoom_grid__item">
		<div class="single_meeting">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'large' ); ?>
					</a>
				</div>
			<?php endif; ?>
			<div class="info">
				<div class="title">
					<a href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
				</div>
				<div class="zoom_date">
					<?php
					$provider = get_post_meta( $_post_id, 'stm_select_gm_zoom', true );
					if ( empty( $provider ) || 'zoom' === $provider ) {
						$start_date = get_post_meta( $_post_id, 'stm_date', true );
						$start_time = get_post_meta( $_post_id, 'stm_time', true );
					} else {
						$start_date = get_post_meta( $_post_id, 'stm_start_date', true );
						$start_time = get_post_meta( $_post_id, 'stm_start_time', true );
					}

					if ( ! empty( $start_date ) ) {
						$meeting_start = (int) $start_date / 1000;

						if ( ! empty( $start_time ) ) {
							$time = explode( ':', $start_time );
							if ( is_array( $time ) && count( $time ) === 2 ) {
								// Modify the timestamp by adding hours and minutes
								$meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
							}
						}
						$date     = new DateTime();
						$date->setTimestamp( $meeting_start );
						$date->setTimezone( new DateTimeZone( 'UTC' ) ); // Adjust timezone as needed

						$formatted_date = $date->format( 'F j, Y g:i a' );

						echo esc_html( $formatted_date );
					}

					?>
					<?php
					$price = '';
					if ( 'product' === $_post_type && class_exists( 'WooCommerce' ) ) {
						global $product;
						$price = $product->get_price_html();
					}
					if ( ! empty( $price ) ) :
						?>
					<span class="price"><?php echo wp_kses_post( $price ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $host_id ) ) : ?>
					<div class="zoom_host">
						<div class="host_image">
							<?php echo get_avatar( $_user_email, 200 ); ?>
						</div>
						<div class="host_info">
							<div class="host_title">
								<?php if ( 'product' === $_post_type && ( empty( $provider ) || 'zoom' === $provider ) ) : ?>
									<a href="<?php echo esc_url( get_home_url( '/' ) . '/zoom-users/' . esc_attr( $host_id ) ); ?>">
								<?php endif; ?>

								<?php echo esc_html( $first_name . ' ' . $last_name ); ?>

								<?php if ( 'product' === $_post_type && ( empty( $provider ) || 'zoom' === $provider ) ) : ?>
									</a>
								<?php endif; ?>
							</div>
							<div class="host_timezone">
								<?php echo esc_html( $timezone ); ?>
							</div>
						</div>
					</div>
				<?php endif ?>
			</div>
		</div>
	</div>
<?php } ?>
