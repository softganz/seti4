<?php
function view_imed_app_poorman_form_5($qtInfo) {
	$qtref=$qtInfo->qtref;
	$isEditable=$qtInfo->RIGHT & _IS_EDITABLE;
	$isAccessable=$qtInfo->RIGHT & _IS_ACCESS;
	$isQtAdmin=$qtInfo->RIGHT & _IS_ADMIN || $qtInfo->right->right=='edit';

	$ret.='<h3 class="header -sub">แบบสำรวจข้อมูลประชาชนในภาวะยากไร้ของ จปฐ.</h3>';



	if ($isEditable) {
	} else 	if ($isAccessable) {
		return 'SHOW QUATATION ONLY';
	} else {
		return '<p class="notify">ขออภัย : ข้อมูลนี้ไม่ได้อยู่ในความรับผิดชอบของท่าน</p>';
	}

	R::View('imed.toolbar',$self,'คนยากลำบาก : '.$qtInfo->tr['PSNL.FULLNAME']->value,'app.poorman');

	$form=new Form('data',url('imed/app/poorman/form/'.$qtref),'imed-poorman-form','imed-poorman-form');
	$form->addConfig('enctype','multipart/form-data');
	$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$qtInfo->psnid));
	$form->addField('qtref',array('type'=>'hidden','id'=>'qtref','value'=>$qtInfo->qtref));
	$form->addField('seq',array('type'=>'hidden','id'=>'seq','value'=>$qtInfo->seq));

	$form->addField('h1','<div style="margin:0 0 16px auto;padding:8px;border:1px #ccc solid;background:#f0f0f0;text-align:right;width:17em;white-space:nowrap;">ลำดับที่การเก็บข้อมูล <input id="qtrefno" class="form-text" type="text" style="width:6em;text-align:center;" value="'.SG\getFirst($qtInfo->qtrefno,'????/'.(date('Y')+543)).'" readonly="readonly" /></div>');

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

	$form->addField('qt:PSNL.SEX',
						array(
							'type'=>'radio',
							'label'=>'เพศ :',
							'options'=>array('ชาย'=>'ชาย','หญิง'=>'หญิง'),
							'value'=>$qtInfo->tr['PSNL.SEX']->value,
							)
						);

	$form->addField('t2_s','<p><b>ที่อยู่ตามทะเบียนบ้าน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField('qt:PSNL.REGIST.PLACENAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PSNL.REGIST.PLACENAME']->value,
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
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PSNL.HOME.PLACENAME']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.HOUSEID',
						array(
							'type'=>'text',
							'label'=>'รหัสประจำบ้าน',
							'class'=>'-fill',
							'maxlength'=>10,
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


	$form->addField('qt:ECON.INCOME.YEARLY.AMT',
						array(
							'type'=>'text',
							'label'=>'รายได้เฉลี่ยต่อปี',
							'class'=>'',
							'posttext'=>' บาท',
							'value'=>$qtInfo->tr['ECON.INCOME.YEARLY.AMT']->value,
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

	$form->addField('save',
						array(
							'type'=>'button',
							'name'=>'save',
							'items'=>array(
												'type'=>'submit',
												'class'=>'-primary',
												'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
												),
							)
						);

	if (!$isQtAdmin) {
		$form->addField('comment','<div><b>หมายเหตุ:</b>'.sg_text2html($qtInfo->tr['APPROVE.REMARK']->value).'</div>');
	}
	$ret.=$form->build();


	if ($isQtAdmin) {
		$form=new Form(NULL,url('imed/app/poorman/form/approve/'.$qtref),NULL,'sg-form imed-poorman-form-approve');
		//$form->addData('rel','parent');
		$form->addData('checkValid',true);
		//$form->addData('callback',url('imed/app/poorman/list'));

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
														_COMPLETE=>'อนุมัติ - ตรวจสอบความถูกต้องของข้อมูลในแบบสอบถามเสร็จสมบูรณ์แล้ว'),
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
								)
							);

		$ret.=$form->build();
	}
	return $ret;
}
?>