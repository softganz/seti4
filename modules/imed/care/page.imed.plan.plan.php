<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String

* Right of Care Plan
* - Creater can do everything
* - Group Admin / Web Admin can do everything
* - CG of Care Plan can add/edit/delete plan item
* - Member of group can view everything
*/

$debug = true;

function imed_care_plan($self, $psnId, $planId) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	$careInfo = R::Model('imed.care.get', $planId);
	$orgInfo = R::Model('imed.social.get', $careInfo->orgid, '{data: "info"}');

	$right = R::Model('imed.care.plan.right',$careInfo, $orgInfo);
	$isEdit = $right->RIGHT & _IS_EDITABLE;
	$isDelete = $right->RIGHT & _IS_DELETABLE;
	$isEditTran = $right->is->tran;
	//$ret .= print_o($right, '$right');

	$ui = new Ui();
	$dropUi = new Ui();

	$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/plan/'.$planId).'" data-rel="#imed-app"><i class="icon -material">assignment</i></a>');

	if ($isEdit) {
		$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/plan.edit/'.$planId).'" data-rel="#imed-care"><i class="icon -material">edit</i></a>');
	}
	if ($isDelete) {
		$dropUi->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/delete/'.$planId).'" data-rel="#imed-app" data-ret="'.url('imed/care/'.$psnId, array('org'=>$careInfo->orgid)).'" data-title="ลบ Care Plan" data-confirm="ต้องการลบ Care Plan กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบ Care Plan</span></a>');
	}

	if ($dropUi->count()) $ui->add(sg_dropbox($dropUi->build()));

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.url('imed/care/'.$psnId, array('org'=>$careInfo->orgid)).'" data-rel="#imed-app"><i class="icon -material">arrow_back</i></a></nav><h3>Care Plan @'.$psnInfo->realname.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$ret .= '<div id="imed-care">';

	$ui = new Ui('div','ui-card plandetail -card -sg-flex -co-3 -sg-padding-16');
	$ui->add('<b>ชื่อ</b> '.$psnInfo->fullname.'<br />อายุ '.($psnInfo->info->birth ? date('Y') - sg_date($psnInfo->info->birth,'Y') : '-').' ปี<br />เลขบัตรประชาชน '.$psnInfo->info->cid.'<br />ที่อยู่ '.$psnInfo->info->address.'<br />โทรศัพท์ '.$psnInfo->info->phone);
	$ui->add('<b>การวินิจฉัยโรค</b> '.$careInfo->info->diagnose.'<br />'
				. '<b>TAI</b> '.$careInfo->info->tai.' <b>ADL</b> '.$careInfo->info->adl.' คะแนน<br />'
				. '<b>วันที่จัดทำ</b> '.sg_date($careInfo->info->datemake, 'd/m/Y').'<br />'
				. '<b>จัดทำโดย </b>'.$careInfo->info->ownerName.'<br />'
			);
	$ui->add('งบประมาณ');
	$ui->add('<b>แนวคิดของผู้รับบริการและครอบครัวที่มีต่อการดำรงชีวิต</b><br />'.nl2br($careInfo->info->conceptlive).'<br />');
	$ui->add('<b>แนวนโยบายการให้ความช่วยเหลือโดยรวม (รวมถึงเป้าหมายระยะยาว)</b><br />'.nl2br($careInfo->info->conceptlong).'<br />');
	$ui->add('<b>ข้อควรระวัง</b><br />'.nl2br($careInfo->info->caution).'<br />');
	$ui->add('<b>ประเด็นปัญหาในการดำรงชีวิต (ความต้องการ)</b><br />'.nl2br($careInfo->info->problem).'<br />');
	$ui->add('<b>เป้าหมายการดำรงชีวิต (เป้าหมายระยะสั้น)</b><br />'.nl2br($careInfo->info->targetshort).'<br />');
	$ui->add('<b>บริการที่นอกเหนือจากรายสัปดาห์ (รวมการช่วยเหลือของครอบครัวและ informal)</b><br />'.nl2br($careInfo->info->servicewant).'<br />');
	$ret .= $ui->build();

	$ret .= R::View('imed.care.plan.tran', $psnInfo, $careInfo, $orgInfo);
	//$ret .= R::Page('imed.care.calendar',NULL, $psnId);

	if ($isEdit || $isEditTran) {
		$ret .= '<nav class="nav -page -sg-text-right" style="padding: 16px;"><a class="sg-action btn -primary" href="'.url('imed/care/'.$psnId.'/plan.tran.add/'.$planId).'" data-rel="box" data-width="600" max-data-height="80%" data-webview="false" data-webview-title="Add Care Plan Tran"><i class="icon -material">add_circle</i><span>เพิ่มรายการแผนการดูแล</span></a></nav>';
	}

	//$ret .= print_o($psnInfo, '$psnInfo');
	//$ret .= print_o($careInfo, '$careInfo');
	//$ret .= print_o($orgInfo, '$orgInfo');

	$ret .= '<!-- imed-care --></div>';

	return $ret;
}
?>