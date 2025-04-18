<?php

class StmZoomAPITypes {
	/*
	 * Meeting type
	 *
	 * 1 - Instant meeting
	 * 2 - Scheduled meeting (default)
	 * 3 - Recurring meeting with no fixed time
	 * 8 - Recurring meeting with fixed time
	 *
	 * */

	/*
	* Webinar types
	*
	* 5 - Webinar (default)
	* 6 - Recurring meeting with no fixed time
	* 9 - Recurring meeting with fixed time
	*
	* */

	const TYPE_MEETING_INSTANT = '1';
	const TYPE_MEETING_SCHEDULED = '2';
	const TYPE_MEETING_NO_FIXED = '3';
	const TYPE_MEETING_RECURRING = '8';

	const TYPE_WEBINAR = '5';
	const TYPE_WEBINAR_NO_FIXED = '6';
	const TYPE_WEBINAR_RECURRING = '9';

	const TYPES_RECURRING = [ self::TYPE_MEETING_RECURRING, self::TYPE_WEBINAR_RECURRING ];
	const TYPES_NO_FIXED = [ self::TYPE_MEETING_NO_FIXED, self::TYPE_WEBINAR_NO_FIXED ];
	const TYPES_RECURRING_AND_NO_FIXED = [ self::TYPE_MEETING_RECURRING, self::TYPE_WEBINAR_RECURRING, self::TYPE_MEETING_NO_FIXED, self::TYPE_WEBINAR_NO_FIXED ];

	/*
	* Recurrence meeting types
	*
	* 1 - Daily
	* 2 - Weekly
	* 3 - Monthly
	*
	* */

	const TYPE_RECURRENCE_DAILY = '1';
	const TYPE_RECURRENCE_WEEKLY = '2';
	const TYPE_RECURRENCE_MONTHLY = '3';

	const TYPES_RECURRENCE_ALL = [ self::TYPE_RECURRENCE_DAILY, self::TYPE_RECURRENCE_WEEKLY, self::TYPE_RECURRENCE_MONTHLY ];

}