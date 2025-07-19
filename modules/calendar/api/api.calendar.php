<?php
/**
 * Calendar:: API
 * Created :: 2025-07-19
 * Modify  :: 2025-07-19
 * Version :: 1
 *
 * @param Int $calendarId
 * @param String $action
 * @param Int $tranId
 * @return Array/Object
 *
*  @usage api/module/{Id}/{action}[/{tranId}]
 */

class CalendarApi extends PageApi {
	var $calendarId;
	var $action;
	var $tranId;
	var $actionDefault;

	function __construct($calendarId = NULL, $action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'calendaeInfo' => $calendaeInfo = (is_numeric($calendarId) ? CalendarModel::getById($calendarId) : NULL),
			'calendarId' => $calendaeInfo->calId,
			'right' => new CalendarRightModel($calendaeInfo->calId),
		]);
		debugMsg($this, '$this');
	}

	#[\Override]
	function build() {
		// debugMsg('calendarId '.$this->calendarId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// Check if no calendar information is provided
		if (empty($this->calendarId)) return error(_HTTP_ERROR_NOT_FOUND, 'PROCESS ERROR');

		return parent::build();
	}

	// This method is used to create and update calendar information
	function update() {
		// Check if the user has permission to update the calendar
		if (!$this->right->edit) return apiError(_HTTP_ERROR_FORBIDDEN, 'You do not have permission to edit this calendar');

		$data = (object) post('calendar');

		$result = CalendarModel::update($data, '{debug: false}');

		return apiSuccess([
			'calId' => $data->id,
			'title' => $data->title,
		]);
	}

	// Delete the calendar item
	function delete() {
		if (!$this->right->edit) return apiError(_HTTP_ERROR_FORBIDDEN, 'You do not have permission to edit this calendar');
		if (!SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'Please confirm the deletion');

		DB::query([
			'DELETE FROM %calendar% WHERE `id` = :id',
			'var' => [':id' => $this->calendarId]
		]);

		return apiSuccess('success','ลบรายการปฏิทินเรียบร้อยแล้ว','calendar');
	}
}
?>