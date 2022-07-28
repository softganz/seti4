<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_calendar_delete($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$id = $conditions->id;

	$para=para(func_get_args(),2);

	$rs = calendar_model::get($id);
	$this->theme->title=$rs->title;
	list($year,$month)=explode('-',$rs->from_date);

	$is_edit=false;
	if (user_access('administer calendars','edit own calendar content',$rs->owner)) {
		$is_edit=true;
	} else if ($rs->tpid && i()->ok) {
		$topicuser=mydb::select('SELECT membership FROM %topic_user% WHERE tpid=:tpid AND uid=:uid LIMIT 1',':tpid',$rs->tpid,':uid',i()->uid);
		if (in_array($topicuser->membership,array('Trainer'))) $is_edit=true;
	}

	if ( $rs->empty ) {
		$ret .= message('error','Calendar item not found');
	} else if (!$is_edit) {
		$ret .= message('error','Access denied','calendar');
	} else if (!SG\confirm()) {
		//location('calendar/'.$year.'/'.$month.($para->tpid?'/tpid/'.$para->tpid:''));
	} else if (SG\confirm()) {
		if ($para->module) 	$form = R::On($para->module.'.calendar.delete', $rs, $para);
		mydb::query('DELETE FROM %calendar% WHERE id=:id LIMIT 1',':id',$id);
		mydb::clear_autoid('%calendar%');
		mydb::query('DELETE FROM %property% WHERE `module`="calendar" AND `propid`=:propid',':propid',$id);
		$ret .= '<font color="red">Calendar item was deleted.</font><br/>';
	} else {
		$ret .= '<h3 class="title -box">ลบปฎิทิน</h3>';

		$ret .= '<p style="margin: 32px;"><b>คำเตือน : จะทำการลบข้อมูลปฏิทินรายการนี้ และจะไม่สามารถเรียกคืนได้อีกแล้ว กรุณายืนยัน?</b></p>';

		$ret .= '<div class="-sg-text-right"><a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i>{tr:CANCEL}</a> <a class="btn -danger" href="'.url(q(),array('confirm'=>'yes')).'"><i class="icon -material">delete</i><span>ดำเนินการลบ</span></a></div>';
	}

	return $result;
}
?>