<?php
/**
* Org :: Calendar
* Created 2021-08-14
* Modify  2021-08-14
*
* @return Widget
*
* @usage org/{id}/info.calendar
*/

$debug = true;

class OrgInfoCalendar extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar',
			]),
			'body' => new Widget([]),
		]);
	}
}
?>