<?php
/**
 * Calendar:: Page Controller
 * Created :: 2007-03-06
 * Modify  :: 2025-07-19
 * Version :: 2
 *
 * @param Int $calendarId or *,t:13259 to all include node 13259
 * @param String $action
 * @return Widget
 *
 * @usage calendar/{Id}/{action}[/{tranId}]
 */

class calendar extends PageController {
	var $calendarId;
	var $action;

	function __construct($calendarId = NULL, $action = NULL) {
		if (substr($calendarId, 0, 1) === '*') {
			$get = $calendarId;
			unset($calendarId, $action);
		}

		if (empty($calendarId) && empty($action)) $action = 'home';
		else if ($calendarId && empty($action)) $action = 'view';


		parent::__construct([
			'calendarId' => $calendarId,
			'action' => 'calendar.'.$action,
			'args' => func_get_args(),
			'info' => is_numeric($calendarId) ? CalendarModel::getById($calendarId) : NULL,
		]);

		// Send get parameter to hom
		if ($get) $this->info = $get;
	}

	function rightToBuild() {
		if (!module_install('calendar')) return error(_HTTP_ERROR_NOT_FOUND, 'Calendar Not Install');
	}
}
?>