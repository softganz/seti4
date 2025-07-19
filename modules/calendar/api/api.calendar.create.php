<?php
/**
 * Calendar:: Create new calendar item API
 * Created :: 2025-07-19
 * Modify  :: 2025-07-19
 * Version :: 1
 *
 * @return Array/Object
 *
 *  @usage api/calendar/create
 */

class CalendarCreateApi extends PageApi {
	var $right;

	function __construct() {
		parent::__construct([
			'right' => new CalendarRightModel(),
		]);
	}

	function rightToBuild() {
		if (!$this->right->create) return apiError(_HTTP_ERROR_FORBIDDEN, 'You do not have permission to create calendar');
		return true; // Allow to build if user has permission to create calendar
	}

	#[\Override]
	function build() {
		$data = (object) post('calendar');
		// TODO: Please test this code, it may not work as expected.
		if ($data->module) {
			$isModuleAddable = R::On($data->module.'.calendar.isadd',$data);
		}

		if (($this->right->create || $isModuleAddable) && post('calendar')) {
			$result = CalendarModel::Update($data, '{debug: false}');
		}
		return apiSuccess([
			'calId' => $data->id,
			'title' => $data->title,
		]);
	}
}
?>