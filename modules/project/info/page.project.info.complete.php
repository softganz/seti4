<?php
/**
* Project Complete
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/
function project_info_complete($self,$projectInfo) {
	$tpid = $projectInfo->tpid;

	$isEdit=user_access('administer projects','edit own project content',$projectInfo->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	if (!$isEdit) return message('error','Access denied');

	if (post('cancelreport')) {
		mydb::query('UPDATE %topic% SET `status`='._LOCK.' WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		mydb::query('UPDATE %project% SET `project_status`="กำลังดำเนินโครงการ" WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		location('project/'.$tpid);
	} else if (SG\confirm()) {
		mydb::query('UPDATE %topic% SET `status`='._PUBLISH.' WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		mydb::query('UPDATE %project% SET `project_status`="ดำเนินการเสร็จสิ้น" WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		$ret.='<p class="notify">ได้ดำเนินการแจ้งปิดโครงการให้ผู้ดูแลระบบเรียบร้อยแล้ว หากต้องการยกเลิกการแจ้ง กรุณาคลิก <a href="'.url('project/'.$tpid.'/info.complete',array('cancelreport' => 'yes')).'">ยกเลิกการแจ้งปิดโครงการ</a>';
	} else {
		$form = new Form(NULL, url(q()));

		$form->addField(
			'confirm',
			array(
				'type' => 'radio',
				'label' => 'คุณต้องการแจ้งปิดโครงการเสร็จสิ้นสมบูรณ์ <strong>"'.$projectInfo->title.'"</strong>  ใช่หรือไม่?',
				'options' => array('no' => 'ไม่ ฉันไม่ต้องการแจ้ง','yes' => 'ใช่ ฉันต้องการแจ้งปิดโครงการ')
			)
		);

		$form->addField(
			'submit',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>แจ้งปิดโครงการ</span>',
				'container' => '{class: "-sg-text-right"}',
				'description' => 'คำเตือน : หลังจากที่ผู้ดูแลระบบปิดโครงการนี้แล้ว ท่านจะไม่สามารถแก้ไขหรือส่งรายงานกิจกรรมของโครงการได้อีกต่อไป'
			)
		);

		$ret .= $form->build();
	}

	return $ret;
}
?>