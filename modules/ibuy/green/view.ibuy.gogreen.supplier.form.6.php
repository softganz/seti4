<?php
function view_ibuy_green_supplier_form_6($qtInfo) {
	$qtref=$qtInfo->qtref;
	$isEditable=$qtInfo->RIGHT & _IS_EDITABLE;
	$isAccessable=$qtInfo->RIGHT & _IS_ACCESS;
	$isQtAdmin=$qtInfo->RIGHT & _IS_ADMIN || $qtInfo->right->right=='edit';




	if ($isEditable) {
		; // Do nothing
	} else 	if ($isAccessable) {
		$ret.=$qtInfo->tr['ORG.NAME']->part;
		return $ret;
	} else {
		return '<p class="notify">ขออภัย : ข้อมูลนี้ไม่ได้อยู่ในความรับผิดชอบของท่าน</p>';
	}
	$ret.='<h3 class="header -sub">แบบฟอร์มลงทะเบียนเครือข่าย Hatyai Go Green</h3>';

	//R::View('imed.toolbar',$self,'คนยากลำบาก : '.$qtInfo->tr['PSNL.FULLNAME']->value,'app.poorman');

	$form=new Form('data',url('ibuy/green/supplier/form/'.$qtref),'imed-poorman-form','imed-poorman-form');
	$form->addConfig('enctype','multipart/form-data');
	$form->addField('orgid',array('type'=>'hidden','id'=>'orgid','value'=>$qtInfo->orgid));
	$form->addField('qtref',array('type'=>'hidden','id'=>'qtref','value'=>$qtInfo->qtref));
	$form->addField('seq',array('type'=>'hidden','id'=>'seq','value'=>$qtInfo->seq));


	$form->addField('h2','<h3>ข้อมูลทั่วไป</h3>');

	$form->addField('qt:ORG.NAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อเครือข่าย',
							'containerclass'=>'-bigheader -first',
							'class'=>'-fill',
							'placeholder'=>'ป้อนชื่อเครือข่าย',
							'value'=>$qtInfo->tr['ORG.NAME']->value,
							)
						);

	$form->addField('qt:ORG.ADDRESS',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่',
							'class'=>'sg-address -fill',
							'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
							'value'=>$qtInfo->tr['ORG.ADDRESS']->value,
							'attr'=>array('data-altfld'=>'ORG_AREACODE')
							)
						);

	$form->addField('qt:ORG.AREACODE',
						array(
							'type'=>'hidden',
							'id'=>'ORG_AREACODE',
							'value'=>$qtInfo->tr['ORG.AREACODE']->value,
							)
						);
	$form->addField('qt:ORG.ZIP',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							'maxlength'=>5,
							'value'=>$qtInfo->tr['ORG.ZIP']->value,
							)
						);

	$form->addField('qt:ORG.TYPE',
						array(
							'type'=>'radio',
							'label'=>'ประเภทองค์กร :',
							'options'=>array(1=>'ชมรม','กลุ่ม','เครือข่าย','วิสาหกิจ','สมาคม',100=>'รายย่อย',99=>'อื่นๆ'),
							'value'=>$qtInfo->tr['ORG.TYPE']->value,
							)
						);

	$form->addField('qt:ORG.PHONE',
						array(
							'type'=>'text',
							'label'=>'โทรศัพท์',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.PHONE']->value,
							)
						);

	$form->addField('qt:ORG.MEMBERS',
						array(
							'type'=>'text',
							'label'=>'จำนวนสมาชิก (คน)',
							'class'=>'-fill',
							'maxlength'=>5,
							'value'=>$qtInfo->tr['ORG.MEMBERS']->value,
							)
						);
	$form->addField('qt:ORG.FACEBOOK',
						array(
							'type'=>'text',
							'label'=>'เฟชบุ๊ค',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.FACEBOOK']->value,
							)
						);

	$form->addField('qt:ORG.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดเครือข่าย (บรรยายเกี่ยวกับเครือข่าย ไม่เกิน 256 ตัวอักษร)',
							'class'=>'-fill',
							'rows'=>10,
							'value'=>$qtInfo->tr['ORG.DETAIL']->value,
							)
						);

	$form->addField('qt:ORG.CONTACT.NAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อผู้ประสานงานหลัก',
							'containerclass'=>'-bigheader -first',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.CONTACTNAME']->value,
							)
						);

	$form->addField('qt:ORG.CONTACT.PHONE',
						array(
							'type'=>'text',
							'label'=>'โทรศัพท์',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.CONTACT.PHONE']->value,
							)
						);
	$form->addField('qt:ORG.CONTACT.IDLINE',
						array(
							'type'=>'text',
							'label'=>'ID ไลน์',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.CONTACT.IDLINE']->value,
							)
						);
	$form->addField('qt:ORG.CONTACT.FACEBOOK',
						array(
							'type'=>'text',
							'label'=>'เฟชบุ๊ค',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.CONTACT.FACEBOOK']->value,
							)
						);

	$form->addField('qt:ORG.PARENT.NAME',
						array(
							'type'=>'text',
							'label'=>'กรณีเป็นกลุ่มย่อยให้ระบุ สังกัดเป็นสมาชิกชมรม/เครือข่ายใหญ่',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qtInfo->tr['ORG.PARENT.NAME']->value,
							)
						);

	$form->addField('qt:PRODUCT.KIND',
						array(
							'type'=>'radio',
							'label'=>'ลักษณะการผลิต :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'ผลิตแบบพอกินใช้กลุ่มในสมาชิก','ผลิตเหลือจำหน่าย','ผลิตเพื่อเป็นรายได้หลักของสมาชิก','ผลิตเพื่อเป็นรายได้เสริมของสมาชิก'),
							'value'=>$qtInfo->tr['PRODUCT.KIND']->value,
							)
						);

	$form->addField('qt:PRODUCT.TYPE.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'รูปแบบการผลิต :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(
													1=>'ใช้แรงงานของสมาชิก',
													'ใช้แรงงานของสมาชิกและเครื่องทุ่นแรง',
													'จ้างงานในการผลิตเป็นหลัก',
													'มีหน้าร้านในการจำหน่าย',
													'สามารถใช้ line,เฟชบุ๊ค ในการจำหน่ายผลผลิต',
													),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('PRODUCT.TYPE.LIST.',$qtInfo->tr),
							)
						);

	$form->addField('qt:PRODUCT.STANDARD.TYPE',
						array(
							'type'=>'radio',
							'label'=>'มาตรฐานการผลิต :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'รับรองแบบเครือข่าย',99=>'อื่นๆ'),
							'value'=>$qtInfo->tr['PRODUCT.STANDARD.TYPE']->value,
							)
						);

	$form->addField('qt:PRODUCT.STANDARD.DETAIL',
						array(
							'type'=>'text',
							'label'=>'ระบุ',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.STANDARD.DETAIL']->value,
							)
						);

	$form->addField('h3','<h3>ประเภทผลผลิต</h3>');

	$form->addField('qt:PRODUCT.NAME.RICE',
						array(
							'type'=>'checkbox',
							'label'=>'ข้าว :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'ข้าว'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.RICE']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.RICE.AREA',
						array(
							'type'=>'text',
							'label'=>'พื้นที่ (ไร่)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.RICE.AREA']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.RICE.GEAN',
						array(
							'type'=>'text',
							'label'=>'พันธ์ข้าวที่ปลูก',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.RICE.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.RICE.OUTPUT.YEAR',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อปี เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.RICE.OUTPUT.YEAR']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.RICE.KIND',
						array(
							'type'=>'radio',
							'label'=>'ผลิตแบบ :',
							'options'=>array(1=>'อินทรีย์','ปลอดภัย'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.RICE.KIND']->value,
							)
						);


	$form->addField('qt:PRODUCT.NAME.VEGETABLE',
						array(
							'type'=>'checkbox',
							'label'=>'ผัก :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'ผัก'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.VEGETABLE']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.VEGETABLE.AREA',
						array(
							'type'=>'text',
							'label'=>'พื้นที่ (ไร่)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.VEGETABLE.AREA']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.VEGETABLE.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.VEGETABLE.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.VEGETABLE.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.VEGETABLE.OUTPUT.MONTH']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.VEGETABLE.KIND',
						array(
							'type'=>'radio',
							'label'=>'ผลิตแบบ :',
							'options'=>array(1=>'อินทรีย์','ปลอดภัย'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.VEGETABLE.KIND']->value,
							)
						);




	$form->addField('qt:PRODUCT.NAME.FRUIT',
						array(
							'type'=>'checkbox',
							'label'=>'ผลไม้ :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'ผลไม้'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.FRUIT']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.FRUIT.AREA',
						array(
							'type'=>'text',
							'label'=>'พื้นที่ (ไร่)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.FRUIT.AREA']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.FRUIT.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.FRUIT.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.FRUIT.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.FRUIT.OUTPUT.MONTH']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.FRUIT.KIND',
						array(
							'type'=>'radio',
							'label'=>'ผลิตแบบ :',
							'options'=>array(1=>'อินทรีย์','ปลอดภัย'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.FRUIT.KIND']->value,
							)
						);



	$form->addField('qt:PRODUCT.NAME.HERB',
						array(
							'type'=>'checkbox',
							'label'=>'สมุนไพร :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'สมุนไพร'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.HERB']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.HERB.AREA',
						array(
							'type'=>'text',
							'label'=>'พื้นที่ (ไร่)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.HERB.AREA']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.HERB.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.HERB.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.HERB.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.HERB.OUTPUT.MONTH']->value,
							)
						);



	$form->addField('qt:PRODUCT.NAME.ARGPROCESS',
						array(
							'type'=>'checkbox',
							'label'=>'เกษตรแปรรูป :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'เกษตรแปรรูป'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.ARGPROCESS']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.ARGPROCESS.AREA',
						array(
							'type'=>'text',
							'label'=>'พื้นที่ (ไร่)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.ARGPROCESS.AREA']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.ARGPROCESS.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.ARGPROCESS.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.ARGPROCESS.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.ARGPROCESS.OUTPUT.MONTH']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.ARGPROCESS.KIND',
						array(
							'type'=>'radio',
							'label'=>'ผลิตแบบ :',
							'options'=>array(1=>'มีวัตถุดิบของตัวเอง','ไม่มีวัตถุดิบของตัวเอง '),
							'value'=>$qtInfo->tr['PRODUCT.NAME.ARGPROCESS.KIND']->value,
							)
						);


	$form->addField('qt:PRODUCT.NAME.LIVESTOCK',
						array(
							'type'=>'checkbox',
							'label'=>'ปศุสัตว์ :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'ปศุสัตว์'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.LIVESTOCK']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.LIVESTOCK.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.LIVESTOCK.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.LIVESTOCK.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.LIVESTOCK.OUTPUT.MONTH']->value,
							)
						);



	$form->addField('qt:PRODUCT.NAME.SEAFOOD',
						array(
							'type'=>'checkbox',
							'label'=>'อาหารทะเล :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'อาหารทะเล'),
							'value'=>$qtInfo->tr['PRODUCT.NAME.SEAFOOD']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.SEAFOOD.GEAN',
						array(
							'type'=>'text',
							'label'=>'ประเภท',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.SEAFOOD.GEAN']->value,
							)
						);
	$form->addField('qt:PRODUCT.NAME.SEAFOOD.OUTPUT.MONTH',
						array(
							'type'=>'text',
							'label'=>'จำนวนผลผลิตต่อเดือน เฉลี่ย (กก.)',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.NAME.SEAFOOD.OUTPUT.MONTH']->value,
							)
						);

	/*
	$form->addField('photo1','ภาพตัวอย่างผลผลิต (ถ้ามี) <ul class="card -photo">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorperson').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorperson" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');

	$form->addField('photo2','ภาพพื้นที่การผลิต (ถ้ามี) <ul class="card -photo">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorperson').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorperson" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');

	$form->addField('photo3','ภาพกระบวนการผลิต (ถ้ามี) <ul class="card -photo">'.__imed_app_poorman_form_photo($qtInfo->photo,'poorperson').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorperson" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');
	*/

	$form->addField('qt:PRODUCT.PROBLEM.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'ปัญหาที่พบในการผลิต :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(
													1=>'ศัตรูพืช',
													'ปุ๋ยอินทรีย์มีไม่เพียงพอ',
													'พื้นที่มีน้อย',
													'ขาดเงินลงทุน',
													'การตลาด',
													'อื่นๆ',
													),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('PRODUCT.PROBLEM.LIST.',$qtInfo->tr),
							)
						);
	$form->addField('qt:PRODUCT.PROBLEM.DETAIL',
						array(
							'type'=>'text',
							'label'=>'ระบุ',
							'class'=>'-fill',
							'value'=>$qtInfo->tr['PRODUCT.PROBLEM.DETAIL']->value,
							)
						);


	$form->addField('qt:PRODUCT.NEED.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'ความต้องการในการสนับสนุน :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(
													1=>'เงินลงทุน',
													'ปุ๋ยอินทรีย์',
													'ความรู้ในการผลิต',
													'ความรู้การบริหารจัดการการตลาด',
													'ตลาดจำหน่ายผลผลิต',
													'ต้องการให้ผู้บริโภคมาซื้อถึงแหล่งผลิต',
													'ต้องการคนกลางมารวบรวมผลผลิตไปจำหน่าย',
													'อื่นๆ',
													),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('PRODUCT.NEED.LIST.',$qtInfo->tr),
							)
						);
	$form->addField('qt:PRODUCT.NEED.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'ระบุ',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$qtInfo->tr['PRODUCT.NEED.DETAIL']->value,
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





	if ($qtInfo->qtstatus!=_COMPLETE) {
		$form->addField(
							'publish',
							array(
								'type'=>'checkbox',
								'label'=>'แจ้งสถานะการบันทึกแบบสอบถาม:',
								'containerclass'=>'-bigheader -first',
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

	$ret.=$form->build();


	if ($isQtAdmin) {
		$form=new Form(NULL,url('ibuy/green/supplier/form/approve/'.$qtref),NULL,'sg-form imed-poorman-form-approve');
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