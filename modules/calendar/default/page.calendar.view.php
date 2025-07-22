<?php
/**
 * Calendar:: View
 * Created :: 2007-03-06
 * Modify  :: 2025-07-22
 * Version :: 4
 *
 * @param String $calendarInfo
 * @return Widget
 *
 * @usage calendar/{calendarId}
 */

use Softganz\DB;

class CalendarView extends Page {
	var $calendarId;
	var $module;
	var $calendarInfo;

	function __construct($calendarInfo = NULL) {
		parent::__construct([
			'calendarId' => $calendarInfo->calId,
			'module' => post('module'),
			'calendarInfo' => $calendarInfo,
		]);
	}

	function rightToBuild() {
		if (empty($this->calendarId)) return error(_HTTP_ERROR_BAD_REQUEST, 'Calendar ID is required');
		return true; // Allow to build if calendarId is set
	}
	
	#[\Override]
	function build() {
		$isEdit = false;
		if (user_access('administer calendars', 'edit own calendar content', $this->calendarInfo->owner)) {
			$isEdit = true;
		} else if ($this->calendarInfo->tpid && i()->ok) {
			$membership = DB::select([
				'SELECT UPPER(`membership`) `membership` FROM %topic_user% WHERE `tpid` = :nodeId AND `uid` = :userId LIMIT 1',
				'var' => [':nodeId' => $this->calendarInfo->tpid, ':userId' => i()->uid]
			])->membership;
			if (in_array($membership, ['TRAINNER','OWNER'])) $isEdit = true;
		}
	
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->calendarInfo->title,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				'trailing' => new Row([
					'children' => [
						'<a id="calendar-back" class="sg-action btn -link" title="กลับสู่หน้าปฏิทิน" data-rel="close"><i class="icon -material">navigate_before</i><span>BACK</span></a>',
						$isEdit ? new Children([
							'children' => [
								'<a id="calendar-edit" class="sg-action btn -link" href="'.url('calendar/form', ['calendarId' => $this->calendarInfo->calId, 'module' => $this->module]).'" title="แก้ไขรายละเอียด" data-rel=".calendar-content" data-done="close"><i class="icon -material">edit</i></a>',

								// ปิดปุ่มลบชั่วคราว จนกว่าจะหาวิธีที่ดีกว่านี้
								$this->calendarInfo->nodeId ? NULL
									// if (mydb::select('SELECT `calid` FROM %project_tr% WHERE `calid` = :id LIMIT 1',':id',$this->calendarId)->calid) {
									// 	$ui->add('<a href="javascript:void(0)" class="-disabled" title="ลบรายการไม่ได้"><i class="icon -material">delete</i></a>');
									// } else {
									// 	$ui->add('<a id="calendar-delete" class="sg-action btn -link" href="'.url('calendar/'.$this->calendarInfo->id.'/delete',array('module'=>$this->module)).'" data-rel="box" title="ลบหัวข้อนี้" data-width="600"><i class="icon -material">delete</i></a>');
									// }
								: new Button([
									'type' => 'danger',
									'id' => 'calendar-delete',
									'class' => 'sg-action',
									'href' => url('calendar/'.$this->calendarInfo->id.'/delete', ['module' => $this->module]),
									'rel' => 'box',
									'title' => 'ลบหัวข้อนี้',
									'data-width' => '600',
									'data-height' => 'auto',
									'icon' => new Icon('delete'),
								]),
							], // children
						]) : NULL, // Edit button if user has permission
					
						(module_install('project') && $this->calendarInfo->nodeId) ? '<a class="sg-action btn -link" href="'.url('project/'.$this->calendarInfo->nodeId.'/info.calendar.short/'.$this->calendarInfo->calId).'" data-rel="box" title="การดำเนินกิจกรรม"><i class="icon -material">find_in_page</i></a>' : NULL

					], // children
				]), // Row
			]), // AppBar
			'body' => new Column([
				'class' => '-sg-paddingnorm',
				'style' => 'gap: 0.5rem;',
				'children' => [
					$this->calendarInfo->topic_title && $this->calendarInfo->topicType == 'project' ? new ListTile([
						'title' => new Button([
							'href' => url('project/'.$this->calendarInfo->tpid),
							'text' => $this->calendarInfo->topic_title,
						]), // Button to link to the project
						'leading' => new Icon('directions_run'),
					]) : NULL,
					'<strong>'.tr('วัน ').sg_date($this->calendarInfo->from_date,'ววว ว ดดด ปปปป')
					. ($this->calendarInfo->to_date!=$this->calendarInfo->from_date ? ' - '.sg_date($this->calendarInfo->to_date,'ววว ว ดดด ปปปป') : '')
					. ($this->calendarInfo->from_time ? tr(' เวลา ').$this->calendarInfo->from_time : '')
					. ($this->calendarInfo->to_time ? ' - '.$this->calendarInfo->to_time : '')
					. ($this->calendarInfo->from_time || $this->calendarInfo->to_time ? ' น.' : '')
					.'</strong>',
					$this->calendarInfo->location ? '<b>'.tr('สถานที่').' '.$this->calendarInfo->location.'</b>' : NULL, // Location of the calendar event
					$this->calendarInfo->detail ? sg_text2html($this->calendarInfo->detail) : NULL, // For calendar details
					'โดย '.$this->calendarInfo->owner_name.' เมื่อ '.sg_date($this->calendarInfo->created_date,'ว ดด ปป H:i').' น.',
					// new DebugMsg($this->calendarInfo, '$calendarInfo'), // Debug message to show the calendar data
				], // children
			]), // Widget
		]);
	}
}
?>