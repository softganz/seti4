<?php
/**
* Delete Project
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

import('model::project.php');

function project_info_delete($self, $tpid) {
	$getForceDelete = post('forcedelete') == 'yes';

	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	R::View('project.toolbar',$self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	$ret .= '<header class="header"><h3>ลบโครงการ</h3></header>';

	$isAdmin = user_access('administer projects');
	$isDeletable = $isAdmin
		|| (i()->ok && ($projectInfo->info->uid == i()->uid))
		|| ($projectInfo->RIGHT & IS_ADMIN);

	if (!$isDeletable) return message('error','access denied');

	$forceDelete = $getForceDelete || cfg('project.delete.force') || $projectInfo->settings->forceDeleteProject;


	if ($projectInfo->info->proposalId && !$forceDelete) {
		$ret .= '<p class="notify">คำเตือน ​: โครงการนี้มีการพัฒนาโครงการ ไม่สามารถลบทิ้งได้</p>';
		return $ret;
	}

	$childProjectDb = mydb::select('SELECT `tpid`,`title` FROM %topic% WHERE `parent` = :tpid', ':tpid', $tpid);

	if ($childProjectDb->count()) {
		$ret .= message('notify', 'มีโครงการย่อยภายใต้โครงการนี้ ไม่สามารถลบทิ้งได้ กรุณาลบโครงการย่อยทั้งหมดก่อน');
		$ret .= '<h3>รายชื่อโครงการย่อย</h3>';
		$ui = new Ui();
		foreach ($childProjectDb->items as $rs) {
			$ui->add('<a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>');
		}
		$ret .= $ui->build();
		return $ret;
	}

	// Use  Organization GL
	if (mydb::table_exists('%project_gl%')) {
		$stmt = 'SELECT * FROM %project_gl% WHERE `tpid` = :tpid LIMIT 1';
		$dbs = mydb::select($stmt, ':tpid', $tpid);
		//$ret .= print_o($dbs,'$dbs');

		if ($dbs->count()) {
			$ret .= '<p class="notify">คำเตือน ​: โครงการนี้มีรายการทางการเงินแล้ว ไม่สามารถลบทิ้งได้</p>';
			$ret .= 'หากต้องการลบโครงการ ให้ลบรายการทางการเงิน เช่น ใบเบิกเงิน ใบคืนเงิน ให้หมดก่อน';
			return $ret;
		}
	}






	$ret .= '<h3 class="notify">!!!!! คำเตือน กำลังลบโครงการ : '.$projectInfo->title.' !!!!!</h3>';



	// ขั้นตอนที่ 1 :: ให้ระงับโครงการก่อน
	// ขั้นตอนที่ 2 :: ยืนยันการลบ
	//$ret .= print_o(post(),'post()');

	if (post('cancelclose')) {
		mydb::query('UPDATE %topic% SET `status` = :status WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid, ':status', _LOCK);
		mydb::query('UPDATE %project% SET `state` = 0, `project_status` = "กำลังดำเนินโครงการ" WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid);
		location('project/'.$tpid);
	} else if (post('confirm')=='no' || post('confirm1') == 'no' || post('confirm2') == 'no') {
		location('project/'.$tpid);
	} else if (post('confirm2') == 'yes') {
		$ret .= 'PROCEED DELETE PROJECT';

		//$ret .= print_o($projectInfo);
		$result = ProjectModel::delete($tpid);

		//$ret .= print_o($result,'$result');

		if ($result->complete) {
			if ($projectInfo->info->parent) {
				location('project/'.$projectInfo->info->parent);
			} else {
				location('project/my/all');
			}
		} else {
			$ret .= message('error','มีข้อผิดพลาดระหว่างการลบโครงการ กรุณาติดต่อผู้ดูแลระบบ');
		}
	} else if (post('confirm1') == 'yes') {
		$ret.='<h3>ขั้นตอนที่ 2 :: การยืนยันการลบ "พัฒนาโครงการ" และ "ติดตามประประเมินผลโครงการ"</h3>';
		$ret .= '<p>ต้องการลบโครงการ <b>'.$projectInfo->title.'</b> กำลังอยู่ในสถานะ <b>'.$projectInfo->info->project_status.'</b> กรุณายืนยัน?</p>';

		$form = new Form(NULL, url('project/'.$tpid.'/info.delete'), NULL, 'sg-form -delete');
		if ($getForceDelete) $form->addField('forcedelete', array('type'=>'hidden', 'value'=>'yes'));

		$form->addField(
			'confirm2',
			array(
				'type' => 'radio',
				'options' => array(
					'no' => 'ไม่ ฉันไม่ต้องการลบโครงการ',
					'yes' => '<strong>ใช่ ฉันต้องการลบโครงการนี้แน่นอน!!!!</strong>'
				),
			)
		);
		$form->addField(
			'submit',
			array(
				'type' => 'button',
				'class' => '-danger',
				'value' => '<i class="icon -delete"></i><span>ยืนยันเพื่อดำเนินการลบข้อมูลโครงการนี้</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/'.$tpid.'/info.delete',array('confirm'=>'no')).'"><i class="icon -cancel"></i>{tr:CANCEL}</a>',
				'container' => array('class'=>'-sg-text-right'),
			)
		);
		$form->addText('<strong style="color:red; font-size: 1.2em;">คำเตือน : หลังจากที่ลบโครงการนี้แล้ว ข้อมูลทุกอย่างเกี่ยวกับ "พัฒนาโครงการ" และ "ติดตามประเมินผลโครงการ" รวมทั้งไฟล์เอกสารและไฟล์ภาพทั้งหมดจะถูกลบทิ้ง และจะไม่สามารถเรียกคืนข้อมูลกลับมาได้อีกต่อไป</strong>');

		$ret .= $form->build();
	} else if ($projectInfo->info->state != 99) {
		$ret.='<h3>ขั้นตอนที่ 1 :: เตรียมลบ "พัฒนาโครงการ" และ "ติดตามประประเมินผลโครงการ"</h3>';

		$ret .= '<p>ต้องการลบโครงการ <b>'.$projectInfo->title.'</b> กำลังอยู่ในสถานะ <b>'.$projectInfo->info->project_status.'</b> กรุณายืนยัน?</p>';

		$form = new Form(NULL, url('project/'.$tpid.'/info.delete'), NULL, 'sg-form -delete');

		$form->addData('rel', 'box');
		$form->addData('width', '640');

		if ($getForceDelete) $form->addField('forcedelete', array('type'=>'hidden', 'value'=>'yes'));

		$form->addField(
			'confirm1',
			array(
				'type' => 'radio',
				'options' => array(
					'no' => 'ไม่ ฉันไม่ต้องการลบโครงการ',
					'yes' => '<strong>ใช่ ฉันต้องการลบโครงการ</strong>'
				),
			)
		);

		$form->addField(
			'submit',
			array(
				'type' => 'button',
				'class' => '-danger',
				'value' => '<i class="icon -material">delete</i><span>ยืนยันเพื่อดำเนินการขั้นตอนถัดไป</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/'.$tpid.'/info.delete',array('confirm'=>'no')).'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			)
		);
		$form->addText('<strong style="color:red; font-size: 1.2em;">คำเตือน : หลังจากที่ลบโครงการนี้แล้ว ข้อมูลทุกอย่างเกี่ยวกับ "พัฒนาโครงการ" และ "ติดตามประเมินผลโครงการ" รวมทั้งไฟล์เอกสารและไฟล์ภาพทั้งหมดจะถูกลบทิ้ง และจะไม่สามารถเรียกคืนข้อมูลกลับมาได้อีกต่อไป</strong>');

		$ret .= $form->build();
	}

	//$ret .= print_o(post(),'post()');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	.page.-main h3 {margin: 16px 0;}
	.form.-delete .form-item {padding: 16px; }
	</style>';
	return $ret;
}
?>