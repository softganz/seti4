<?php
/**
 * Calendar  :: Model
 * Created :: 2025-07-19
 * Modify  :: 2025-07-19
 * Version :: 1
 *
 * @param Array $args
 * @return Object
 *
 * @usage import('model:calendar.right.php')
 * @usage new CalendarRightModel([])
 */

class CalendarRightModel {
	var $create = false; // Cannot use property 'add' because it conflicts with the 'add' method in the CalendarModel class.
	var $edit = false;

	function __construct($calendarId = NULL) {
		$calendarInfo = $calendarId ? CalendarModel::getById($calendarId) : NULL;

		$this->create = user_access('administer calendars,create calendar content');
		$this->edit = user_access('administer calendars') || (i()->ok && $calendarInfo->owner == i()->uid);
	}
}