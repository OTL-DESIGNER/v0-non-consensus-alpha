<?php
define( 'STM_FREEMIUS_CHECKOUT_LINK', 'https://checkout.freemius.com/mode/dialog/plugin/' );
define( 'STM_FREEMIUS_CHECKOUT_UTM_SOURCE', 'utm_source=wpadmin&utm_medium=buynow&utm_campaign=eroom-plugin' );
define( 'STM_FREEMIUS_PLUGIN_INFO_URL', 'https://stylemixthemes.com/api/freemius/eroom-zoom-meetings-webinar-pro.json' );

function get_freemius_info() {
	$response = wp_remote_get( STM_FREEMIUS_PLUGIN_INFO_URL );
	$body     = wp_remote_retrieve_body( $response );
	$body     = json_decode( $body );

	if ( empty( $body ) ) {
		return '';
	}

	$freemius_info = array();

	/**
	 * Set to Array Premium Plan's Prices
	 */
	function set_premium_plan_prices( $plans, $plugin_id ) {
		$plan_info = array();

		$plan_data = array(
			'1'  => array(
				'text'      => __( 'Single Site', 'eroom-zoom-meetings-webinar' ),
				'classname' => '',
				'type'      => '',
			),
			'5'  => array(
				'classname' => 'stm_plan--popular',
				'text'      => __( 'Up to 5 Sites', 'eroom-zoom-meetings-webinar' ),
				'type'      => __( 'Most Popular', 'eroom-zoom-meetings-webinar' ),
			),
			'25' => array(
				'classname' => 'stm_plan--developer',
				'text'      => __( 'Up to 25 Sites', 'eroom-zoom-meetings-webinar' ),
				'type'      => __( 'Developer Oriented', 'eroom-zoom-meetings-webinar' ),
			),
		);

		foreach ( $plans as $plan ) {
			if ( 'premium' === $plan->name ) {
				if ( isset( $plan->pricing ) ) {
					foreach ( $plan->pricing as $pricing ) {
						$plan_info[ 'licenses_' . $pricing->licenses ]      = $pricing;
						$plan_info[ 'licenses_' . $pricing->licenses ]->url = STM_FREEMIUS_CHECKOUT_LINK . "{$plugin_id}/plan/{$pricing->plan_id}/licenses/{$pricing->licenses}/";

						if ( ! isset( $plan_data[ $pricing->licenses ] ) ) {
							$plan_data[ $pricing->licenses ] = array(
								'text'      => esc_html__( "Up to {$pricing->licenses} Sites", 'eroom-zoom-meetings-webinar' ), // phpcs:ignore
								'classname' => '',
								'type'      => '',
							);
						}
						$plan_info[ 'licenses_' . $pricing->licenses ]->data = $plan_data[ $pricing->licenses ];
					}
				}
				break;
			}
		}

		return array_reverse( $plan_info );
	}

	/**
	 * Set to Array Latest Plugin's Info
	 */
	function set_latest_info( $latest ) {
		$latest_info['version']           = $latest->version;
		$latest_info['tested_up_to']      = $latest->tested_up_to_version;
		$latest_info['created']           = date( "M j, Y", strtotime( $latest->created ) ); // phpcs:ignore
		$latest_info['last_update']       = date( "M j, Y", strtotime( $latest->updated ) ); // phpcs:ignore
		$latest_info['wordpress_version'] = $latest->requires_platform_version;

		return $latest_info;
	}

	if ( isset( $body->plans ) && ! empty( $body->plans ) ) {
		$freemius_info['plan'] = set_premium_plan_prices( $body->plans, $body->id );
	}

	if ( isset( $body->latest ) && ! empty( $body->latest ) ) {
		$freemius_info['latest'] = set_latest_info( $body->latest );
	}

	if ( isset( $body->info ) && ! empty( $body->info ) ) {
		$freemius_info['info'] = $body->info;
	}

	return $freemius_info;
}

$freemius_info = get_freemius_info();
$start_date    = new DateTime( '14th November 2024' );
$deadline      = new DateTime( '7th December 2024' );
$current_time  = time();
$is_promotion  = $current_time >= $start_date->format( 'U' ) && $current_time < $deadline->format( 'U' );

if ( $is_promotion ) {
	$freemius_info['plan']['licenses_5000']->annual_price   = 199.99;
	$freemius_info['plan']['licenses_5000']->lifetime_price = 399.99;
	$freemius_info['plan']['licenses_5']->annual_price      = 99.99;
	$freemius_info['plan']['licenses_5']->lifetime_price    = 199.99;
	$freemius_info['plan']['licenses_1']->annual_price      = 39.99;
	$freemius_info['plan']['licenses_1']->lifetime_price    = 129.99;
}

?>
<div class="eroom_go_pro">
	<section class="stm_go_pro">
		<div class="container">
			<div class="stm_go_pro_plugin">
				<h2 class="stm_go_pro_plugin__title">
					<?php esc_html_e( 'eRoom - WordPress and Zoom/Google Meet Integration', 'eroom-zoom-meetings-webinar' ); ?>
				</h2>
				<p class="stm_go_pro_plugin__content">
					<?php if ( isset( $freemius_info['info'] ) ) : ?>
						<?php if ( isset( $freemius_info['info']->short_description ) ) : ?>
							<?php echo esc_html( nl2br( $freemius_info['info']->short_description ) ); ?>
						<?php endif; ?>
						<?php if ( $freemius_info['info']->url ) : ?>
							<a href="<?php echo esc_attr( $freemius_info['info']->url . '?utm_source=wpadmin&utm_medium=gopro&utm_campaign=2021' ); ?>">
								<?php esc_html_e( 'Learn more.', 'eroom-zoom-meetings-webinar' ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
				</p>
			</div>
			<?php if ( $is_promotion ) : ?>
				<div class="stm-discount"><a href="https://stylemixthemes.com/deal/?utm_source=wpadmin&utm_medium=gopro&utm_campaign=bfcampaign24" target="_blank"></a></div>
			<?php endif; ?>

			<?php if ( isset( $freemius_info['plan'] ) ) : ?>
				<h2><?php esc_html_e( 'Pricing', 'eroom-zoom-meetings-webinar' ); ?></h2>
				<div class="stm-type-pricing">
					<div class="left active"><?php esc_html_e( 'Annual', 'eroom-zoom-meetings-webinar' ); ?></div>
					<div class="stm-type-pricing__switch">
						<input type="checkbox" id="GoProStmTypePricing">
						<label for="GoProStmTypePricing"></label>
					</div>
					<div class="right "><?php esc_html_e( 'Lifetime', 'eroom-zoom-meetings-webinar' ); ?></div>
				</div>
				<div class="row">
					<?php foreach ( $freemius_info['plan'] as $plan ) : ?>
						<div class="col-md-4">
							<div class="stm_plan <?php echo esc_attr( $plan->data['classname'] ); ?>">
								<?php if ( ! empty( $plan->data['type'] ) ) : ?>
									<div class="stm_plan__type">
										<?php echo esc_html( $plan->data['type'] ); ?>
									</div>
								<?php endif; ?>
								<div class="stm_price">
									<?php
									if ( $is_promotion ) :
										?>
										<sup>$</sup>
										<span class="stm_price__value"
											data-price-annual="<?php echo esc_attr( number_format( $plan->annual_price * 0.50, 2, '.', '' ) ); ?>"
											data-price-lifetime="<?php echo esc_attr( number_format( $plan->lifetime_price * 0.50, 2, '.', '' ) ); ?>"
											data-price-old-annual="<?php echo esc_attr( $plan->annual_price ); ?>"
											data-price-old-lifetime="<?php echo esc_attr( $plan->lifetime_price ); ?>"
										>
											<?php echo esc_html( number_format( $plan->annual_price * 0.50, 2, '.', '' ) ); ?>
										</span>
										<div class="discount">
											<p>$</p>
											<span class="stm_price__value" style="font-size: 20px;"
												data-price-annual="<?php echo esc_attr( $plan->annual_price ); ?>"
												data-price-lifetime="<?php echo esc_attr( $plan->lifetime_price ); ?>">
												<?php echo esc_html( $plan->annual_price ); ?>
											</span>
										</div>
										<small style="float: left; width: 100%; text-align: center;">/<?php esc_html_e( 'per year', 'eroom-zoom-meetings-webinar' ); ?></small>
									<?php else : ?>
										<sup>$</sup>
										<span class="stm_price__value" data-price-annual="<?php echo esc_attr( $plan->annual_price ); ?>" data-price-lifetime="<?php echo esc_attr( $plan->lifetime_price ); ?>">
											<?php echo esc_html( $plan->annual_price ); ?>
										</span>
										<small>/<?php esc_html_e( 'per year', 'eroom-zoom-meetings-webinar' ); ?></small>
									<?php endif; ?>

								</div>
								<p class="stm_plan__title"><?php echo esc_html( $plan->data['text'] ); ?></p>
								<?php
									$get_now_link = isset( $freemius_info['info']->url ) ? $freemius_info['info']->url . '?' . STM_FREEMIUS_CHECKOUT_UTM_SOURCE . '&licenses=' . $plan->licenses . '&billing_cycle=annual' : $plan->url;
									$data_url     = isset( $freemius_info['info']->url ) ? $freemius_info['info']->url . '?' . STM_FREEMIUS_CHECKOUT_UTM_SOURCE . '&licenses=' . $plan->licenses : $plan->url;
								?>
								<a href="<?php echo esc_url( $get_now_link ); ?>" class="stm_plan__btn stm_plan__btn--buy" data-checkout-url="<?php echo esc_attr( $data_url ); ?>" target="_blank">
									<?php esc_html_e( 'Get now', 'eroom-zoom-meetings-webinar' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<p class="stm_terms_content">
				<?php esc_html_e( 'You get', 'eroom-zoom-meetings-webinar' ); ?>
				<a href="https://stylemixthemes.com/subscription-policy/">
					<span class="stm_terms_content_support" data-support-lifetime="<?php esc_attr_e( 'Lifetime', 'eroom-zoom-meetings-webinar' ); ?>" data-support-annual="<?php esc_attr_e( '1 year', 'eroom-zoom-meetings-webinar' ); ?>">
						<?php esc_html_e( '1 year', 'eroom-zoom-meetings-webinar' ); ?>
					</span>
					<?php esc_html_e( 'updates and support', 'eroom-zoom-meetings-webinar' ); ?>
				</a>
				<?php esc_html_e( 'from the date of purchase. We offer 30 days Money Back Guarantee based on', 'eroom-zoom-meetings-webinar' ); ?>
				<a href="https://stylemixthemes.com/subscription-policy/">
					<?php esc_html_e( 'Refund Policy.', 'eroom-zoom-meetings-webinar' ); ?>
				</a>
			</p>

			<?php if ( ! empty( $freemius_info['latest'] ) ) : ?>
				<ul class="stm_last_changelog_info">
					<li>
						<span class="stm_last_changelog_info__label">
							<?php esc_html_e( 'Version:', 'eroom-zoom-meetings-webinar' ); ?>
						</span>
						<span class="stm_last_changelog_info__value">
							<?php echo esc_html( $freemius_info['latest']['version'] ); ?>
							<a href="https://docs.stylemixthemes.com/eroom/changelog/">
								<?php esc_html_e( 'View Changelog', 'eroom-zoom-meetings-webinar' ); ?>
							</a>
						</span>
					</li>
					<li>
						<span class="stm_last_changelog_info__label">
							<?php esc_html_e( 'Last Update:', 'eroom-zoom-meetings-webinar' ); ?>
						</span>
						<span class="stm_last_changelog_info__value">
							<?php echo esc_html( $freemius_info['latest']['created'] ); ?>
						</span>
					</li>
					<li>
						<span class="stm_last_changelog_info__label">
							<?php esc_html_e( 'Wordpress Version:', 'eroom-zoom-meetings-webinar' ); //phpcs:ignore ?>
						</span>
						<span class="stm_last_changelog_info__value">
							<?php echo esc_html( $freemius_info['latest']['wordpress_version'] ); ?> or higher
						</span>
					</li>
					<li>
						<span class="stm_last_changelog_info__label">
							<?php esc_html_e( 'Tested up to:', 'eroom-zoom-meetings-webinar' ); ?>
						</span>
						<span class="stm_last_changelog_info__value">
							<?php echo defined( 'EROOM_WP_TESTED_UP' ) ? esc_html( EROOM_WP_TESTED_UP ) : esc_html( $freemius_info['latest']['tested_up_to'] ); ?>
						</span>
					</li>
				</ul>
			<?php endif; ?>
		</div>
	</section>
</div>

<script>
	jQuery(document).ready(function ($) {
		$('#GoProStmTypePricing').on('change', function () {

			let parent = $(this).closest('.stm-type-pricing');

			let left = parent.find('.left'); //Annual
			let right = parent.find('.right'); //Lifetime
			let stm_price = $('.stm_price small');

			left.toggleClass('active', !this.checked);
			right.toggleClass('active', this.checked);

			stm_price.toggleClass('hidden', this.checked);

			let typePrice = 'annual';

			if (this.checked) typePrice = 'lifetime';

			let support = $('.stm_terms_content_support');
			support.text(support.attr('data-support-' + typePrice));

			$('.stm_plan__btn--buy').each(function () {
				let $this = $(this)
				let checkoutUrl = $this.attr('data-checkout-url');
				$this.attr('href', checkoutUrl + '&billing_cycle=' + typePrice);
			})

			$('.stm_price__value').each(function () {
				let $this = $(this);
				$this.text($this.attr('data-price-' + typePrice));
			})

		});

	});
</script>
