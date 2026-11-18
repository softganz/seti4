<?php
/**
 * Calendar :: Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2007-03-06
 * Modified :: 2026-11-18
 * Version  :: 4
 *
 * @param int $calendarId or *,t:13259 to all include node 13259
 * @param string $action
 *
 * @uses calendar/{Id}/{action}[/{tranId}]
 */

class calendar extends PageController {
	public $calendarId;

	function __construct($calendarId = null, $action = null) {
		if (substr($calendarId, 0, 1) === '*') {
			$get = $calendarId;
			unset($calendarId, $action);
		}

		if (empty($calendarId) && empty($action)) {
			$action = 'home';
		} elseif ($calendarId && empty($action)) {
			$action = 'view';
		}

		parent::__construct([
			'calendarId' => $calendarId ?? null,
			'action' => 'calendar.' . $action,
			'args' => func_get_args(),
			'info' =>
				!empty($calendarId) && is_numeric($calendarId)
					? CalendarModel::getById($calendarId)
					: null
		]);

		// Send get parameter to hom
		if ($get) {
			$this->info = (object) $get;
		}
	}

	function rightToBuild() {
		if (!module_install('calendar')) {
			return error(_HTTP_ERROR_NOT_FOUND, 'Calendar Not Install');
		}
	}
}
?>
