<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_view($self, $id=NULL) {
	$para=para(func_get_args(),1);
	$module = post('module');

	$rs = R::Model('calendar.get',$id);

	$self->theme->title=$rs->title;

	$ret = '<header class="header">'._HEADER_BACK.'<h3 class="title -box">'.$rs->title.'</h3></header>';

	if ($rs->topic_title && $rs->topicType == 'project') $ret.='<h4><a href="'.url('project/'.$rs->tpid).'">'.$rs->topic_title.'</a></h4>';
	$ret .= '<p><strong>'.tr('วัน ').sg_date($rs->from_date,'ววว ว ดดด ปปปป');
	if ($rs->to_date!=$rs->from_date) $ret .= ' - '.sg_date($rs->to_date,'ววว ว ดดด ปปปป');
	if ($rs->from_time) $ret .= tr(' เวลา ').$rs->from_time;
	if ($rs->to_time) $ret .= ' - '.$rs->to_time;
	if ($rs->from_time || $rs->to_time) $ret.=' น.';
	if ($rs->location) $ret .= '<br /><br />'.tr('สถานที่').' '.$rs->location;
	$ret .= '</strong></p>';
	list($year,$month)=explode('-',$rs->from_date);

	$is_edit=false;
	if (user_access('administer calendars','edit own calendar content',$rs->owner)) {
		$is_edit=true;
	} else if ($rs->tpid && i()->ok) {
		$topicuser=mydb::select('SELECT `membership` FROM %topic_user% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1',':tpid',$rs->tpid,':uid',i()->uid);
		if (in_array($topicuser->membership,array('Trainer','Owner'))) $is_edit=true;
	}

	$ui = new Ui();
	$ui->add('<a id="calendar-back" class="sg-action btn -link" title="กลับสู่หน้าปฏิทิน" data-rel="close"><i class="icon -back"></i><span>BACK</span></a>');
	if ($is_edit) {
		$ui->add('<a id="calendar-edit" class="sg-action btn -link" href="'.url('calendar/'.$rs->id.'/edit',array('module'=>$module)).'" title="แก้ไขรายละเอียด" data-rel="#calendar-body" data-done="close"><i class="icon -edit"></i></a>');

		// ปิดปุ่มลบชั่วคราว จนกว่าจะหาวิธีที่ดีกว่านี้
		if (mydb::table_exists('%project_tr%')) {
			if (mydb::select('SELECT `calid` FROM %project_tr% WHERE `calid` = :id LIMIT 1',':id',$id)->calid) {
				$ui->add('<a href="javascript:void(0)" class="-disabled" title="ลบรายการไม่ได้"><i class="icon -delete -gray"></i></a>');
			} else {
				$ui->add('<a id="calendar-delete" class="sg-action btn -link" href="'.url('calendar/'.$rs->id.'/delete',array('module'=>$module)).'" data-rel="box" title="ลบหัวข้อนี้" data-width="600"><i class="icon -delete"></i></a>');
			}
		} else {
			$ui->add('<a id="calendar-delete" class="sg-action btn -link" href="'.url('calendar/'.$rs->id.'/delete',array('module'=>$module)).'" data-rel="box" title="ลบหัวข้อนี้" data-width="600"><i class="icon -delete"></i></a>');
		}
	}
	if (module_install('project') && $rs->tpid) $ui->add('<a class="sg-action btn -link" href="'.url('project/'.$rs->tpid.'/info.calendar.short/'.$rs->id).'" data-rel="box" title="การดำเนินกิจกรรม"><i class="icon -viewdoc"></i></a>');

	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$ret .= '<div style="min-height: 100px;">'.($rs->detail ? sg_text2html($rs->detail) : '').'</div>';
	$ret.='<p class="">โดย '.$rs->owner_name.' เมื่อ '.sg_date($rs->created_date,'ว ดด ปป H:i').' น.</p>';
	return $ret;
}
?>