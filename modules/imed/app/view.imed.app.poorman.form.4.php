<?php
function view_imed_app_poorman_form_4($qtInfo,$action=NULL) {
	$qtref = $qtInfo->qtref;
	$getRef = post('ref');

	$isEditable=$qtInfo->RIGHT & _IS_EDITABLE;
	$isAccessable=$qtInfo->RIGHT & _IS_ACCESS;
	$isQtAdmin=$qtInfo->RIGHT & _IS_ADMIN || $qtInfo->right->right=='edit';

	//$ret.=$isQtAdmin?'isadmin':'not admin';

	//$ret.=print_o($qtInfo,'$qtInfo');
	$isActionEdit = $action == 'edit';

	$statusList = array(
		_START=>'กำลังป้อน',
		_DRAFT=>'แก้ไข',
		_WAITING=>'รอตรวจ',
		_COMPLETE=>'อนุมัติ',
		_CANCEL=>'ยกเลิก',
		_REJECT=>'ไม่ผ่าน'
	);

	$fldList = R::Model('imed.poorman.field');

	$ret.='<header class="header -qt"><h3>แบบสำรวจข้อมูลประชาชนในภาวะยากลำบากและกลุ่มเปราะบางทางสังคม ('.$statusList[$qtInfo->qtstatus].')</h3></header>';



	if ($isEditable) {
	} else if ($isAccessable) {
		// SHOW QUATATION ONLY
		$isEditable = false;
		$isActionEdit = false;
	} else {
		return '<p class="notify">ขออภัย : ข้อมูลนี้ไม่ได้อยู่ในความรับผิดชอบของท่าน</p>';
	}

	R::View('imed.toolbar',$self,'คนยากลำบาก : '.$qtInfo->tr['PSNL.FULLNAME']->value,'app.poorman');

	if ($isEditable && !$isActionEdit) {
		$ret .= '<div class="btn-floating -poorman-app -right-bottom">';
		if ($getRef == 'imed') {
			$ret .= '<a class="sg-action btn -floating -circle48" href="'.url('imed/app/poorman/form/'.$qtInfo->qtref.'/edit', array('ref' => $getRef)).'" data-rel="#imed-app"><i class="icon -edit -white"></i></a>';
		} else {
			$ret .= '<a class="btn -floating -circle48" href="'.url('imed/app/poorman/form/'.$qtInfo->qtref.'/edit', array('ref' => $getRef)).'"><i class="icon -edit -white"></i></a>';
		}
		$ret.='</div>';
	}

	$form = new Form('data',url('imed/app/poorman/form/'.$qtref),'imed-poorman-form','sg-form imed-poorman-form');
	$form->addData('rel', 'none');
	if ($getRef == 'imed') {
		$form->addData('done', 'moveto:0,0 | load:#imed-app:'.url('imed/poorman/info/'.$qtInfo->psnid));
	} else if ($getRef == 'imed.app') {
		$form->addData('done', 'moveto:0,0 | load:#main:'.url('imed/app/'.$qtInfo->psnid.'/info.poorqts'));
	} else {
		$form->addData('done', 'reload:'.url('imed/app/poorman/list'));
	}
	$form->addConfig('enctype','multipart/form-data');
	if (!$isActionEdit) $form->addConfig('readonly',true);
	$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$qtInfo->psnid));
	$form->addField('qtref',array('type'=>'hidden','id'=>'qtref','value'=>$qtInfo->qtref));
	$form->addField('seq',array('type'=>'hidden','id'=>'seq','value'=>$qtInfo->seq));

	$form->addField('h1','<div style="margin:16px 0 16px auto;padding:8px;border:1px #ccc solid;background:#f0f0f0;text-align:right;width:17em;white-space:nowrap;">ลำดับที่การเก็บข้อมูล <input id="qtrefno" class="form-text" type="text" style="width:6em;text-align:center;" value="'.SG\getFirst($qtInfo->qtrefno,'????/'.(date('Y')+543)).'" readonly="readonly" /></div>');

	$form->addField('h2','<h3>ข้อมูลทั่วไป</h3>');

	$optionsName=array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว');
	if ($qtInfo->tr['PSNL.PRENAME']->value && !in_array($qtInfo->tr['PSNL.PRENAME']->value, $optionsName)) {
		$optionsName[$qtInfo->tr['PSNL.PRENAME']->value]=$qtInfo->tr['PSNL.PRENAME']->value;
	}
	$optionsName['อื่นๆ']='อื่นๆ';

	$form->addField('qt:PSNL.PRENAME',
		array(
			'type'=>'select',
			'label'=>'คำนำหน้าชื่อ :',
			'class'=>'-fill',
			'options'=>$optionsName,
			'value'=>$qtInfo->tr['PSNL.PRENAME']->value,
			'posttext'=>'<input class="form-text -fill -hidden" type="text" name="data[prename-other]" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
		)
	);

	$form->addField('qt:PSNL.FULLNAME',
		array(
			'type'=>'text',
			'label'=>'ชื่อ - นามสกุล',
			'class'=>$qtInfo->psnid?'-fill':'sg-autocomplete -fill',
			'placeholder'=>'ป้อนชื่อ นามสกุล หรือ เลข 13 หลัก',
			'value'=>$qtInfo->tr['PSNL.FULLNAME']->value,
			'attr'=>array(
				'data-query'=>url('imed/api/person'),
				'data-altfld'=>'psnid',
				'data-callback'=>'imedAppPoormanGetPerson',
			),
		)
	);

	$form->addField('qt:PSNL.CID',
		array(
			'type'=>'text',
			'label'=>'เลขที่บัตรประจำตัวประชาชน',
			'class'=>'-fill',
			'maxlength'=>13,
			'value'=>$qtInfo->tr['PSNL.CID']->value,
		)
	);

	$form->addField('qt:PSNL.NOIDCARD',
		array(
			'type'=>'text',
			'label'=>'กรณีไม่มีบัตรประจำตัวประชาชนเนื่องจาก',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.NOIDCARD']->value,
		)
	);

	$form->addField('birth',
		array(
			'type'=>'date',
			'label'=>'วันเกิด (วันที่/เดือน/ปี พ.ศ.) :',
			'year'=>(object)array('range'=>'0,110,DESC','type'=>'BC'),
			'value'=>(object)array(
				'date'=>$qtInfo->tr['PSNL.BIRTH.DATE'],
				'month'=>$qtInfo->tr['PSNL.BIRTH.MONTH'],
				'year'=>$qtInfo->tr['PSNL.BIRTH.YEAR']
			),
			'posttext'=>' อายุ <span id="age">'.($qtInfo->tr['PSNL.BIRTH.YEAR']?date('Y')-$qtInfo->tr['PSNL.BIRTH.YEAR']:'??').'</span> ปี',
		)
	);

	$form->addField('qt:PSNL.SEX',
		array(
			'type'=>'radio',
			'label'=>'เพศ :',
			'options'=>array('ชาย'=>'ชาย','หญิง'=>'หญิง'),
			'value'=>$qtInfo->tr['PSNL.SEX']->value,
		)
	);

	$form->addField('qt:PSNL.RACE',
		array(
			'type'=>'text',
			'label'=>'เชื้อชาติ',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.RACE']->value,
		)
	);

	$form->addField('qt:PSNL.NATION',
		array(
			'type'=>'text',
			'label'=>'สัญชาติ',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.NATION']->value,
		)
	);

	$form->addField('qt:PSNL.RELIGION',
		array(
			'type'=>'text',
			'label'=>'ศาสนา',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.RELIGION']->value,
		)
	);

	$form->addField('qt:PSNL.MARRIED',
		array(
			'type'=>'radio',
			'label'=>'สถานภาพ :',
			'options'=>array('โสด'=>'โสด','สมรสอยู่ด้วยกัน'=>'สมรสอยู่ด้วยกัน','สมรสแยกกันอยู่'=>'สมรสแยกกันอยู่','หย่าร้าง'=>'หย่าร้าง','ไม่ได้สมรสแต่อยู่ด้วยกัน'=>'ไม่ได้สมรสแต่อยู่ด้วยกัน','หม้าย (คู่สมรสเสียชีวิต)'=>'หม้าย (คู่สมรสเสียชีวิต)'),
			'value'=>$qtInfo->tr['PSNL.MARRIED']->value,
		)
	);

	$form->addField('t2_s','<p><b>ที่อยู่ตามทะเบียนบ้าน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField('qt:PSNL.REGIST.PLACENAME',
		array(
			'type'=>'text',
			'label'=>'ชื่อชุมชน/บ้าน',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.REGIST.PLACENAME']->value,
		)
	);

	$form->addField('qt:PSNL.REGIST.HOUSEID',
		array(
			'type'=>'text',
			'label'=>'รหัสประจำบ้าน',
			'class'=>'-fill',
			'maxlength'=>11,
			'value'=>$qtInfo->tr['PSNL.REGIST.HOUSEID']->value,
		)
	);

	$form->addField('qt:PSNL.REGIST.ADDRESS',
		array(
			'type'=>'text',
			'label'=>'ที่อยู่ตามทะเบียนบ้าน',
			'class'=>'sg-address -fill',
			'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
			'value'=>$qtInfo->tr['PSNL.REGIST.ADDRESS']->value,
			'attr'=>array('data-altfld'=>'PSNL_REGIST_AREACODE')
		)
	);

	$form->addField('qt:PSNL.REGIST.AREACODE',
		array(
			'type'=>'hidden',
			'id'=>'PSNL_REGIST_AREACODE',
			'value'=>$qtInfo->tr['PSNL.REGIST.AREACODE']->value,
		)
	);

	$form->addField('qt:PSNL.REGIST.ZIP',
		array(
			'type'=>'text',
			'label'=>'รหัสไปรษณีย์',
			'class'=>'-fill',
			'maxlength'=>5,
			'value'=>$qtInfo->tr['PSNL.REGIST.ZIP']->value,
		)
	);

	$form->addField('qt:PSNL.REGIST.PHONE',
		array(
			'type'=>'text',
			'label'=>'เบอร์โทรศัพท์บ้าน',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['PSNL.REGIST.PHONE']->value,
		)
	);

	$form->addField('qt:PSNL.REGIST.MOBILE',
		array(
			'type'=>'text',
			'label'=>'เบอร์มือถือ',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['PSNL.REGIST.MOBILE']->value,
		)
	);

	$form->addField('t2_e','</div>');

	$form->addField('t3_s','<p><b>ที่อยู่ปัจจุบัน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField('qt:PSNL.HOME.STATUS',
		array(
			'type'=>'radio',
			'label'=>'สถานะของที่พักอาศัย :',
			'options'=>array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง'),
			'value'=>$qtInfo->tr['PSNL.HOME.STATUS']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.NOTSAMEADDRESS',
		array(
			'type'=>'checkbox',
			'label'=>'ที่อยู่ปัจจุบันกับที่อยู่ตามทะเบียนบ้าน',
			'options'=>array(1=>'ที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน'),
			'value'=>$qtInfo->tr['PSNL.HOME.NOTSAMEADDRESS']->value,
		)
	);

	$form->addField('t1_s','<div id="imed-poorman-form-regishome" class="'.($qtInfo->tr['PSNL.HOME.NOTSAMEADDRESS']->value?'':'hidden').'">กรณีที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน (ระบุรายละเอียดเพิ่มเติมด้านล่าง)');

	$form->addField('qt:PSNL.HOME.PLACENAME',
		array(
			'type'=>'text',
			'label'=>'ชื่อชุมชน/บ้าน',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.HOME.PLACENAME']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.HOUSEID',
		array(
			'type'=>'text',
			'label'=>'รหัสประจำบ้าน',
			'class'=>'-fill',
			'maxlength'=>11,
			'value'=>$qtInfo->tr['PSNL.HOME.HOUSEID']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.ADDRESS',
		array(
			'type'=>'text',
			'label'=>'ที่อยู่ปัจจุบัน',
			'class'=>'sg-address -fill',
			'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
			'value'=>$qtInfo->tr['PSNL.HOME.ADDRESS']->value,
			'attr'=>array('data-altfld'=>'PSNL_HOME_AREACODE')
		)
	);

	$form->addField('qt:PSNL.HOME.AREACODE',
		array(
			'type'=>'hidden',
			'id'=>'PSNL_HOME_AREACODE',
			'value'=>$qtInfo->tr['PSNL.HOME.AREACODE']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.ZIP',
		array(
			'type'=>'text',
			'label'=>'รหัสไปรษณีย์',
			'class'=>'-fill',
			'maxlength'=>5,
			'value'=>$qtInfo->tr['PSNL.HOME.ZIP']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.PHONE',
		array(
			'type'=>'text',
			'label'=>'เบอร์โทรศัพท์บ้าน',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['PSNL.HOME.PHONE']->value,
		)
	);

	$form->addField('qt:PSNL.HOME.MOBILE',
		array(
			'type'=>'text',
			'label'=>'เบอร์มือถือ',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['PSNL.HOME.MOBILE']->value,
		)
	);

	$form->addField('t1_e','</div>');
	$form->addField('t3_e','</div>');

	$form->addField('qt:PSNL.EDUCA',
		array(
			'type'=>'radio',
			'label'=>'ระดับการศึกษา :',
			'options'=>array(1=>'ไม่ได้รับการศึกษา/ไม่จบชั้นประถมศึกษาตอนต้น','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษาตอนต้น','มัธยมศึกษาตอนปลาย','ปวช./อนุปริญญา','ปริญญาตรี','สูงกว่าปริญญาตรี'),
			'value'=>$qtInfo->tr['PSNL.EDUCA']->value,
		)
	);

	$form->addField('qt:PSNL.OCCUPA',
		array(
			'type'=>'radio',
			'label'=>'อาชีพ :',
			'options'=>array(1=>'ไม่มีอาชีพ/ว่างงาน','นักเรียน/นักศึกษา','ค้าขาย/ธุรกิจส่วนตัว','ภิกษุ/สามเณร/แม่ชี','เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)','ข้าราชการ/พนักงานของรัฐ','พนักงานรัฐวิสาหกิจ','พนักงานบริษัท','รับจ้าง',99=>'อื่น ๆ : ระบุรายละเอียดอาชีพด้านล่าง'),
			'value'=>$qtInfo->tr['PSNL.OCCUPA']->value,
		)
	);

	$form->addField('qt:ECON.OCCUPY.DETAIL',
		array(
			'type'=>'text',
			'label'=>'รายละเอียดอาชีพ',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['ECON.OCCUPY.DETAIL']->value,
		)
	);

	$form->addField('qt:ECON.INCOME.MONTHLY.AMT',
		array(
			'type'=>'text',
			'label'=>'รายได้เฉลี่ยต่อเดือน',
			'class'=>'',
			'posttext'=>' บาท',
			'value'=>$qtInfo->tr['ECON.INCOME.MONTHLY.AMT']->value,
		)
	);

	$form->addField('qt:ECON.INCOME.FROM',
		array(
			'type'=>'text',
			'label'=>'ที่มาของรายได้',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['ECON.INCOME.FROM']->value,
		)
	);

	$form->addField('qt:PSNL.INFO.BYTYPE',
		array(
			'type'=>'radio',
			'label'=>'ผู้ให้ข้อมูล :',
			'options'=>array(1=>'เป็นผู้ให้เอง','ผู้อื่นให้'),
			'value'=>$qtInfo->tr['PSNL.INFO.BYTYPE']->value,
		)
	);

	$form->addField('qt:PSNL.INFO.BYNAME',
		array(
			'type'=>'text',
			'label'=>'ชื่อผู้ให้ข้อมูล',
			'containerclass'=>'-last',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['PSNL.INFO.BYNAME']->value,
		)
	);



	$form->addField('h3','<h3>สภาวะความยากลำบาก และ เปราะบางทางสังคม</h3>');

	foreach ($fldList as $key => $item) {
		if (preg_match('/^(POOR.TYPE.LIST.)(.*)/i',$key,$out)) {
			$typeKey = $out[2];
			$poorType[$typeKey] = $item;
		}
	}
	//$ret .= print_o($poorType,'$poorType');

	$form->addField('qt:POOR.TYPE.LIST.',
		array(
			'type'=>'checkbox',
			'label'=>'1. ประเภทของสภาวะความยากลำบากและกลุ่มเปราะบางทางสังคม :',
			'containerclass'=>'-bigheader -first',
			'options'=>$poorType,
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.TYPE.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('poor1photo','ภาพถ่ายบุคคลพร้อมคำบรรยาย (ถ้ามี) <ul class="card -photo -sg-clearfix">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorperson').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorperson" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');


	$form->addField('qt:POOR.CAUSE.LIST.',
		array(
			'type'=>'checkbox',
			'label'=>'2. สาเหตุของความยากลำบาก :',
			'containerclass'=>'-bigheader',
			'options'=>array(1=>'1. ยากจน / รายได้น้อย','2. มีหนี้สิน','3. ตกงาน / ไม่มีงานทำ / ไม่มีอาชีพ','4. ขาดผู้อุปการะ','5. ขาดความรู้ที่จะประกอบอาชีพ','6. ปัญหาครอบครัว','7. ไม่มีที่อยู่อาศัย / ไม่มีที่ดินทำกิน','8. ถูกชักจูงโดยคนรู้จัก / เพื่อน','9. ถูกบังคับ / ล่อลวง / แสวงหาผลประโยชน์','10. ไม่มีสถานะทางทะเบียนราษฎร์','11. ขาดโอกาสทางการศึกษาตามเกณฑ์','12. เจ็บป่วยเรื้อรัง','13. ช่วยเหลือตนเองไม่ได้ในชีวิตประจำวัน',99=>'14. อื่น ๆ : ระบุรายละเอียดด้านล่าง'),
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.CAUSE.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('qt:POOR.CAUSE.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียดสาเหตุความยากลำบาก',
			'class'=>'-fill',
			'rows'=>3,
			'value'=>$qtInfo->tr['POOR.CAUSE.DETAIL']->value,
		)
	);

	$disabledList=array(1=>'ทางการเห็น','ทางการได้ยินหรือสื่อความหมาย','ทางการเคลื่อนไหวหรือทางร่างกาย','ทางจิตใจหรือพฤติกรรม','ทางสติปัญญา','ทางการเรียนรู้','ทางออทิสติก');
	foreach ($disabledList as $key => $value) {
		$disabledType.='<label class="option" style="display:block;">'
			.'<input name="data[qt:POOR.HEALTH.DISABLED.'.$key.']" value="'.$key.'" '
			.($qtInfo->tr['POOR.HEALTH.DISABLED.'.$key]->value==$key?'checked="checked"':'')
			.' class="form-checkbox" type="checkbox" /> '.$value
			.'</label>';
	}


	$form->addField('qt:POOR.HEALTH.LIST.',
		array(
			'type'=>'checkbox',
			'label'=>'3. สถานะทางสุขภาพในปัจจุบัน :',
			'containerclass'=>'-bigheader',
			'options'=>array(
				1=>'1. ปกติ',
				'2. ผู้สูงอายุ',
				'3. เจ็บป่วย',
				'4. พิการ<br />'.$disabledType,
				99=>'5. อื่น ๆ'
			),
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.HEALTH.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('qt:POOR.HEALTH.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียดสถานะทางสุขภาพในปัจจุบัน',
			'class'=>'-fill',
			'rows'=>3,
			'value'=>$qtInfo->tr['POOR.HEALTH.DETAIL']->value,
		)
	);

	$form->addField('qt:POOR.NEED.GOV.LIST.',
		array(
			'type'=>'checkbox',
			'label'=>'4. สิ่งที่ต้องการให้รัฐช่วยเหลือ',
			'containerclass'=>'-bigheader',
			'options'=>array(1=>'1. เข้าสถานสงเคราะห์','2. กลับภูมิลำเนา','3. ฝึกอาชีพ','4. หางานทำ','5. ที่พักชั่วคราว','6. เงินทุนประกอบอาชีพ','7. เงินสงเคราะห์ช่วยเหลือ','8. รักษาพยาบาล','9. ทำบัตรประชาชน',99=>'10. อื่น ๆ'),
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.NEED.GOV.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('qt:POOR.NEED.GOV.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียดสิ่งที่ต้องการให้รัฐช่วยเหลือ',
			'class'=>'-fill',
			'rows'=>3,
			'value'=>$qtInfo->tr['POOR.NEED.GOV.DETAIL']->value,
		)
	);

	$form->addField('poor4photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี)  <ul class="card -photo -sg-clearfix">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorgvneed').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorgvneed" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');

	$form->addField('qt:POOR.HELP.ORG.YES',
		array(
			'type'=>'radio',
			'label'=>'5. เคยได้รับความช่วยเหลือจากหน่วยงานใดบ้าง',
			'containerclass'=>'-bigheader',
			'options'=>array(1=>'ไม่เคยได้รับ','เคยได้รับความช่วยเหลือเป็น'),
			'value'=>$qtInfo->tr['POOR.HELP.ORG.YES']->value,
		)
	);

	$form->addField('qt:POOR.HELP.ORG.LIST.',
		array(
			'type'=>'checkbox',
			'options'=>array(1=>'บริการ','เงิน','สิ่งของ'),
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.HELP.ORG.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('qt:POOR.HELP.ORG.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียดความช่วยเหลือที่ได้รับ',
			'class'=>'-fill',
			'rows'=>3,
			'value'=>$qtInfo->tr['POOR.HELP.ORG.DETAIL']->value,
		)
	);

	$form->addField('qt:POOR.HELP.ORG.YEAR',
		array(
			'type'=>'text',
			'label'=>'เมื่อปี',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['POOR.HELP.ORG.YEAR']->value,
		)
	);

	$form->addField('qt:POOR.HELP.ORG.NAME',
		array(
			'type'=>'text',
			'label'=>'จากหน่วยงาน',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['POOR.HELP.ORG.NAME']->value,
		)
	);

	$form->addField('qt:POOR.NEED.COMMUNITY.LIST.',
		array(
			'type'=>'checkbox',
			'label'=>'6. สิ่งที่ต้องการให้ชุมชนหรือองค์กรท้องถิ่นช่วยเหลือ',
			'containerclass'=>'-bigheader',
			'options'=>array(1=>'1. ซ่อมแซมที่อยู่อาศัย','2. อาหาร','3. ฝึกอาชีพ','4. ให้งานทำ','5. ของใช้ในชีวิต','6. เครื่องนุ่งห่ม','7. เงินสงเคราะห์ช่วยเหลือ','8. วัสดุเพื่อการรักษาพยาบาล',99=>'9. อื่นๆ'),
			'separate'=>true,
			'value'=>__imed_app_poorman_form_tranvalue('POOR.NEED.COMMUNITY.LIST.',$qtInfo->tr),
		)
	);

	$form->addField('qt:POOR.NEED.COMMUNITY.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'ระบุรายละเอียดสิ่งที่ต้องการด้านล่าง',
			'class'=>'-fill',
			'rows'=>5,
			'value'=>$qtInfo->tr['POOR.NEED.COMMUNITY.DETAIL']->value,
		)
	);

	$form->addField('poor6photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี)  <ul class="card -photo -sg-clearfix">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorcmneed').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorcmneed" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');


	$form->addField('poor7.h',
		array(
			'type'=>'textfield',
			'label'=>'7. สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
			'containerclass'=>'-bigheader',
		)
	);

	$form->addField('qt:POOR.ECON.INCOME.YES',
		array(
			'type'=>'radio',
			'label'=>'รายรับ :',
			'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
			'value'=>$qtInfo->tr['POOR.ECON.INCOME.YES']->value,
		)
	);

	$form->addField('qt:POOR.ECON.EXPENSE.YES',
		array(
			'type'=>'radio',
			'label'=>'รายจ่าย :',
			'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
			'value'=>$qtInfo->tr['POOR.ECON.EXPENSE.YES']->value,
		)
	);

	$form->addField('qt:POOR.ECON.LOAN.YES',
		array(
			'type'=>'radio',
			'label'=>'หนี้สิน :',
			'options'=>array(1=>'ไม่มี',2=>'มีมากเกินรายได้',3=>'มีพอกับรายได้'),
			'value'=>$qtInfo->tr['POOR.ECON.LOAN.YES']->value,
		)
	);

	$form->addField('qt:POOR.ECON.DETAIL',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียด สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
			'class'=>'-fill',
			'rows'=>5,
			'value'=>$qtInfo->tr['POOR.ECON.DETAIL']->value,
		)
	);

	$form->addField('qt:POOR.PSNL.CARETAKER.HEADER',
		array(
			'type'=>'textfield',
			'label'=>'8. ผู้ดูแลหรือผู้ปกครอง (ในกรณีไม่สามารถดูแลตนเองได้)',
			'containerclass'=>'-bigheader',
		)
	);
	$form->addField('qt:POOR.PSNL.CARETAKER.NAME',
		array(
			'type'=>'text',
			'label'=>'ชื่อผู้ดูแลหรือผู้ปกครอง',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.NAME']->value,
		)
	);
	$form->addField('qt:POOR.PSNL.CARETAKER.RELATION',
		array(
			'type'=>'text',
			'label'=>'ความสัมพันธ์',
			'class'=>'-fill',
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.RELATION']->value,
		)
	);
	$form->addField('qt:POOR.PSNL.CARETAKER.ADDRESS',
		array(
			'type'=>'text',
			'label'=>'ที่อยู่ผู้ดูแล',
			'class'=>'sg-address -fill',
			'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.ADDRESS']->value,
		)
	);
	$form->addField('qt:POOR.PSNL.CARETAKER.ZIP',
		array(
			'type'=>'text',
			'label'=>'รหัสไปรษณีย์',
			'class'=>'-fill',
			'maxlength'=>5,
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.ZIP']->value,
		)
	);

	$form->addField('qt:POOR.PSNL.CARETAKER.PHONE',
		array(
			'type'=>'text',
			'label'=>'เบอร์โทรศัพท์บ้าน',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.PHONE']->value,
		)
	);

	$form->addField('qt:POOR.PSNL.CARETAKER.MOBILE',
		array(
			'type'=>'text',
			'label'=>'เบอร์มือถือ',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['POOR.PSNL.CARETAKER.MOBILE']->value,
		)
	);





	$form->addField('qt:POOR.PSNL.HISTORY.MORE',
		array(
			'type'=>'textarea',
			'label'=>'9. ประวัติ(เพิ่มเติม)',
			'containerclass'=>'-bigheader',
			'class'=>'-fill',
			'rows'=>5,
			'value'=>$qtInfo->tr['POOR.PSNL.HISTORY.MORE']->value,
		)
	);

	$form->addField('qt:POOR.PSNL.HISTORY.FAMILY',
		array(
			'type'=>'textarea',
			'label'=>'10. ประวัติครอบครัว',
			'containerclass'=>'-bigheader',
			'class'=>'-fill',
			'rows'=>5,
			'value'=>$qtInfo->tr['POOR.PSNL.HISTORY.FAMILY']->value,
		)
	);

	$form->addField('collectname',
		array(
			'type'=>'text',
			'label'=>'ผู้จัดเก็บข้อมูล',
			'containerclass'=>'-bigheader',
			'class'=>'-fill',
			'value'=>$qtInfo->collectname,
		)
	);

	$form->addField('qtdate',
		array(
			'type'=>'text',
			'label'=>'วันที่เก็บข้อมูล',
			'class'=>'sg-datepicker -fill',
			'readonly'=>true,
			'value'=>sg_date(SG\getFirst($qtInfo->qtdate,date('Y-m-d')),'d/m/Y'),
			'attr'=>array('data-max-date'=>date('d/m/Y')),
		)
	);
	$form->addField('qt:COLLECTOR.PHONE',
		array(
			'type'=>'text',
			'label'=>'เบอร์โทรศัพท์',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['COLLECTOR.PHONE']->value,
		)
	);
	$form->addField('qt:COLLECTOR.IDLINE',
		array(
			'type'=>'text',
			'label'=>'ID LINE',
			'class'=>'-fill',
			'maxlength'=>50,
			'value'=>$qtInfo->tr['COLLECTOR.IDLINE']->value,
		)
	);

	if ($qtInfo->qtstatus!=_COMPLETE) {
		$form->addField(
			'publish',
			array(
				'type'=>'checkbox',
				'label'=>'แจ้งสถานะการบันทึกแบบสอบถาม:',
				'name'=>'publish',
				'options'=>array(_WAITING=>'บันทึกแบบสอบถามเสร็จสมบูรณ์แล้ว'),
				'value'=>$qtInfo->qtstatus,
			)
		);
	}

	if ($isActionEdit) {
		$form->addField('save',
			array(
				'type'=>'button',
				'name'=>'save',
				'items'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
				),
				'container' => '{class: "-sg-text-right"}',
			)
		);
	}

	$ret.=$form->build();
	$ret .= '<p>บันทึกข้อมูลโดย '.$qtInfo->posterName.' เมื่อ '.sg_date($qtInfo->created,'ว ดด ปปปป H:i').' น.</p>';

	//$ret .= print_o($qtInfo);


	if ($isQtAdmin && $isActionEdit) {
		$form=new Form(NULL,url('imed/app/poorman/form/approve/'.$qtref),NULL,'sg-form imed-poorman-form-approve');
		$form->addData('checkValid',true);
		$form->addData('rel', 'none');

		if ($getRef == 'imed') {
			$form->addData('done', 'moveto:0,0 | load:#imed-app:'.url('imed/poorman/info/'.$qtInfo->psnid));
		} else if ($getRef == 'imed.app') {
			$form->addData('done', 'moveto:0,0 | load:#main:'.url('imed/app/'.$qtInfo->psnid.'/info.poorqts'));
		} else {
			$form->addData('done', 'reload:'.url('imed/app/poorman/list'));
		}

		$form->addField('h3','<h3>ผู้ตรวจสอบข้อมูล</h3>');

		$form->addField('qtid',array('type'=>'hidden','name'=>'qtid','value'=>$qtInfo->tr['APPROVE.REMARK']->qtid));

		$form->addField(
			'approve',
			array(
				'type'=>'radio',
				'label'=>'ผลการตรวจสอบความถูกต้องของข้อมูลในแบบสอบถาม:',
				'name'=>'approve',
				'require'=>true,
				'options'=>array(
					_REJECT=>'ไม่ผ่าน',
					_CANCEL=>'ยกเลิก',
					_DRAFT=>'ให้แก้ไขใหม่',
					_COMPLETE=>'อนุมัติ - ตรวจสอบความถูกต้องของข้อมูลในแบบสอบถามเสร็จสมบูรณ์แล้ว'
				),
				'value'=>$qtInfo->qtstatus,
			)
		);

		$form->addField('approveremark',
			array(
				'type'=>'textarea',
				'label'=>'คำอธิบาย',
				'name'=>'approveremark',
				'class'=>'-fill',
				'rows'=>5,
				'value'=>$qtInfo->tr['APPROVE.REMARK']->value,
			)
		);

		$form->addField('save',
			array(
				'type'=>'button',
				'name'=>'save',
				'items'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -save -white"></i><span>{tr:ดำเนินการเปลี่ยนสถานะแบบสอบถาม}</span>',
				),
				'container' => '{class: "-sg-text-right"}',
			)
		);

		$ret.=$form->build();
	}

	//$ret.='<style type="text/css">.btn-floating.-right-bottom .btn {bottom:72px;}</style>';
	return $ret;
}
?>