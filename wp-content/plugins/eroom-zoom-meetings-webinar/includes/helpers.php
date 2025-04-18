<?php
/**
 * @return array of timezones
 */
function stm_zoom_get_timezone_options() {
	$time_zone = array(
		'Pacific/Midway'                 => ' Midway Island, Samoa',
		'Pacific/Pago_Pago'              => ' Pago Pago',
		'Pacific/Honolulu'               => ' Hawaii',
		'America/Anchorage'              => ' Alaska',
		'America/Juneau'                 => ' Juneau',
		'America/Vancouver'              => ' Vancouver',
		'America/Los_Angeles'            => ' Pacific Time (US and Canada)',
		'America/Tijuana'                => ' Tijuana',
		'America/Phoenix'                => ' Arizona',
		'America/Mazatlan'               => ' Mazatlan',
		'America/Chihuahua'              => ' Chihuahua',
		'America/Edmonton'               => ' Edmonton',
		'America/Denver'                 => ' Mountain Time (US and Canada)',
		'America/Regina'                 => ' Saskatchewan',
		'America/Guatemala'              => ' Guatemala',
		'America/Mexico_City'            => ' Mexico City',
		'America/El_Salvador'            => ' El Salvador',
		'America/Managua'                => ' Managua',
		'America/Costa_Rica'             => ' Costa Rica',
		'America/Tegucigalpa'            => ' Tegucigalpa',
		'America/Monterrey'              => ' Monterrey',
		'America/Winnipeg'               => ' Winnipeg',
		'America/Chicago'                => ' Central Time (US and Canada)',
		'America/Panama'                 => ' Panama',
		'America/Bogota'                 => ' Bogota',
		'America/Lima'                   => ' Lima',
		'America/Montreal'               => ' Montreal',
		'America/New_York'               => ' Eastern Time (US and Canada)',
		'America/Indianapolis'           => ' Indiana (East)',
		'America/Puerto_Rico'            => ' Puerto Rico',
		'America/Caracas'                => ' Caracas',
		'America/La_Paz'                 => ' La Paz',
		'America/Guyana'                 => ' Guyana',
		'America/Halifax'                => ' Halifax',
		'America/Santiago'               => ' Santiago',
		'America/Montevideo'             => ' Montevideo',
		'America/Araguaina'              => ' Recife',
		'America/Argentina/Buenos_Aires' => ' Buenos Aires',
		'America/Godthab'                => ' Greenland',
		'America/Sao_Paulo'              => ' Sao Paulo',
		'Canada/Atlantic'                => ' Atlantic Time (Canada)',
		'America/St_Johns'               => ' Newfoundland and Labrador',
		'Atlantic/Cape_Verde'            => ' Cape Verde Islands',
		'Atlantic/Azores'                => ' Azores',
		'UTC'                            => ' Universal Time UTC',
		'Etc/Greenwich'                  => ' Greenwich Mean Time',
		'Atlantic/Reykjavik'             => ' Reykjavik',
		'Europe/Dublin'                  => ' Dublin',
		'Europe/London'                  => ' London',
		'Europe/Lisbon'                  => ' Lisbon',
		'Africa/Nouakchott'              => ' Nouakchott',
		'Europe/Belgrade'                => ' Belgrade, Bratislava, Ljubljana',
		'CET'                            => ' Sarajevo, Skopje, Zagreb',
		'Africa/Casablanca'              => ' Casablanca',
		'Europe/Oslo'                    => ' Oslo',
		'Europe/Copenhagen'              => ' Copenhagen',
		'Europe/Brussels'                => ' Brussels',
		'Europe/Berlin'                  => ' Amsterdam, Berlin, Rome, Stockholm, Vienna',
		'Europe/Amsterdam'               => ' Amsterdam',
		'Europe/Rome'                    => ' Rome',
		'Europe/Stockholm'               => ' Stockholm',
		'Europe/Vienna'                  => ' Vienna',
		'Europe/Luxembourg'              => ' Luxembourg',
		'Europe/Paris'                   => ' Paris',
		'Europe/Zurich'                  => ' Zurich',
		'Europe/Madrid'                  => ' Madrid',
		'Africa/Bangui'                  => ' West Central Africa',
		'Africa/Algiers'                 => ' Algiers',
		'Africa/Tunis'                   => ' Tunis',
		'Europe/Warsaw'                  => ' Warsaw',
		'Europe/Prague'                  => ' Prague Bratislava',
		'Europe/Budapest'                => ' Budapest',
		'Europe/Helsinki'                => ' Helsinki',
		'Africa/Harare'                  => ' Harare, Pretoria',
		'Europe/Sofia'                   => ' Sofia',
		'Europe/Athens'                  => ' Athens',
		'Europe/Bucharest'               => ' Bucharest',
		'Asia/Nicosia'                   => ' Nicosia',
		'Asia/Beirut'                    => ' Beirut',
		'Asia/Jerusalem'                 => ' Jerusalem',
		'Africa/Tripoli'                 => ' Tripoli',
		'Africa/Cairo'                   => ' Cairo',
		'Africa/Johannesburg'            => ' Johannesburg',
		'Africa/Khartoum'                => ' Khartoum',
		'Europe/Kiev'                    => ' Kiev',
		'Africa/Nairobi'                 => ' Nairobi',
		'Europe/Istanbul'                => ' Istanbul',
		'Asia/Damascus'                  => ' Damascus',
		'Asia/Amman'                     => ' Amman',
		'Europe/Moscow'                  => ' Moscow',
		'Asia/Baghdad'                   => ' Baghdad',
		'Asia/Kuwait'                    => ' Kuwait',
		'Asia/Riyadh'                    => ' Riyadh',
		'Asia/Bahrain'                   => ' Bahrain',
		'Asia/Qatar'                     => ' Qatar',
		'Asia/Aden'                      => ' Aden',
		'Africa/Djibouti'                => ' Djibouti',
		'Africa/Mogadishu'               => ' Mogadishu',
		'Europe/Minsk'                   => ' Minsk',
		'Asia/Tehran'                    => ' Tehran',
		'Asia/Dubai'                     => ' Dubai',
		'Asia/Muscat'                    => ' Muscat',
		'Asia/Baku'                      => ' Baku, Tbilisi, Yerevan',
		'Asia/Kabul'                     => ' Kabul',
		'Asia/Yekaterinburg'             => ' Yekaterinburg',
		'Asia/Tashkent'                  => ' Islamabad, Karachi, Tashkent',
		'Asia/Calcutta'                  => ' India',
		'Asia/Kolkata'                   => ' Mumbai, Kolkata, New Delhi',
		'Asia/Kathmandu'                 => ' Kathmandu',
		'Asia/Almaty'                    => ' Almaty',
		'Asia/Dacca'                     => ' Dacca',
		'Asia/Dhaka'                     => ' Astana, Dhaka',
		'Asia/Rangoon'                   => ' Rangoon',
		'Asia/Novosibirsk'               => ' Novosibirsk',
		'Asia/Krasnoyarsk'               => ' Krasnoyarsk',
		'Asia/Bangkok'                   => ' Bangkok',
		'Asia/Saigon'                    => ' Vietnam',
		'Asia/Jakarta'                   => ' Jakarta',
		'Asia/Irkutsk'                   => ' Irkutsk',
		'Asia/Shanghai'                  => ' Beijing',
		'Asia/Hong_Kong'                 => ' Hong Kong SAR',
		'Asia/Taipei'                    => ' Taipei',
		'Asia/Kuala_Lumpur'              => ' Kuala Lumpur',
		'Asia/Singapore'                 => ' Singapore',
		'Australia/Perth'                => ' Perth',
		'Asia/Yakutsk'                   => ' Yakutsk',
		'Asia/Seoul'                     => ' Seoul',
		'Asia/Tokyo'                     => ' Osaka, Sapporo, Tokyo',
		'Australia/Darwin'               => ' Darwin',
		'Asia/Vladivostok'               => ' Vladivostok',
		'Pacific/Port_Moresby'           => ' Guam, Port Moresby',
		'Australia/Brisbane'             => ' Brisbane',
		'Australia/Adelaide'             => ' Adelaide',
		'Australia/Sydney'               => ' Canberra, Melbourne, Sydney',
		'Australia/Hobart'               => ' Hobart',
		'Asia/Magadan'                   => ' Magadan',
		'SST'                            => ' Solomon Islands',
		'Pacific/Noumea'                 => ' New Caledonia',
		'Asia/Kamchatka'                 => ' Kamchatka',
		'Pacific/Fiji'                   => ' Fiji Islands, Marshall Islands',
		'Pacific/Auckland'               => ' Auckland, Wellington',
		'Pacific/Apia'                   => ' Independent State of Samoa',

	);

	$zones_array = array();

	foreach ( $time_zone as $key => $item ) {
		if ( in_array( $key, timezone_identifiers_list(), true ) ) {
			$dt                  = new DateTimeImmutable( gmdate( 'Y-m-d 00:00:00' ), new DateTimeZone( $key ) );
			$zones_array[ $key ] = '(GMT' . $dt->format( 'P' ) . ')' . $item;
		}
	}

	return $zones_array;
}

/**
 * Require Admin Templates
 */
function admin_pages() {
	require_once STM_ZOOM_PATH . '/admin_templates/main.php';
}

/**
 * Get All Meetings
 * @return array
 */
function get_meetings() {
	$args     = array(
		'numberposts' => - 1,
		'post_type'   => 'stm-zoom',
	);
	$results  = array();
	$meetings = get_posts( $args );
	foreach ( $meetings as $meeting ) {
		$results[ $meeting->ID ] = $meeting->post_title;
	}
	wp_reset_postdata();

	return $results;
}

/**
 * Get All Webinars
 * @return array
 */
function get_webinars() {
	$args     = array(
		'numberposts' => - 1,
		'post_type'   => 'stm-zoom-webinar',
	);
	$results  = array();
	$webinars = get_posts( $args );
	foreach ( $webinars as $webinar ) {
		$results[ $webinar->ID ] = $webinar->post_title;
	}
	wp_reset_postdata();

	return $results;
}

/**
 * Get All Meetings and Webinars
 * @return array
 */
function get_meetings_webinars() {
	$args     = array(
		'numberposts' => - 1,
		'post_type'   => array( 'stm-zoom', 'stm-zoom-webinar' ),
	);
	$results  = array();
	$webinars = get_posts( $args );
	foreach ( $webinars as $webinar ) {
		$results[ $webinar->ID ] = $webinar->post_title;
	}
	wp_reset_postdata();

	return $results;
}

/**
 * Template Manager
 *
 * @param $file
 *
 * @return bool|string
 */
function get_zoom_template( $file ) {
	$templates = array(
		get_stylesheet_directory() . '/eroom_templates/',
		get_template_directory() . '/eroom_templates/',
		STM_ZOOM_PATH . '/templates/',
	);

	$templates = apply_filters( 'stm_zoom_template_pathes', $templates );

	foreach ( $templates as $template ) {
		if ( file_exists( $template . $file ) ) {
			return $template . $file;
		}
	}

	return false;
}

/**
 * Get Current Timezone
 * @return string
 */
function get_current_timezone() {
	$timezone_string = get_option( 'timezone_string' );
	if ( ! empty( $timezone_string ) ) {
		return $timezone_string;
	}

	$offset  = get_option( 'gmt_offset' );
	$hours   = (int) $offset;
	$minutes = abs( ( $offset - (int) $offset ) * 60 );
	$seconds = $hours * 60 * 60 + $minutes * 60;

	$timezone = timezone_name_from_abbr( '', $seconds, 1 );
	if ( false === $timezone ) {
		$timezone = timezone_name_from_abbr( '', $seconds, 0 );
	}

	return $timezone;
}

/**
 * Generate url link to Google Calendar
 *
 * @param $config
 * @param $options
 *
 * @return string
 * @throws Exception
 */

function stm_eroom_generate_google_calendar( $config, $options ) {
	$url = 'https://calendar.google.com/calendar/render?action=TEMPLATE';

	$url .= stm_eroom_generate_calendar_params( $config, false );

	$url .= '&text=' . rawurlencode( $config['title'] );
	$url .= '&details=' . rawurlencode( $config['description'] );
	$url .= '&location=' . rawurlencode( $config['address'] );
	$url .= '&sf=true&output=xml';

	if ( isset( $options['calendar_options']['rrule'] ) ) {
		$url .= '&recur=RRULE:' . rawurlencode( $options['calendar_options']['rrule'] );
	}

	return $url;
}

function stm_eroom_generate_calendar_params( $config, $ics = false ) {

	//set timezone
	$timezone_set = isset( $config['timezone'] ) ? $config['timezone'] : 'UTC';
	date_default_timezone_set( $timezone_set ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

	$duration = ! empty( $config['duration'] ) ? intval( $config['duration'] ) : 60;

	$start_date_time = strtotime( $config['start'] );
	$end_date_time   = strtotime( "+{$duration} minutes", $start_date_time );

	$utc_timezone        = new DateTimeZone( 'UTC' );
	$utc_start_date_time = new DateTime( '@' . $start_date_time, $utc_timezone );
	$utc_end_date_time   = new DateTime( '@' . $end_date_time, $utc_timezone );

	$date_format      = 'Ymd'; //no fixed time
	$date_time_format = 'Ymd\THis\Z';

	if ( $ics ) {

		$utc_stamp = new DateTime( 'now', $utc_timezone );

		$props[] = 'DTSTART:' . $utc_start_date_time->format( $date_time_format );
		$props[] = 'DTEND:' . $utc_end_date_time->format( $date_time_format );
		$props[] = 'DTSTAMP:' . $utc_stamp->format( $date_time_format );

		return $props;
	}

	$date_time_format = ! empty( $config['allDay'] ) ? $date_format : $date_time_format;

	return '&dates=' . $utc_start_date_time->format( $date_time_format ) . '/' . $utc_end_date_time->format( $date_time_format );
}

/**
 * Generate iCal Calendar
 *
 * @param $post_id
 *
 * @return string
 * @throws Exception
 */
function stm_eroom_generate_ics_calendar( $post_id = '' ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	$zoom_data = get_post_meta( $post_id, 'stm_zoom_data', true );

	if ( ! empty( $post_id ) && ! empty( $zoom_data ) && ! empty( $zoom_data['id'] ) ) {
		$title         = get_the_title( $post_id );
		$agenda        = get_post_meta( $post_id, 'stm_agenda', true );
		$duration      = get_post_meta( $post_id, 'stm_duration', true );
		$start_time    = get_post_meta( $post_id, 'stm_time', true );
		$timezone      = get_post_meta( $post_id, 'stm_timezone', true );
		$meeting_data  = StmZoom::meeting_time_data( $post_id );
		$meeting_start = $meeting_data['meeting_start'];

		$recurring_data = array();
		if ( class_exists( 'StmZoomRecurring' ) ) {
			$recurring_data = StmZoomRecurring::stm_product_recurring_meeting_data( $post_id, $zoom_data );
		}

		$config_calendar = array(
			'start'       => $meeting_start,
			'allDay'      => isset( $zoom_data['type'] ) && in_array( $zoom_data['type'], StmZoomAPITypes::TYPES_NO_FIXED, true ),
			'address'     => '',
			'title'       => $title,
			'duration'    => $duration,
			'description' => $agenda,
			'start_time'  => $start_time,
			'timezone'    => $timezone,
		);

		return stm_eroom_generate_ics_calendar_build( $config_calendar, $recurring_data );
	}
}


function stm_eroom_generate_ics_calendar_build( $config, $recurring_data ) {
	$ics_props = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-// eRoom plugin //NONSGML v1.0//EN',
		'CALSCALE:GREGORIAN',
		'BEGIN:VEVENT',
		'UID:eRoom-' . time(),
		'SUMMARY:' . $config['title'],
		'LOCATION:' . $config['address'],
		'DESCRIPTION' . $config['description'],
		'URL;VALUE=URI:https://wordpress.org/plugins/eroom-zoom-meetings-webinar/',
	);

	$ics_props_param = stm_eroom_generate_calendar_params( $config, true );
	$ics_props       = array_merge( $ics_props, $ics_props_param );

	if ( isset( $recurring_data['calendar_options']['rrule'] ) ) {
		$ics_props[] = 'RRULE:' . $recurring_data['calendar_options']['rrule'];
	}

	$ics_props[] = 'BEGIN:VALARM';
	$ics_props[] = 'ACTION:DISPLAY';
	$ics_props[] = 'RIGGER;RELATED=START:-PT00H15M00S';
	$ics_props[] = 'BEGIN:VALARM';

	// Build ICS properties - add footer
	$ics_props[] = 'END:VEVENT';
	$ics_props[] = 'END:VCALENDAR';

	return implode( "\r\n", $ics_props );
}

/**
 * Return Support Ticket URL
 * @return string
 */
function stm_zoom_get_ticket_url() {
	$type = defined( 'STM_ZOOM_PRO_PATH' ) ? 'support' : 'pre-sale';

	return "https://support.stylemixthemes.com/tickets/new/{$type}?item_id=27";
}
