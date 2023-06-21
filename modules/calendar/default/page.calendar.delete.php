<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function calendar_delete($self, $calId) {
	$calendarInfo = is_object($calId) ? $calId : R::Model('calendar.get',$calId, '{initTemplate: true}');
	$calId = $calendarInfo->id;

	$module = post('module');

	$self->theme->title = $calendarInfo->title;

	$ret = '';

	$isEdit = false;

	if (user_access('administer calendars','edit own calendar content',$calendarInfo->owner)) {
		$isEdit = true;
	} else if ($calendarInfo->tpid && i()->ok) {
		$topicuser = mydb::select('SELECT UPPER(`membership`) `membership` FROM %topic_user% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1',':tpid',$calendarInfo->tpid,':uid',i()->uid);
		if (in_array($topicuser->membership,array('OWNER','ADMIN','MANAGER','TRAINER'))) $isEdit = true;
	}


	if ( $calendarInfo->_empty ) {
		$ret .= message('error','Calendar item not found');
	} else if (!$isEdit) {
		$ret .= message('error','Access denied','calendar');
	} else if (\SG\confirm()) {
		if ($module) 	$form = R::On($module.'.calendar.delete', $calendarInfo, post());
		mydb::query('DELETE FROM %calendar% WHERE `id` = :caiId LIMIT 1',':caiId',$calId);
		mydb::clear_autoid('%calendar%');
		mydb::query('DELETE FROM %property% WHERE `module`="calendar" AND `propid`=:propid',':propid',$calId);
		$ret .= '<font color="red">Calendar item was deleted.</font><br/>';
		//location('calendar/'.$year.'/'.$month.($para->tpid?'/tpid/'.$para->tpid:''));
	} else {
		$ret .= '<header class="header">'._HEADER_BACK.'<h3 class="title -box">ลบปฎิทิน</h3></header>';

		$ret .= '<p style="margin: 32px;"><b>คำเตือน : จะทำการลบข้อมูลปฏิทินรายการนี้ และจะไม่สามารถเรียกคืนได้อีกแล้ว กรุณายืนยัน?</b></p>';

		$ret .= '<div class="-sg-text-right"><a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i>{tr:CANCEL}</a> <a class="sg-action btn -danger" href="'.url(q(),array('module'=>$module,'confirm'=>'yes')).'" data-rel="none" data-callback="calendarRefresh" data-x-ret="'.url('calendar',array('year'=>sg_date($calendarInfo->from_date,'Y'),'month'=>sg_date($calendarInfo->from_date,'m'))).'" data-done="close"><i class="icon -material">delete</i><span>ดำเนินการลบ</span></a></div>';
	}

	$ret .= '<script>
	function calendarRefresh() {
		$("#calendar-refresh").trigger("click")
	}</script>';

	return $ret;
}
?>