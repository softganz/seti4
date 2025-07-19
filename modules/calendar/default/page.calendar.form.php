<?php
/**
 * Calendar:: Insert or Update Calendar Form
 * Created :: 2007-03-06
 * Modify  :: 2025-07-19
 * Version :: 2
 *
 * @return Widget
 *
 * @usage calendar/form?calendarId={calendarId}&module={module}
 */

class CalendarForm extends Page {
	var $calendarId;
	var $right;
	var $calendarInfo;

	function __construct() {
		parent::__construct([
			'calendarId' => $calendarId = SG\getFirstInt(Request::get('calendarId')),
			'calendarInfo' => $calendarId ? CalendarModel::getById($calendarId) : NULL,
			'right' => new CalendarRightModel($calendarId),
		]);
	}

	function rightToBuild() {
		if (empty($this->calendarId) && !$this->right->create) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		if ($this->calendarId && !$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		return true; // Allow to build if user has permission
	}

	#[\Override]
	function build() {
		$post = (Object) post();

		if ($this->calendarId && $post->module) {
			$isEdit = R::On($post->module.'.calendar.isadd',$this->calendarInfo);
			if (!$isEdit) return error('Access denied');
		}

		if (empty($this->calendarInfo->calId)) {
			$this->calendarInfo = (Object) [
				'from_date' => SG\getFirst($post->d, date('Y-m-d')),
				'to_date' => SG\getFirst($post->d, date('Y-m-d')),
				'tpid' => $post->tpid
			];
		}

		$this->calendarInfo->from_date = sg_date($this->calendarInfo->from_date,'d/m/Y');
		$this->calendarInfo->to_date = sg_date($this->calendarInfo->to_date,'d/m/Y');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Caendar Form',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					R::View('calendar.form', $this->calendarInfo, $post),
				], // children
			]), // Widget
		]);
	}
}
?>