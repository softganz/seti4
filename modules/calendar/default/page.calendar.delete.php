<?php
/**
 * Calendar:: Delete Calendar Page
 * Created :: 2007-03-06
 * Modify  :: 2025-07-19
 * Version :: 2
 *
 * @param String $calendarInfo
 * @return Widget
 *
 * @usage calendar/{calendarId}/delete
 */

use Softganz\DB;

class CalendarDelete extends Page {
	var $calendarId;
	var $module;
	var $calendarInfo;

	function __construct($calendarInfo = NULL) {
		parent::__construct([
			'calendarId' => $calendarInfo->calId,
			'module' => post('module'),
			'calendarInfo' => $calendarInfo
		]);
	}

	function rightToBuild() {
		if (empty($this->calendarId)) return error(_HTTP_ERROR_BAD_REQUEST, 'Calendar ID is required');
		return true; // Allow to build if calendarId is set
	}

	#[\Override]
	function build() {
		$ret = '';

		$isEdit = false;

		if (user_access('administer calendars','edit own calendar content',$this->calendarInfo->owner)) {
			$isEdit = true;
		} else if ($this->calendarInfo->tpid && i()->ok) {
			$membership = DB::select([
				'SELECT UPPER(`membership`) `membership` FROM %topic_user% WHERE `tpid` = :nodeId AND `uid` = :userId LIMIT 1',
				'var' => [
					':nodeId' => $this->calendarInfo->tpid,
					':userId' => i()->uid
				]
			])->membership;
			$isEdit = in_array($membership, ['OWNER','ADMIN','MANAGER','TRAINER']);
		}

		if (!$isEdit) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		if (SG\confirm()) return $this->delete();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ลบปฎิทิน : '.$this->calendarInfo->title,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Column([
				'class' => '-sg-paddingnorm',
				'children' => [
					new Container([
						'style' => 'margin: 24px',
						'child' => '<b>คำเตือน : จะทำการลบข้อมูลปฏิทินรายการนี้ และจะไม่สามารถเรียกคืนได้อีกแล้ว กรุณายืนยัน?</b>'
					]), // Container for warning message

					new Row([
						'mainAxisAlignment' => 'end',
						'crossAxisAlignment' => 'center',
						'children' => [
							new Button([
								'class' => 'sg-action',
								'type' => 'cancel',
								'href' => 'javascript:void(0)',
								'rel' => 'back',
								'icon' => new Icon('cancel'),
								'text' => '{tr:CANCEL}',
							]), // Cancel button
							new Button([
								'class' => 'sg-action',
								'type' => 'danger',
								'href' => url('api/calendar/'.$this->calendarId.'/delete', ['module' => $this->module, 'confirm' => 'yes']),
								'rel' => 'none',
								'icon' => new Icon('delete'),
								'text' => 'ดำเนินการลบ',
								'done' => 'close | remove:.-calendar-item-'.$this->calendarId, // Close the box and remove the calendar item
							]), // Delete button
						], // children
					]), // Row for action buttons
				], // children
			]), // Widget
		]);
	}
}
?>