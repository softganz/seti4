<?php
/**
* Vew personal health information
*
* @param Integer $psnId
* @return String
*/
function imed_patient_health($self, $psnId = NULL) {
	// Data Model
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error',$psnInfo->error);



	// View Model
	if (post('type')=='short') {
		$ret .= '<div class="popup-profile"><a href="'.url('imed', ['pid' => $psnId]).'" role="patient" data-pid="'.$psnId.'" tooltip-uri="'.url('imed/patient/individual/'.$psnId,'type=short').'"><img src="'.imed_model::patient_photo($psnId).'" class="disabled-info-photo" /><span class="name">'.$psnInfo->fullname.'</span></a><span class="address">ที่อยู่ '.$psnInfo->info->address.'</span></div>';
		return $ret;
	}

	//$ret .= print_o($psnInfo,'$psnInfo');

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('imed/edit/patient');
		$inlineAttr['data-psnid'] = $psnId;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	include_once 'modules/imed/assets/qt.individual.php';

	$ret .= '<div id="imed-care-individual" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->id='imed-patient-individual';
	$tables->caption='ข้อมูลสุขภาพ';




	$tables->rows[] = array('<b>ADL</b>', $psnInfo->info->barthelLevel ? '<i class="icon -local -barthel'.$psnInfo->info->barthelLevel.'"></i><strong> Barthel ADL Index = '.$psnInfo->info->adl . ' '. $psnInfo->info->barthelLevelStr .'</strong>' : 'ไม่ระบุ');

//โรคประจำตัว
	$tables->rows[] = array(
		'โรคประจำตัว',
		imed_model::qt('HLTH.2.4',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'โรคประจำตัว',
		'<p><strong>โรคเรื้อรัง</strong></p>'
		.imed_model::qt('โรคประจำตัว-ความดันโลหิตสูง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-เบาหวาน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-ไขมันในเลือดสูง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคหัวใจ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคปอด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคถุงลมโป่งพอง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-ไตวาย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-มะเร็ง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-ข้ออักเสบ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-เก๊าท์',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-รูมาตอยด์',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคลมชัก',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคเส้นเลือดสมองตีบ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-พาร์กินสัน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-อัลไซเมอร์',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-จิตเวช',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคอ้วน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคสมองและหลอดเลือด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL

		/*
		.imed_model::qt('โรคประจำตัว-โรคทางระบบหัวใจและหลอดเลือด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคทางระบบหลอดเลือดสมอง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคระบบกระดูกและข้อ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคระบบทางเดินอาหารและช่องท้อง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคทางเดินปัสสาวะ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคทางตา',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคออโตอิมมูน',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคระบบทางเดินหายใจ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคระบบต่อมไร้ท่อ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-โรคทางระบบผิวหนัง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('โรคประจำตัว-อื่นๆ',$qt,$psnInfo->qt,$isEdit)._NL
		*/
		.imed_model::qt(
			'โรคประจำตัว-อื่นๆ',$qt,$psnInfo->qt,$isEdit)
		.'<p><strong>ภาวะแทรกซ้อน</strong></p>'
		.imed_model::qt('ภาวะแทรกซ้อน-แผลกดทับ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('ภาวะแทรกซ้อน-ข้อติดแข็ง',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('ภาวะแทรกซ้อน-กล้ามเนื้อเกร็งหรือกระตุก',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('ภาวะแทรกซ้อน-อื่นๆ',$qt,$psnInfo->qt,$isEdit)._NL
		.imed_model::qt('ภาวะแทรกซ้อน-ระบุ',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	//การเข้าถึงสิทธิ์
	$tables->rows[] = array(
		'สิทธิในการรับการรักษาพยาบาล',
		imed_model::qt('PSNL.1.10.1',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('PSNL.1.10.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.'รายละเอียดสิทธิ์ '.'<br />'._NL
		.imed_model::qt('PSNL.1.10.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.RIGHT.OFFICE',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.RIGHT.SOCIALSECURITY.NO',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('PSNL.RIGHT.EMPTY.CAUSE',$qt,$psnInfo->qt,$isEdit)
	);

	if ($psnInfo->disabled->healthright) $tables->rows[] = array('การเข้าถึงสิทธิ์',$psnInfo->disabled->healthright); // view::show_field(array('group'=>'disabled','fld'=>'healthright','button'=>$button),$psnInfo->disabled->healthright,$isEdit,'select','ระบบหลักประกันสุขภาพ(ท.74),ประกันสังคม,ข้าราชการ,พนักงานรัฐวิสาหกิจ,สิทธิว่าง,สิทธิหลักประกันสุขภาพถ้านหน้า ระบุ ท,สิทธิอื่น ๆ')

	$tables->rows[] = array(
		'ประวัติการรักษาพยาบาล',
		imed_model::qt('HLTH.2.5.1',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('HLTH.2.5.1.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'สถานที่รับการรักษา ',
		imed_model::qt('HLTH.2.5.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'
		.imed_model::qt('HLTH.2.5.2.2',$qt,$psnInfo->qt,$isEdit).'<br />'
	);

	$tables->rows[] = array(
		'การรักษาต่อเนื่อง',
		imed_model::qt('HLTH.2.5.3',$qt,$psnInfo->qt,$isEdit)
	);
	$tables->rows[] = array(
		'ประวัติการแพ้ยา',
		imed_model::qt('HLTH.2.5.4',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('HLTH.2.5.4.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array(
		'ประวัติการแพ้อาหาร',
		imed_model::qt('HLTH.2.5.5',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('HLTH.2.5.5.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array('<th colspan="2">การช่วยเหลือตนเองในกิจวัตรประจำวัน</th>');

	$tables->rows[] = array(
		'สุขอนามัยส่วนตัวคนพิการ',
		imed_model::qt('OTHR.5.8.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.8.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'สภาพสิ่งแวดล้อมในบ้าน',
		imed_model::qt('OTHR.5.8.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.8.3.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'ความปลอดภัยของที่อยู่อาศัย',
		imed_model::qt('HLTH.ความปลอดภัยของที่อยู่อาศัย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('HLTH.ความปลอดภัยของที่อยู่อาศัย.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'ความมั่นคงของที่อยู่อาศัย',
		imed_model::qt('HLTH.ความมั่นคงของที่อยู่อาศัย',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('HLTH.ความมั่นคงของที่อยู่อาศัย.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน',
		imed_model::qt('HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน',$qt,$psnInfo->qt,$isEdit)._NL
		.imed_model::qt('HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน.รายละเอียด',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'การรับบริการด้านสุขภาพ',
		imed_model::qt('OTHR.5.2.5',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.4',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.2',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.3',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.1',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.6',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
		.imed_model::qt('OTHR.5.2.6.VISIT',$qt,$psnInfo->qt,$isEdit).'<br />'._NL
	);

	$tables->rows[] = array(
		'อุปกรณ์ความช่วยเหลือ',
		imed_model::qt('HLTH.PROSTHETIC',$qt,$psnInfo->qt,$isEdit)._NL
	);

	$ret .= $tables->build();




	$ret .= R::Page('imed.patient.po', NULL, $psnInfo);

	$ret .= '<div class="-sg-text-right"><a class="sg-action btn -link" href="'.url('imed/patient/po/'.$psnId.'/add').'" data-rel="box" data-width="480" data-max-height="80%"><i class="icon -material">add_circle_outline</i><span>เพิ่มกายอุปกรณ์</span></a></div>';
	$ret .= '<style type="text/css">
	.btn-floating.-po-add {display: none;}
	</style>';



	unset($tables->rows,$tables->caption);

	$tables->rows[] = array(
		'การฟื้นฟูสมรรถภาพ',
		imed_model::qt('DSBL.3.3',$qt,$psnInfo->qt,$isEdit)
		.imed_model::qt('DSBL.3.3.1',$qt,$psnInfo->qt,$isEdit)
	);

	$tables->rows[] = array('ปัญหาด้านสุขภาพที่ต้องการให้แก้ไข',
		imed_model::qt('problem',$qt,$psnInfo->disabled->problem,$isEdit).'<br />'.'กรุณาระบุรายการละ 1 บรรทัด'
	);

	$tables->rows[] = array(
		'ความต้องการ',
		imed_model::qt('DSBL.3.7',$qt,$psnInfo->qt,$isEdit).'<br />'.'กรุณาระบุรายการละ 1 บรรทัด'
	);

	$ret .= $tables->build();

	$ret .= '</div>';

	//$ret.=print_o($psnInfo,'$psnInfo');

	$ret .= '<p><small>สร้างโดย '.$psnInfo->info->created_by.' เมื่อ '.sg_date($psnInfo->info->created_date,'ว ดด ปปปป H:i').($psnInfo->info->modify?' แก้ไขล่าสุดโดย '.$psnInfo->info->modify_by.' เมื่อ '.sg_date($psnInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small></p>';

	return $ret;
}
?>