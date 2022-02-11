<?php
function r_project_paiddoc_canCreate($projectInfo, $fundInfo) {
	$error = array();

	if (!$fundInfo->hasInitAccount) {
		$error[] = 'กองทุนยังไม่มีการกำหนดข้อมูลบัญชี-การเงิน<br /><br /><nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/fund/'.$fundInfo->orgid.'/info.finance').'" data-rel="box" data-width="640">กำหนดข้อมูลบัญชี-การเงิน</a></nav>';
	}

	if (empty($projectInfo->info->supporttype)) {
		$error[]='คำเตือน : โครงการยังไม่ได้กำหนด <b>"ประเภทการสนับสนุน"</b><br />กรุณาคลิกที่ <a href="'.url('project/'.$tpid).'">รายละเอียดโครงการ</a> แล้วกำหนดประเภทการสนับสนุนให้เรียบร้อยก่อน จึงจะสามารถบันทึกใบเบิกเงินได้นะคะ';
	}

	if ($projectInfo->info->budget <= 0) {
		$error[]='คำเตือน : โครงการยังไม่ได้กำหนด <b>"งบประมาณ"</b><br />กรุณาคลิกที่ <a href="'.url('project/'.$tpid).'">รายละเอียดโครงการ</a> แล้วกำหนดงบประมาณให้เรียบร้อยก่อน จึงจะสามารถบันทึกใบเบิกเงินได้นะคะ';
	}

	if ($error) return $error;
}
?>