<?php
function imed_app_poorman_form_v1($self,$psnid) {
	R::View('imed.toolbar',$self,'คนยากลำบาก','app.poorman');
	$ret.='<h3 class="header -sub">แบบสำรวจข้อมูลประชาชนในภาวะยากลำบากและกลุ่มเปราะบางทางสังคม</h3>';

	$form=new Form('data',url('imed/app/poorman/form/v1'),NULL,'imed-poorman-form');

	$form->addField('h1','<p style="padding:8px;border:1px #ccc solid;background:#f0f0f0;">ลำดับที่การเก็บข้อมูล <input type="text" style="width:4em;"/> / 2560</p>');

	$form->addField('h2','<h3>ข้อมูลทั่วไป</h3>');

	$form->addField(
						'prename',
						array(
							'type'=>'select',
							'label'=>'คำนำหน้าชื่อ :',
							'class'=>'-fill',
							'options'=>array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว','99'=>'อื่นๆ'),
							'posttext'=>'<input class="form-text -fill -hidden" type="text" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
							)
						);

	$form->addField(
						'fullname',
						array(
							'type'=>'text',
							'label'=>'ชื่อ - นามสกุล',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'cid',
						array(
							'type'=>'text',
							'label'=>'เลขที่บัตรประจำตัวประชาชน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'nocid',
						array(
							'type'=>'text',
							'label'=>'กรณีไม่มีบัตรประจำตัวประชาชนเนื่องจาก',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'birth',
						array(
							'type'=>'date',
							'label'=>'วัน/เดือน/ปีเกิด :',
							'year'=>(object)array('range'=>'-110,110','type'=>'BC'),
							'value'=>(object)array(
													'date'=>SG\getFirst($post->date['date'],date('d')),
													'month'=>SG\getFirst($post->date['month'],date('m')),
													'year'=>SG\getFirst($post->date['year'],date('Y'))
													),
							)
						);

	$form->addField(
						'sex',
						array(
							'type'=>'radio',
							'label'=>'เพศ :',
							'options'=>array('1'=>'ชาย','2'=>'หญิง'),
							)
						);


	$form->addField(
						'race',
						array(
							'type'=>'text',
							'label'=>'เชื้อชาติ',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'nation',
						array(
							'type'=>'text',
							'label'=>'สัญชาติ',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'religian',
						array(
							'type'=>'text',
							'label'=>'ศาสนา',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'married',
						array(
							'type'=>'radio',
							'label'=>'สถานภาพ :',
							'options'=>array(1=>'โสด','สมรสอยู่ด้วยกัน','สมรสแยกกันอยู่','หย่าร้าง','ไม่ได้สมรสแต่อยู่ด้วยกัน','หม้าย (คู่สมรสเสียชีวิต)'),
							)
						);

	$form->addField('t2_s','<p><b>ที่อยู่ตามทะเบียนบ้าน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField(
						'registplacename',
						array(
							'type'=>'text',
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'registhouseid',
						array(
							'type'=>'text',
							'label'=>'รหัสประจำบ้าน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'registaddr',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ตามทะเบียนบ้าน',
							'class'=>'sg-address -fill',
							)
						);

	$form->addField(
						'registzip',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'registphone',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์บ้าน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'registmobile',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							)
						);

	$form->addField('t2_e','</div>');

	$form->addField('t3_s','<p><b>ที่อยู่ปัจจุบัน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField(
						'registhometype',
						array(
							'type'=>'radio',
							'label'=>'ที่อยู่ปัจจุบัน :',
							'options'=>array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง'),
							)
						);

	$form->addField(
						'issameaddress',
						array(
							'type'=>'checkbox',
							'label'=>'ที่อยู่ปัจจุบันกับที่อยู่ตามทะเบียนบ้าน',
							'options'=>array(1=>'ที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน')
							)
						);

	$form->addField('t1_s','<div id="imed-poorman-form-regishome" class="-hidden">กรณีที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน (ระบุรายละเอียดเพิ่มเติมด้านล่าง)');
	
	$form->addField(
						'homeplacename',
						array(
							'type'=>'text',
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'homehouseid',
						array(
							'type'=>'text',
							'label'=>'รหัสประจำบ้าน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'homeaddr',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ปัจจุบัน',
							'class'=>'sg-address -fill',
							)
						);

	$form->addField(
						'homezip',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'homephone',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์บ้าน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'homemobile',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							)
						);

	$form->addField('t1_e','</div>');
	$form->addField('t3_e','</div>');

	$form->addField(
						'educa',
						array(
							'type'=>'radio',
							'label'=>'ระดับการศึกษา :',
							'options'=>array(1=>'ไม่ได้รับการศึกษา/ไม่จบชั้นประถมศึกษาตอนต้น','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษาตอนต้น','มัธยมศึกษาตอนปลาย','ปวช./อนุปริญญา','ปริญญาตรี','สูงกว่าปริญญาตรี'),
							)
						);

	$form->addField(
						'occupy',
						array(
							'type'=>'radio',
							'label'=>'อาชีพ :',
							'options'=>array(1=>'ไม่มีอาชีพ/ว่างงาน','นักเรียน/นักศึกษา','ค้าขาย/ธุรกิจส่วนตัว','ภิกษุ/สามเณร/แม่ชี','เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)','ข้าราชการ/พนักงานของรัฐ','พนักงานรัฐวิสาหกิจ','พนักงานบริษัท','รับจ้าง','อื่น ๆ : ระบุ <input class="form-text" type="text" />'),
							)
						);

	$form->addField(
						'income',
						array(
							'type'=>'text',
							'label'=>'รายได้เฉลี่ยต่อเดือน',
							'class'=>'',
							'posttext'=>' บาท',
							)
						);

	$form->addField(
						'incfrom',
						array(
							'type'=>'text',
							'label'=>'ที่มาของรายได้',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'incby',
						array(
							'type'=>'radio',
							'label'=>'ผู้ให้ข้อมูล :',
							'options'=>array(1=>'เป็นผู้ให้เอง','ผู้อื่นให้ : ระบุ <input class="form-text" type="text" />'),
							)
						);

	$form->addField('h3','<h3>สภาวะความยากลำบาก และ เปราะบางทางสังคม</h3>');

	$form->addField(
						'poor1',
						array(
							'type'=>'checkbox',
							'label'=>'1. ประเภทของสภาวะความยากลำบากและกลุ่มเปราะบางทางสังคม :',
							'options'=>array(1=>'1. คนไร้บ้าน','2. คนไร้สัญชาติ','3. ผู้สูงอายุที่ถูกทอดทิ้ง','4. ผู้ติดเชื้อ','5. ผู้ป่วยติดบ้าน/ติดเตียง','6. อดีตผู้ต้องขัง','7. คนพิการ'),
							)
						);

	$form->addField('poor1photo','ภาพถ่ายบุคคลพร้อมคำบรรยาย (ถ้ามี) <a class="btn"><i class="icon -camera"></i></a>');

	$form->addField(
						'poor2',
						array(
							'type'=>'checkbox',
							'label'=>'2. สาเหตุของความยากลำบาก (ตอบได้มากกว่า 1 ข้อ) :',
							'options'=>array(1=>'1. ยากจน / รายได้น้อย','2. มีหนี้สิน','3. ตกงาน / ไม่มีงานทำ / ไม่มีอาชีพ','4. ขาดผู้อุปการะ','5. ขาดความรู้ที่จะประกอบอาชีพ','6. ปัญหาครอบครัว','7. ไม่มีที่อยู่อาศัย / ไม่มีที่ดินทำกิน','8. ถูกชักจูงโดยคนรู้จัก / เพื่อน','9. ถูกบังคับ / ล่อลวง / แสวงหาผลประโยชน์','10. ไม่มีสถานะทางทะเบียนราษฎร์','11. ขาดโอกาสทางการศึกษาตามเกณฑ์','12. เจ็บป่วยเรื้อรัง','13. ช่วยเหลือตนเองไม่ได้ในชีวิตประจำวัน','14. อื่น ๆ : ระบุ <input class="form-text" type="text" />'),
							)
						);
	$form->addField(
						'poor3',
						array(
							'type'=>'checkbox',
							'label'=>'3. สถานะทางสุขภาพในปัจจุบัน :',
							'options'=>array(
													1=>'1. ปกติ',
													'2. ผู้สูงอายุ',
													'3. เจ็บป่วย : ระบุ <input class="form-text" type="text" />',
													'4. พิการ : ลักษณะความพิการ <input class="form-text" type="text" />',
													'5. อื่น ๆ : ระบุ <input class="form-text" type="text" />'
													),
							)
						);
	$form->addField(
						'poor4',
						array(
							'type'=>'checkbox',
							'label'=>'4. สิ่งที่ต้องการให้รัฐช่วยเหลือ :',
							'options'=>array(1=>'1. เข้าสถานสงเคราะห์','2. กลับภูมิลำเนา','3. ฝึกอาชีพ','4. หางานทำ','5. ที่พักชั่วคราว','6. เงินทุนประกอบอาชีพ','7. เงินสงเคราะห์ช่วยเหลือ','8. รักษาพยาบาล','9. ทำบัตรประชาชน','10. อื่น ๆ : ระบุ <input class="form-text" type="text" />'),
							)
						);

	$form->addField('poor4photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี) <a class="btn"><i class="icon -camera"></i></a>');

	$form->addField(
						'poor5',
						array(
							'type'=>'radio',
							'label'=>'5. เคยได้รับความช่วยเหลือจากหน่วยงานใดบ้าง :',
							'options'=>array(1=>'ไม่เคยได้รับ','เคยได้รับความช่วยเหลือเป็น'),
							)
						);

	$form->addField(
						'poor5.1',
						array(
							'type'=>'checkbox',
							'options'=>array(1=>'บริการ : <input class="form-text" type="text" />','เงิน : <input class="form-text" type="text" />','สิ่งของ : <input class="form-text" type="text" />'),
							)
						);

	$form->addField(
						'rcvyear',
						array(
							'type'=>'text',
							'label'=>'เมื่อปี',
							'class'=>'-fill',
							)
						);

	$form->addField(
					'rcvorg',
					array(
						'type'=>'text',
						'label'=>'จากหน่วยงาน',
						'class'=>'-fill',
						)
					);

	$form->addField(
					'poor6',
					array(
						'type'=>'checkbox',
						'label'=>'6. สิ่งที่ต้องการให้ชุมชนหรือองค์กรท้องถิ่นช่วยเหลือ',
						'options'=>array(1=>'1. ซ่อมแซมที่อยู่อาศัย','2. อาหาร','3. ฝึกอาชีพ','4. ให้งานทำ','5. ของใช้ในชีวิต','6. เครื่องนุ่งห่ม','7. เงินสงเคราะห์ช่วยเหลือ','8. วัสดุเพื่อการรักษาพยาบาล','9. อื่นๆ'),
						)
					);

	$form->addField(
					'poor6.detail',
					array(
						'type'=>'textarea',
						'label'=>'ระบุรายละเอียดสิ่งที่ต้องการด้านล่าง',
						'class'=>'-fill',
						'rows'=>5,
						)
					);

	$form->addField('poor6photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี) <a class="btn"><i class="icon -camera"></i></a>');

	$form->addField(
					'poor7.h',
					array(
						'type'=>'textfield',
						'label'=>'7. สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
						)
					);

	$form->addField(
						'poor7.1',
						array(
							'type'=>'radio',
							'label'=>'รายรับ',
							'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
							)
						);

	$form->addField(
					'poor7.2',
					array(
						'type'=>'radio',
						'label'=>'รายจ่าย',
						'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
						)
					);

	$form->addField(
					'poor7.3',
					array(
						'type'=>'radio',
						'label'=>'หนี้สิน',
						'options'=>array(1=>'ไม่มี',2=>'มีมากเกินรายได้',3=>'มีพอกับรายได้'),
						)
					);

	$form->addField(
					'poor7.detail',
					array(
						'type'=>'textarea',
						'label'=>'รายละเอียด สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
						'class'=>'-fill',
						'rows'=>5,
						)
					);

	$form->addField(
					'poor8',
					array(
						'type'=>'textarea',
						'label'=>'8. ประวัติ(เพิ่มเติม)',
						'class'=>'-fill',
						'rows'=>5,
						)
					);

	$form->addField(
					'poor9',
					array(
						'type'=>'textarea',
						'label'=>'9. ประวัติครอบครัว',
						'class'=>'-fill',
						'rows'=>5,
						)
					);

	$form->addField(
					'by',
					array(
						'type'=>'text',
						'label'=>'ผู้จัดเก็บข้อมูล',
						'class'=>'-fill',
						)
					);

	$form->addField(
					'getdate',
					array(
						'type'=>'text',
						'label'=>'วันที่เก็บข้อมูล',
						'class'=>'sg-datepicker -fill',
						)
					);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'name'=>'save',
						'items'=>array(
											'type'=>'submit',
											'class'=>'-primary',
											'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
											),
						'posttext'=>' <a class="sg-action" href="'.url('imed/app/poorman/list').'" data-rel="#main" data-done="moveto:0,0">ยกเลิก</a>',
						)
					);

	$ret.=$form->build();


	$ret.='<style type="text/css">
	.imed-poorman-form {margin:0;padding:8px;}
	</style>';
	$ret.='<script type="text/javascript">
	$("#edit-data-prename").change(function(){
		console.log($(this).val())
		if ($(this).val()=="99") {
			$(this).next().show().focus()
		} else {
			$(this).next().hide()
		}
	});
	$("input[name=\'data[issameaddress]\'").click(function() {
		console.log("Click")
		if($(this).is(":checked")) {
			$("#imed-poorman-form-regishome").show();
		} else {
			$("#imed-poorman-form-regishome").hide();
		}
	});
	</script>';
	return $ret;
}
?>