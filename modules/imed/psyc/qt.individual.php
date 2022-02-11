<?php
$qt['prename']=array(
					'label'=>'คำนำหน้าชื่อ',
					'type'=>'text',
					'group'=>'person',
					);

$qt['name']=array(
					'label'=>'ชื่อ - นามสกุล',
					'type'=>'text',
					'group'=>'person',
					'class'=>'w-10',
					);

$qt['nickname']=array(
					'label'=>'ชื่อเล่น',
					'type'=>'text',
					'group'=>'person',
					);

$qt['cid']=array(
					'label'=>'หมายเลขบัตรประชาชน',
					'type'=>'text',
					'group'=>'person',
					'options'=>'{"maxlength":13}',
					);

$qt['sex']=array(
					'label'=>'เพศ',
					'type'=>'select',
					'group'=>'person',
					'option'=>array('ชาย'=>'ชาย','หญิง'=>'หญิง'),
					);

$qt['PSNL.1.5.1']=array(
					'label'=>'เชื้อชาติ',
					'type'=>'text',
					'group'=>'qt',
					);

$qt['PSNL.1.5.2']=array(
					'label'=>'สัญชาติ',
					'type'=>'text',
					'group'=>'qt',
					);

$qt['PSNL.1.5.3']=array(
					'label'=>'ศาสนา',
					'type'=>'text',
					'group'=>'qt',
					);

$qt['PSNL.1.7.2']=array(
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'อื่น ๆ ระบุ',
					'ret'=>'html',
					'remark'=>'ระดับการศึกษาอื่น ๆ',
					);

$qt['mstatus']=array(
					'label'=>'สถานภาพสมรส',
					'type'=>'select',
					'group'=>'person',
					'class'=>'w-3',
					'option'=>imed_model::get_category('mstatus'),
					);

$qt['PSNL.1.6.2']=array(
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>' อื่น ๆ ระบุ',
					'remark'=>'สถานภาพสมรส อื่น ๆ',
					);

$qt['educate']=array(
					'label'=>'ระดับการศึกษา',
					'type'=>'select',
					'group'=>'person',
					'option'=>imed_model::get_category('education'),
					);

$qt['PSNL.EDU.OTHER']=array(
					'label'=>'การศึกษา - อื่น ๆ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'อื่น ๆ ระบุ',
					);

$qt['PSNL.EDU.GRADE']=array(
					'label'=>'การศึกษา - ชั้นปีที่',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-1',
					'pretext'=>'ชั้นปีที่',
					);

$qt['PSNL.EDU.DEPART']=array(
					'label'=>'การศึกษา-สาขา',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'สาขา',
					);

$qt['PSNL.EDU.FACULTY']=array(
					'label'=>'การศึกษา-คณะ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'คณะ',
					);

$qt['phone']=array(
					'label'=>'โทรศัพท์',
					'type'=>'text',
					'group'=>'person',
					'class'=>'w-10',
					);

$qt['email']=array(
					'label'=>'อีเมล์',
					'type'=>'text',
					'group'=>'person',
					'class'=>'w-10',
					);

$qt['commune']=array(
					'label'=>'ชื่อชมุชน',
					'type'=>'text',
					'group'=>'person',
					'class'=>'w-8',
					);

$qt['aptitude']=array(
					'label'=>'ความสามารถในการทำงาน',
					'type'=>'text',
					'group'=>'person',
					'class'=>'-fill',
					);

$qt['interest']=array(
					'label'=>'ความสามารถพิเศษ',
					'type'=>'text',
					'group'=>'person',
					'class'=>'-fill',
					);

$qt['remark']=array(
					'label'=>'หมายเหตุ',
					'type'=>'textarea',
					'group'=>'person',
					'class'=>'-fill',
					'ret'=>'html',
					'onblur' => 'none',
					'button'=>'yes',
					);

$qt['PSNL.1.8.1']=array(
					'label'=>'การศึกษาต่อ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่ได้ศึกษาต่อ,ศึกษาต่อ',
					'display'=>'block',
					);

$qt['PSNL.1.8.2']=array(
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-2',
					'pretext'=>' ระดับ',
					'remark'=>'ศึกษาต่อระดับ',
					);

$qt['PSNL.1.8.3']=array(
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'สถานศึกษา',
					'remark'=>'สถานที่ศึกษาต่อ',
					);

$qt['OTHR.5.5']=array(
					'label'=>'สถานะของที่พักอาศัย',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'บ้านพ่อแม่, บ้านตนเอง, บ้านญาติ, บ้านเช่า, อื่น ๆ',
					);
$qt['OTHR.5.5.1']=array(
					'label'=>'สถานะของที่พักอาศัย อื่น ๆ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุ',
					);

$qt['PSNL.HOUSECONDITION']=array(
					'label'=>'สภาพบ้าน',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:บ้านชั้นเดียว, 2:บ้านสองชั้นขึ้นไป, 3:ตึกแถว, 4:ห้องแถว, 99:อื่นๆ',
					);
$qt['PSNL.HOUSECONDITION.OTHER']=array(
					'label'=>'สภาพบ้าน-อื่น ๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-4',
					'pretext'=>'อื่น ๆ (เช่น กระต๊อบ ขนำ วัด เป็นต้น) ระบุ',
					);

$qt['PSNL.1.10.1']=array(
					'label'=>'การเข้าถึงสิทธิ์',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'สิทธิข้าราชการ/วิสาหกิจ, สิทธิประกันสังคม, สิทธิหลักประกันสุขภาพถ้วนหน้า (ท.74), สิทธิหลักประกันสุขภาพถ้วนหน้า, สิทธิ์ประกันชีวิต, สิทธิว่าง (อยู่ระหว่างรอสิทธิ์), สิทธิว่าง (ไม่มีสิทธิ์ใด ๆ), สิทธิอื่น ๆ',
					'display'=>'block',
					);
$qt['PSNL.1.10.2']=array(
					'label'=>'สิทธิหลักประกันสุขภาพถ้วนหน้า-ระบุ ท.',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-2',
					'pretext'=>'ระบุ ท.',
					);
$qt['PSNL.1.10.3']=array(
					'label'=>'สิทธิหลักประกันสุขภาพถ้วนหน้า-อื่น ๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-2',
					'pretext'=>'ระบุ',
					);
$qt['PSNL.RIGHT.OFFICE']=array(
					'label'=>'สถานที่ทำงาน-อื่น ๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'สถานที่ทำงาน',
					);
$qt['PSNL.RIGHT.SOCIALSECURITY.NO']=array(
					'label'=>'เลขที่ประกันสังคม',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'เลขที่ประกันสังคม',
					);
$qt['PSNL.RIGHT.EMPTY.CAUSE']=array(
					'label'=>'ไม่มีสิทธิ์ใดๆ เพราะสาเหตุใด',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ไม่มีสิทธิ์ใดๆ เพราะสาเหตุใด',
					);

$qt['PSNL.1.9.1.1']=array(
					'label'=>'บัตรประจำตัวคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'บัตรประจำตัวคนพิการ',
					);
$qt['PSNL.CARD.DISABLED.TYPE']=array(
					'label'=>'บัตรประจำตัวคนพิการ แบบเล่ม',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:แบบเล่ม, 2:สมาร์ทการ์ด',
					);
$qt['PSNL.1.9.2.1']=array(
					'label'=>'บัตรประจำตัวประชาชน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'บัตรประจำตัวประชาชน',
					);
$qt['PSNL.1.9.3.1']=array(
					'label'=>'บัตรทองคนพิการ (ท.74)',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'บัตรทองคนพิการ (ท.74)',
					);
$qt['PSNL.CARD.ELDER']=array(
					'label'=>'บัตรผู้สูงอายุ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'บัตรผู้สูงอายุ',
					);
$qt['PSNL.1.9.4.1']=array(
					'label'=>'ทะเบียนบ้าน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ทะเบียนบ้าน',
					);
$qt['PSNL.1.9.5.1']=array(
					'label'=>'บัตรอื่นๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'บัตรอื่นๆ',
					);
$qt['PSNL.CARD.IDENTIFY']=array(
					'label'=>'บัตรอื่นๆ ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุ',
					);

$qt['register']=array(
					'label'=>'จดทะเบียนคนพิการ',
					'type'=>'select',
					'group'=>'disabled',
					'option'=>'จดทะเบียน,ไม่ได้จดทะเบียน,รอจดทะเบียน,ไม่ประสงค์จะจดทะเบียน',
					);
$qt['regdate'] = array(
					'label'=>'วันที่จดทะเบียนคนพิการ',
					'type'=>'datepicker',
					'group'=>'disabled',
					'ret'=>'date:ว ดดด ปปปป',
					'pretext'=>'วันจดทะเบียนครั้งแรก',
					);
$qt['PSNL.1.9.1.2']=array(
					'label'=>'วันต่อบัตรล่าสุด',
					'type'=>'datepicker',
					'group'=>'qt',
					'ret'=>'date:ว ดดด ปปปป',
					'pretext'=>'วันต่อบัตรล่าสุด',
					);
$qt['PSNL.1.9.1.3']=array(
					'label'=>'วันหมดอายุ',
					'type'=>'datepicker',
					'group'=>'qt',
					'ret'=>'date:ว ดดด ปปปป',
					'pretext'=>'วันหมดอายุ',
					);
$qt['PSNL.1.9.1.4']=array(
					'label'=>'สถานที่ต่อทะเบียน',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'สถานที่ต่อทะเบียน',
					);

//ความพิการ
$qt['DSBL.REGIST.CAUSE']=array(
					'label'=>'เหตุผลที่ยังไม่ได้รับการจดทะเบียน',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-4',
					'pretext'=>'เหตุผลที่ยังไม่ได้รับการจดทะเบียน เพราะ',
					);

$qt['DSBL.DEFECT.VISUAL']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'1:ทางการเห็น',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.HEARING']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'2:ทางการได้ยินหรือสื่อความหมาย',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.MOVEMENT']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'3:ทางการเคลื่อนไหวหรือทางร่างกาย',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.MENTAL']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'4:ทางจิตใจหรือพฤติกรรม',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.INTELLECTUAL']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'5:ทางสติปัญญา',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.LEARNING']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'6:ทางการเรียนรู้',
					'class'=>'-disabled-type',
					);
$qt['DSBL.DEFECT.AUTISTIC']=array(
					'label'=>'ประเภทความพิการ',
					'type'=>'checkbox',
					'group'=>'defect',
					'option'=>'7:ทางออทิสติก',
					'class'=>'-disabled-type',
					);

$qt['begetting']=array(
					'label'=>'สาเหตุความพิการ',
					'type'=>'select',
					'group'=>'disabled',
					'option'=>imed_model::get_category('begetting'),
					);
$qt['DSBL.2.1.2']=array(
					'label'=>'สาเหตุความพิการ/อุบัติเหุต/เจ็บป่วย/โรค',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'สาเหตุความพิการ/อุบัติเหุต/เจ็บป่วย/โรค',
					);
$qt['DSBL.2.1.3']=array(
					'label'=>'พิการเมื่ออายุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-1',
					'pretext'=>'เมื่ออายุ',
					);
$qt['disabilities_level']=array(
					'label'=>'ระดับความพิการ',
					'type'=>'select',
					'group'=>'disabled',
					'option'=>imed_model::get_category('dislevel'),
					);
$qt['comunicate']=array(
					'label'=>'สื่อสารได้หรือไม่',
					'type'=>'select',
					'group'=>'disabled',
					'option'=>'ได้,ไม่ได้',
					);

$qt['DSBL.NEEDCARE']=array(
					'label'=>'กรณีมีผู้ดูแล ผู้ดูแลให้การดูแลคนพิการในเรื่องใดบ้าง โปรดบรรยาย',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					);

// หมวดสุขภาพ
$qt['HLTH.2.4']=array(
					'โรคประจำตัว',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่มี,มี',
					);
$qt['โรคประจำตัว-อื่นๆ']=array(
					'โรคประจำตัว-อื่นๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'อื่น ๆ ระบุ',
					);
$qt['โรคประจำตัว-ความดันโลหิตสูง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ความดันโลหิตสูง',
					);
$qt['โรคประจำตัว-เบาหวาน']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:เบาหวาน',
					);
$qt['โรคประจำตัว-ไขมันในเลือดสูง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ไขมันในเลือดสูง',
					);
$qt['โรคประจำตัว-โรคหัวใจ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคหัวใจ',
					);
$qt['โรคประจำตัว-โรคปอด']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคปอด/หอบหืด',
					);
$qt['โรคประจำตัว-โรคถุงลมโป่งพอง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคถุงลมโป่งพอง',
					);
$qt['โรคประจำตัว-ไตวาย']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ไตวาย',
					);
$qt['โรคประจำตัว-มะเร็ง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:มะเร็ง',
					);
$qt['โรคประจำตัว-ข้ออักเสบ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ข้ออักเสบ',
					);
$qt['โรคประจำตัว-เก๊าท์']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ข้ออักเสบจากเก๊าท์/ข้อเสื่อม',
					);
$qt['โรคประจำตัว-รูมาตอยด์']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ข้ออักเสบจากรูมาตอยด์',
					);
$qt['โรคประจำตัว-โรคลมชัก']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคลมชัก',
					);
$qt['โรคประจำตัว-โรคเส้นเลือดสมองตีบ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคเส้นเลือดสมองตีบ/อัมพฤกษ์/อัมพาต',
					);
$qt['โรคประจำตัว-พาร์กินสัน']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:พาร์กินสัน/อาการสั่น',
					);
$qt['โรคประจำตัว-อัลไซเมอร์']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:อัลไซเมอร์/สมองเสื่อม',
					);
$qt['โรคประจำตัว-จิตเวช']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:มีอาการทางจิตเวช',
					);
$qt['โรคประจำตัว-โรคอ้วน']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคอ้วน',
					);
$qt['โรคประจำตัว-โรคสมองและหลอดเลือด']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคสมองและหลอดเลือด',
					);

//ภาวะแทรกซ้อน
$qt['ภาวะแทรกซ้อน-แผลกดทับ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:แผลกดทับ',
					);
$qt['ภาวะแทรกซ้อน-ข้อติดแข็ง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ข้อติดแข็ง',
					);
$qt['ภาวะแทรกซ้อน-ข้อติดแข็ง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ข้อติดแข็ง',
					);
$qt['ภาวะแทรกซ้อน-กล้ามเนื้อเกร็งหรือกระตุก']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:กล้ามเนื้อเกร็งหรือกระตุก',
					);
$qt['ภาวะแทรกซ้อน-อื่นๆ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:อื่นๆ',
					);
$qt['ภาวะแทรกซ้อน-ระบุ']=array(
					'label'=>'',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);

//โรคประจำตัวผู้สูงอายุ
$qt['โรคประจำตัว-โรคทางระบบหัวใจและหลอดเลือด']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคทางระบบหัวใจและหลอดเลือด',
					);
$qt['โรคประจำตัว-โรคทางระบบหลอดเลือดสมอง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคทางระบบหลอดเลือดสมอง',
					);
$qt['โรคประจำตัว-โรคระบบกระดูกและข้อ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคระบบกระดูกและข้อ',
					);
$qt['โรคประจำตัว-โรคระบบทางเดินอาหารและช่องท้อง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคระบบทางเดินอาหารและช่องท้อง',
					);
$qt['โรคประจำตัว-โรคทางเดินปัสสาวะ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคทางเดินปัสสาวะ',
					);
$qt['โรคประจำตัว-โรคทางตา']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคทางตา',
					);
$qt['โรคประจำตัว-โรคออโตอิมมูน']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคออโตอิมมูน',
					);
$qt['โรคประจำตัว-โรคระบบทางเดินหายใจ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคระบบทางเดินหายใจ',
					);
$qt['โรคประจำตัว-โรคระบบต่อมไร้ท่อ']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคระบบต่อมไร้ท่อ',
					);
$qt['โรคประจำตัว-โรคทางระบบผิวหนัง']=array(
					'label'=>'',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:โรคทางระบบผิวหนัง',
					);

$qt['HLTH.2.5.1']=array(
					'label'=>'ประวัติการรักษาพยาบาล',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่ได้รับการรักษา,ได้รับการรักษา',
					'display'=>'block',
					);
$qt['HLTH.2.5.1.1']=array(
					'label'=>'ได้รับการรักษา-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุอาการหรือโรค');
$qt['HLTH.2.5.2.1']=array(
					'label'=>'สถานที่รับการรกษา 1.',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-7',
					'pretext'=>'1.',
					);
$qt['HLTH.2.5.2.2']=array(
					'label'=>'สถานที่รับการรกษา 2.',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-7',
					'pretext'=>'2.'
					);
$qt['HLTH.2.5.3']=array(
					'label'=>'การรักษาต่อเนื่อง',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'รักษาสม่ำเสมอ,รักษาไม่สม่ำเสมอ',
					);

$qt['HLTH.2.5.4']=array(
					'label'=>'ประวัติการแพ้ยา',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'แพ้ยา',
					);
$qt['HLTH.2.5.4.1']=array(
					'label'=>'ประวัติการแพ้ยา-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุ',
					);
$qt['HLTH.2.5.5']=array(
					'label'=>'ประวัติการแพ้อาหาร',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'แพ้อาหาร',
					);
$qt['HLTH.2.5.5.1']=array(
					'label'=>'ประวัติการแพ้อาการ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุ',
					);

$qt['discharge']=array(
					'label'=>'Discharge',
					'type'=>'select',
					'group'=>'disabled',
					'option'=>imed_model::get_category('discharge'),
					);
$qt['dischargedate']=array(
					'label'=>'วันที่ Discharge',
					'type'=>'datepicker',
					'group'=>'disabled',
					'ret'=>'date:ว ดดด ปปปป',
					'pretext'=>'เมื่อวันที่'
					);


$qt['OTHR.5.8.2']=array(
					'label'=>'สุขอนามัยส่วนตัวคนพิการ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'สะอาดถูกหลักอนามัย:สะอาด, 2:สะอาดปานกลาง, ไม่ค่อยสะอาด:ไม่สะอาด',
					);
$qt['OTHR.5.8.2.1']=array(
					'label'=>'สุขอนามัยส่วนตัวคนพิการ-รายละเอียด',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'อธิบายสิ่งที่สังเกตได้',
					);

$qt['OTHR.5.8.3']=array(
					'label'=>'สภาพสิ่งแวดล้อมในบ้าน',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'สะอาดมาก:สะอาด, สะอาดปานกลาง:สะอาดปานกลาง, ไม่ค่อยสะอาด:ไม่สะอาด',
					);
$qt['OTHR.5.8.3.1']=array(
					'label'=>'สภาพสิ่งแวดล้อมในบ้าน-รายละเอียด',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'อธิบายสิ่งที่สังเกตได้',
					);

$qt['HLTH.ความปลอดภัยของที่อยู่อาศัย']=array(
					'label'=>'ความปลอดภัยของที่อยู่อาศัย',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:ปลอดภัย, 2:ปลอดภัยปานกลาง, 3:ไม่ปลอดภัย',
					);
$qt['HLTH.ความปลอดภัยของที่อยู่อาศัย.รายละเอียด']=array(
					'label'=>'ความปลอดภัยของที่อยู่อาศัย-รายละเอียด',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'อธิบายสิ่งที่สังเกตได้',
					);

$qt['HLTH.ความมั่นคงของที่อยู่อาศัย']=array(
					'label'=>'ความมั่นคงของที่อยู่อาศัย',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:มั่นคงดี, 2:มั่นคงปานกลาง, 3:ไม่มั่นคง',
					);
$qt['HLTH.ความมั่นคงของที่อยู่อาศัย.รายละเอียด']=array(
					'label'=>'ความมั่นคงของที่อยู่อาศัย-รายละเอียด',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'อธิบายสิ่งที่สังเกตได้',
					);

$qt['HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน']=array(
					'label'=>'สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน',
					'type'=>'radio',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'1:ไม่มี, 2:มี',
					);
$qt['HLTH.สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน.รายละเอียด']=array(
					'label'=>'สิ่งอำนวยความสะดวกให้สามารถดำรงชีวิตในบ้าน-รายละเอียด',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-6',
					'pretext'=>'ได้แก่',
					);

$qt['HLTH.การรับบริการด้านสุขภาพ']=array(
					'label'=>'การรับบริการด้านสุขภาพ',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'1:ได้รับการรักษาพยาบาลโดยไม่เสียค่าใช้จ่าย, 2:ได้รับการตรวจสุขภาพประจำปี ปีละ 1 ครั้ง, 3:ได้รับความรู้เกี่ยวกับการดูแลสุขภาพและนำมาใช้ในชีวิตประจำวัน, 4:ได้รับการดูแลจากเจ้าหน้าที่สาธารณสุข (พยาบาล/อสม.)',
					);
$qt['HLTH.PROSTHETIC']=array(
					'label'=>'อุปกรณ์ความช่วยเหลือ',
					'type'=>'radio',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'1:ไม่เคยได้รับอุปกรณ์ความช่วยเหลือ, 2:ได้รับอุปกรณ์ช่วยเหลือความพิการ (เช่น รถเข็น ไม้เท้า ไม้ค้ำยัน วอล์คเกอร์ ไม้เท้าขาว เครื่องช่วยฟัง เป็นต้น)',
					);

$qt['OTHR.5.2.1']=array(
					'label'=>'ได้รับบริการตามระบบสาธารณสุขจาก รพ.สต./โรงพยาบาลชุมชน/โรงพยาบาลของจังหวัด',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับบริการตามระบบสาธารณสุขจาก รพ.สต./โรงพยาบาลชุมชน/โรงพยาบาลของจังหวัด',
					);
$qt['OTHR.5.2.2']=array(
					'label'=>'ได้รับความรู้เกี่ยวกับการดูแลสุขภาพและนำมาใช้ในชีวิตประจำวัน',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับความรู้เกี่ยวกับการดูแลสุขภาพและนำมาใช้ในชีวิตประจำวัน',
					);
$qt['OTHR.5.2.3']=array(
					'label'=>'ได้รับความรู้เกี่ยวกับการดูแลสุขภาพ แต่ไม่ได้ใช้',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับความรู้เกี่ยวกับการดูแลสุขภาพ แต่ไม่ได้ใช้',
					);
$qt['OTHR.5.2.4']=array(
					'label'=>'ได้รับการตรวจสุขภาพประจำปี ปีละ 1 ครั้ง',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับการตรวจสุขภาพประจำปี ปีละ 1 ครั้ง',
					);
$qt['OTHR.5.2.5']=array(
					'label'=>'ได้รับการรักษาพยาบาลโดยไม่เสียค่าใช้จ่าย',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับการรักษาพยาบาลโดยไม่เสียค่าใช้จ่าย',
					);
$qt['OTHR.5.2.6']=array(
					'label'=>'ได้รับการดูแลจากเจ้าหน้าที่สาธารณสุข (พยาบาล/อสม.)',
					'type'=>'checkbox',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'ได้รับการดูแลจากเจ้าหน้าที่สาธารณสุข (พยาบาล/อสม.)',
					);
$qt['OTHR.5.2.6.VISIT']=array(
					'label'=>'ได้รับการดูแลจากเจ้าหน้าที่สาธารณสุข (พยาบาล/อสม.) - การเยี่ยมบ้าน',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:มาเยี่ยมบ้าน, 2:ไม่ได้มาเยี่ยม',
					);

$qt['DSBL.3.3']=array(
					'label'=>'เคยได้รับการฟื้นฟูคนพิการและการช่วยเหลือตนเองในชีวิตประจำวัน',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่เคย, เคย',
					);
$qt['DSBL.3.3.1']=array(
					'label'=>'เคยได้รับการฟื้นฟูคนพิการและการช่วยเหลือตนเองในชีวิตประจำวัน-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'pretext'=>'ระบุ',
					);

$qt['problem']=array(
					'label'=>'ปัญหาและความต้องการในด้านสุขภาพ-ปัญหา',
					'type'=>'textarea',
					'group'=>'disabled',
					'class'=>'w-9',
					'ret'=>'html',
					);
$qt['DSBL.3.7']=array(
					'label'=>'ปัญหาและความต้องการในด้านสุขภาพ-ความต้องการ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					'ret'=>'html',
					);

$qt['DSBL.SHOULDHELP.ทำความสะอาดร่างกาย']=array(
					'label'=>'ทำความสะอาดร่างกาย',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ทำความสะอาดร่างกาย',
					);
$qt['DSBL.SHOULDHELP.ทำแผลกดทับ']=array(
					'label'=>'ทำแผลกดทับ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ทำแผลกดทับ',
					);
$qt['DSBL.SHOULDHELP.เปลี่ยนสายสวนปัสสาวะ']=array(
					'label'=>'เปลี่ยนสายสวนปัสสาวะ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เปลี่ยนสายสวนปัสสาวะ',
					);
$qt['DSBL.SHOULDHELP.เปลี่ยนสายให้อาหารทางจมูก']=array(
					'label'=>'เปลี่ยนสายให้อาหารทางจมูก',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เปลี่ยนสายให้อาหารทางจมูก',
					);
$qt['DSBL.SHOULDHELP.ช่วยฝึกกายภาพบำบัดหรือฝึกให้ทำด้วยตนเอง']=array(
					'label'=>'ช่วยฝึกกายภาพบำบัดหรือฝึกให้ทำด้วยตนเอง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ช่วยฝึกกายภาพบำบัดหรือฝึกให้ทำด้วยตนเอง'
					);
$qt['DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน']=array(
					'label'=>'ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',
					);
$qt['DSBL.SHOULDHELP.ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน.อื่น ๆ']=array(
					'label'=>'ฝึกการช่วยเหลือตัวเองในกิจวัตรประจำวัน',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-4',
					'pretext'=>'ระบุ',
					);
$qt['DSBL.SHOULDHELP.การฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ']=array(
					'label'=>'การฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ',
					);
$qt['DSBL.SHOULDHELP.ส่งต่อคนพิการเข้ารับการตรวจร่างกายหรือฟื้นฟูจากแพทย์เฉพาะทาง']=array(
					'label'=>'ส่งต่อคนพิการเข้ารับการตรวจร่างกายหรือฟื้นฟูจากแพทย์เฉพาะทาง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ส่งต่อคนพิการเข้ารับการตรวจร่างกายหรือฟื้นฟูจากแพทย์เฉพาะทาง',
					);
$qt['DSBL.SHOULDHELP.อื่นๆ']=array(
					'label'=>'อื่นๆ.ระบุ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'อื่น ๆ',
					);
$qt['DSBL.SHOULDHELP.อื่นๆ.ระบุ']=array(
					'label'=>'อื่นๆ.ระบุ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					'pretext'=>'ระบุ',
					);

$qt['DSBL.SHOULDHELP.ฝึกทักษะ O and M']=array(
					'label'=>'ฝึกทักษะ O&M (การใช้ไม้เท้าขาวของคนตาบอด)',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ฝึกทักษะ O and M',
					);
$qt['DSBL.SHOULDHELP.ฝึกทักษะการพูด']=array(
					'label'=>'ฝึกทักษะการพูด',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ฝึกทักษะการพูด',
					);
$qt['DSBL.SHOULDHELP.ฝึกการใช้ภาษามือ']=array(
					'label'=>'ฝึกการใช้ภาษามือ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ฝึกการใช้ภาษามือ',
					);
$qt['DSBL.SHOULDHELP.ฝึกวิธีการดูแลคนพิการให้สมาชิกครอบครัว']=array(
					'label'=>'ฝึกวิธีการดูแลคนพิการให้สมาชิกครอบครัว',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ฝึกวิธีการดูแลคนพิการให้สมาชิกครอบครัว',
					);
$qt['DSBL.SHOULDHELP.การดูแลเรื่องการกินยาจิตเวชให้ต่อเนื่อง']=array(
					'label'=>'การดูแลเรื่องการกินยาจิตเวชให้ต่อเนื่อง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การดูแลเรื่องการกินยาจิตเวชให้ต่อเนื่อง',
					);

$qt['DSBL.HELPER.WHO']=array(
					'label'=>'ผู้ดูแลประจำคนพิการ',
					'type'=>'radio',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'1:ไม่มีคนดูแล, 2:มีคนดูแล',
					);
$qt['DSBL.HELPER.MEMBER']=array(
					'label'=>'ผู้ดูแลประจำคนพิการ-สมาชิกครอบครัว',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'สมาชิกครอบครัว',
					);
$qt['DSBL.HELPER.OTHER']=array(
					'label'=>'ผู้ดูแลประจำคนพิการ-จ้างคนนอกครอบครัวมาดูแล',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'จ้างคนนอกครอบครัวมาดูแล',
					);
$qt['OTHR.5.7']=array(
					'newkey'=>'DSBL.HELPER.QUALITY.TRAINING',
					'label'=>'ได้รับการอบรมเรื่องการดูแลคนพิการ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่เคยได้รับการอบรมความรู้การดูแลคนพิการ:ไม่เคย, เคยได้รับการอบรมความรู้การดูแลคนพิการ:เคย',
					);
$qt['DSBL.HELPER.QUALITY.TRAINING.BY']=array(
					'label'=>'ได้รับการอบรมเรื่องการดูแลคนพิการ-จากใครหรือที่ใด',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-9',
					);
$qt['DSBL.3.5']=array(
					'label'=>'ได้รับข้อมูลข่าวสารหรือการช่วยเหลือที่เป็นประโยชน์ต่อการดูแลคนพิการ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'1:ไม่เคย, 2:เคย',
					);
$qt['DSBL.3.5.1']=array(
					'label'=>'ได้รับข้อมูลข่าวสารหรือการช่วยเหลือที่เป็นประโยชน์ต่อการดูแลคนพิการ-จากใครหรือที่ใด',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-9',
					);
$qt['OTHR.5.7.1']=array(
					'label'=>'ปัญหาของผู้ดูแล',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					);
$qt['DSBL.HELPER.WANT']=array(
					'label'=>'ความต้องการของผู้ดูแล',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					);

//ECONOMIC
$qt['occupa']=array(
					'label'=>'ประกอบอาชีพ',
					'type'=>'select',
					'group'=>'person',
					'class'=>'-fill',
					'option'=>imed_model::get_category('occupation'),
					);
$qt['ECON.4.4.11.1']=array(
					'label'=>'ประกอบอาชีพ-อื่นๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'อื่น ๆ ระบุ',
					);
$qt['ECON.4.1']=array(
					'label'=>'ฝึกอาชีพ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่เคย,เคย',
					);
$qt['ECON.4.1.1']=array(
					'label'=>'ฝึกอาชีพ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);
$qt['ECON.4.2']=array(
					'label'=>'ต้องการฝึกอาชีพ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่ต้องการ,ต้องการ',
					);
$qt['ECON.4.2.1']=array(
					'label'=>'ต้องการฝึกอาชีพ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);



$qt['ECON.4.5']=array(
					'label'=>'รายได้ของคนพิการ',
					'type'=>'radio',
					'group'=>'qt',
					'display'=>'block',
					'option'=>'1:ต่ำกว่า 3000 บาท/เดือน, 2:3001-5000 บาท/เดือน, 3:5001-8000 บาทต่อเดือน, 4:8001 บาทขึ้นไป, 5:ไม่มีรายได้',
					);
$qt['ECON.4.6']=array(
					'label'=>'รายได้ของครอบครัวโดยเฉลี่ยต่อเดือน',
					'type'=>'text',
					'group'=>'qt',
					'ret'=>'money',
					);
$qt['ECON.INCOME.ENOUGH']=array(
					'label'=>'รายได้ของครอบครัวโดยเฉลี่ยต่อเดือน-เพียงพอ',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'2:เพียงพอ, 1:ไม่เพียงพอ',
					);
$qt['ECON.EXPENSE.ABOUT.MEDICAL']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ด้านอุปกรณ์ทางการแพทย์',
					);
$qt['ECON.EXPENSE.ABOUT.FOOD']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ด้านอาหาร',
					);
$qt['ECON.EXPENSE.ABOUT.COMMODITY']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ด้านสาธารณูปโภค',
					);
$qt['ECON.EXPENSE.ABOUT.PRIVATE']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ของใช้ส่วนตัว',
					);
$qt['ECON.EXPENSE.ABOUT.OTHER']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1:ด้านอื่นๆ',
					);
$qt['ECON.4.8']=array(
					'label'=>'รายจ่ายไปในเรื่องอะไรบ้าง-ระบุ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-9',
					'pretext'=>'ระบุ',
					);

$qt['ECON.4.7']=array(
					'label'=>'คนพิการและครอบครัวมีภาระหนี้สินหรือไม่',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'ไม่มี,มี',
					);
$qt['ECON.4.7.1']=array(
					'label'=>'คนพิการและครอบครัวมีภาระหนี้สินหรือไม่-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);
$qt['ECON.4.7.2']=array(
					'label'=>'ภาระหนี้สิน-ผู้กู้',
					'type'=>'radio',
					'group'=>'qt',
					'option'=>'คนพิการกู้,รวมกลุ่มกันกู้,ผู้ดูแลคนพิการกู้ (ผู้ดูแลตามกฎหมาย)',
					'pretext'=>'ผู้กู้',
					);

$qt['OTHR.5.1.1']=array(
					'label'=>'รู้เรื่องเกี่ยวกับสิทธิ์คนพิการ สามารถตอบคำถามเรื่องสิทธิคนพิการพื้นฐานได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'รู้เรื่องเกี่ยวกับสิทธิ์คนพิการ สามารถตอบคำถามเรื่องสิทธิคนพิการพื้นฐานได้',
					);
$qt['OTHR.5.1.2']=array(
					'label'=>'เคยรู้สึกว่าตัวเองไม่ได้รับความเป็นธรรมแล้วต้องการดำเนินการเพื่อให้สิทธิ์นั้น ๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เคยรู้สึกว่าตัวเองไม่ได้รับความเป็นธรรมแล้วต้องการดำเนินการเพื่อให้สิทธิ์นั้น ๆ',
					);
$qt['OTHR.5.1.3']=array(
					'label'=>'ได้รับการรักษาพยาบาลโดยไม่เสียค่าใช้จ่าย',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับการรักษาพยาบาลโดยไม่เสียค่าใช้จ่าย',
					);
$qt['OTHR.5.1.4']=array(
					'label'=>'ทุนการศึกษา (ของตัวท่านเองหรือบุตร)',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ทุนการศึกษา (ของตัวท่านเองหรือบุตร)',
					);
$qt['OTHR.5.1.5']=array(
					'label'=>'ได้รับเงินสงเคราะห์หรือเงินช่วยเหลือกรณีฉุกเฉินจากสำนักงาน พม.จังหวัดสงขลา',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับเงินสงเคราะห์หรือเงินช่วยเหลือกรณีฉุกเฉินจากสำนักงาน พม.จังหวัดสงขลา',
					);
$qt['OTHR.5.1.6']=array(
					'label'=>'ได้รับเงินสงเคราะห์หรือเงินช่วยเหลือกรณีฉุกเฉิน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับเงินสงเคราะห์หรือเงินช่วยเหลือกรณีฉุกเฉิน',
					);
$qt['OTHR.5.1.7']=array(
					'label'=>'ได้กู้ยืมเงิน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้กู้ยืมเงิน',
					);
$qt['OTHR.5.1.8']=array(
					'label'=>'ได้รับเบี้ยยังชีพคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับเบี้ยยังชีพคนพิการ',
					);
$qt['OTHR.5.1.9']=array(
					'label'=>'ได้รับเบี้ยยังชีพผู้สูงอายุ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับเบี้ยยังชีพผู้สูงอายุ',
					);
$qt['OTHR.5.1.10']=array(
					'label'=>'ได้รับแจกเครื่องอุปโภค บริโภคเป็นครั้งคราว',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับแจกเครื่องอุปโภค บริโภคเป็นครั้งคราว',
					);
$qt['OTHR.5.1.11']=array(
					'label'=>'ได้รับอุปกรณ์ช่วยเหลือความพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับอุปกรณ์ช่วยเหลือความพิการ',
					);

$qt['OTHR.5.1.6.1']=array(
					'label'=>'ได้รับเงินสงเคราะห์หรือเงินช่วยเหลือกรณีฉุกเฉิน-จาก',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'จาก',
					);
$qt['OTHR.5.1.7.1']=array(
					'label'=>'ได้กู้ยืมเงิน-จาก',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'จาก',
					);
$qt['OTHR.SERVICE.LOAN.AMOUNT']=array(
					'label'=>'ได้กู้ยืมเงิน-จำนวนเงินที่กู้',
					'type'=>'text',
					'group'=>'qt',
					'ret'=>'money',
					'pretext'=>'จำนวนเงินที่กู้',
					'posttext'=>'บาท',
					);

$qt['OTHR.SERVICE.NEEDEQUITY']=array(
					'label'=>'ต้องการดำเนินการให้ได้รับความเป็นธรรมเพื่อได้สิทธิ์นั้นๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ต้องการดำเนินการให้ได้รับความเป็นธรรมเพื่อได้สิทธิ์นั้นๆ',
					);
$qt['OTHR.SERVICE.NEEDEQUITY.IDENTIFY']=array(
					'label'=>'ต้องการดำเนินการให้ได้รับความเป็นธรรมเพื่อได้สิทธิ์นั้นๆ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.SERVICE.MONEY.BY']=array(
					'label'=>'ได้รับเบี้ยความพิการหรือบี้ยยังชีพผู้สูงอายุ-โดย',
					'type'=>'radio',
					'group'=>'qt',
					'pretext'=>'ได้รับอย่างไร',
					'option'=>'1:ไปรับเอง, 2:เจ้าหน้าที่นำมาให้, 3:ผู้ดูแลรับให้',
					);
$qt['OTHR.SERVICE.MONEY.FREQUENTLY']=array(
					'label'=>'ได้รับเบี้ยความพิการหรือบี้ยยังชีพผู้สูงอายุ-โดย',
					'type'=>'radio',
					'group'=>'qt',
					'pretext'=>'ได้รับอย่างไร',
					'option'=>'1:ได้รับทุกเดือน, 2:ได้รับทุกสามเดือน, 3:ไม่แน่นอน',
					);
$qt['OTHR.SERVICE.PROBLEM']=array(
					'label'=>'ปัญหาในด้านบริการ/สวัสดิการ',
					'type'=>'textarea',
					'class'=>'w-10',
					'group'=>'qt',
					);
$qt['OTHR.SERVICE.WANT']=array(
					'label'=>'ความต้องการในด้านบริการ/สวัสดิการ',
					'type'=>'textarea',
					'class'=>'w-10',
					'group'=>'qt',
					);

$qt['OTHR.5.3.1']=array(
					'label'=>'ได้รับการฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ (เช่น ฝึกการแปรงฟัน แต่งตัวหรือฝึกการนั่งซ้อนรถมอเตอร์ไซต์ เป็นต้น)',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับการฝึกทักษะเพื่อการดำรงชีวิตตามความต้องการ (เช่น ฝึกการแปรงฟัน แต่งตัวหรือฝึกการนั่งซ้อนรถมอเตอร์ไซต์ เป็นต้น)',
					);
$qt['OTHR.5.3.2']=array(
					'label'=>'เข้าร่วมกิจกรรมทางสังคม ศาสนา วัฒนธรรม ประเพณีในชุมชน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เข้าร่วมกิจกรรมทางสังคม ศาสนา วัฒนธรรม ประเพณีในชุมชน',
					);
$qt['OTHR.5.3.3']=array(
					'label'=>'ได้รับการยอมรับและให้ความสำคัญในฐานะสมาชิกครอบครัวคนหนึ่ง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ได้รับการยอมรับและให้ความสำคัญในฐานะสมาชิกครอบครัวคนหนึ่ง',
					);
$qt['OTHR.5.3.4']=array(
					'label'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรคนพิการ',
					);
$qt['OTHR.5.3.5']=array(
					'label'=>'มีส่วนร่วมในการทำประชาคมในชุมชน เป็นคณะกรรมการต่าง ๆ ของ อปท. และหรือ หน่วยงาน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'มีส่วนร่วมในการทำประชาคมในชุมชน เป็นคณะกรรมการต่าง ๆ ของ อปท. และหรือ หน่วยงาน',
					);
$qt['OTHR.5.3.6']=array(
					'label'=>'เข้าร่วมกิจกรรมกีฬานันทนาการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เข้าร่วมกิจกรรมกีฬานันทนาการ',
					);
$qt['OTHR.5.3.7']=array(
					'label'=>'มีส่วนร่วมในการทำประชาคมในชุมชน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'มีส่วนร่วมในการทำประชาคมในชุมชน',
					);
$qt['OTHR.5.3.8']=array(
					'label'=>'เป็นคณะกรรมการต่าง ๆ ในชุมชน หรืออปท.และหรือหน่วยงานราชการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นคณะกรรมการต่าง ๆ ในชุมชน หรืออปท.และหรือหน่วยงานราชการ',
					);
$qt['OTHR.5.3.9']=array(
					'label'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรในชุมชน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรในชุมชน',
					);
$qt['OTHR.5.3.10']=array(
					'label'=>'เป็นสมาชิกกลุ่มสัจจะวันละบาท',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นสมาชิกกลุ่มสัจจะวันละบาท',
					);
$qt['OTHR.5.3.11']=array(
					'label'=>'เป็นสมาชิกกองทุนหมู่บ้าน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นสมาชิกกองทุนหมู่บ้าน',
					);
$qt['OTHR.5.3.12']=array(
					'label'=>'เป็นสมาชิกกลุ่มออมทรัพย์',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เป็นสมาชิกกลุ่มออมทรัพย์',
					);
$qt['OTHR.5.3.99']=array(
					'label'=>'อื่น ๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'อื่น ๆ',
					);


$qt['OTHR.5.3.4.IDENTIFY']=array(
					'label'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรคนพิการ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.5.3.4.MEMBERID']=array(
					'label'=>'หมายเลขสมาชิก',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-1',
					'pretext' => 'หมายเลขสมาชิก',
					);

$qt['OTHR.5.3.8.IDENTIFY']=array(
					'label'=>'เป็นคณะกรรมการต่าง ๆ ในชุมชน หรืออปท.และหรือหน่วยงานราชการ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.5.3.9.IDENTIFY']=array(
					'label'=>'เป็นสมาชิก กลุ่ม/ชมรม/องค์กรในชุมชน-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.5.3.99.IDENTIFY']=array(
					'label'=>'อื่น ๆ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-3',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.SOCIAL.PROBLEM']=array(
					'label'=>'ปัญหาในด้านกิจกรรมทางสังคม',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ปัญหา',
					);
$qt['OTHR.SOCIAL.WANT']=array(
					'label'=>'ความต้องการในด้านกิจกรรมทางสังคม',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ความต้องการ',
					);

$qt['OTHR.5.4.1']=array(
					'label'=>'ใช้ประโยชน์จากสถานที่สาธารณะในพื้นที่ เช่น สวนสุขภาพ วัด ตลาด เป็นต้น ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ใช้ประโยชน์จากสถานที่สาธารณะในพื้นที่ เช่น สวนสุขภาพ วัด ตลาด เป็นต้น ได้',
					);
$qt['OTHR.5.4.2']=array(
					'label'=>'พึงพอใจในการไปใช้สื่งอำนวยความสะดวกของคนพิการและสถานที่สาธารณะในพื้นที่',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'พึงพอใจในการไปใช้สื่งอำนวยความสะดวกของคนพิการและสถานที่สาธารณะในพื้นที่',
					);
$qt['OTHR.5.4.3']=array(
					'label'=>'ไม่เคยใช้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ไม่เคยใช้',
					);
$qt['OTHR.5.4.4']=array(
					'label'=>'เคยใช้ ระบุการใช้ประโยชน์และความพึงพอใจ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เคยใช้ ระบุการใช้ประโยชน์และความพึงพอใจ',
					);
$qt['OTHR.6.5.1']=array(
					'label'=>'พาหนะเพื่อการเดินทาง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'พาหนะเพื่อการเดินทาง',
					);
$qt['OTHR.6.5.2']=array(
					'label'=>'การจัดสิ่งอานวยความสะดวกในสถานที่สาธารณะเช่น ห้องน้า ทางลาดในอาคาร ที่จอดรถ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การจัดสิ่งอานวยความสะดวกในสถานที่สาธารณะเช่น ห้องน้้ำ ทางลาดในอาคาร ที่จอดรถ',
					);
$qt['OTHR.6.5.3']=array(
					'label'=>'การเข้าถึงข้อมูล ข่าวสารด้านต่าง ๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การเข้าถึงข้อมูล ข่าวสารด้านต่าง ๆ',
					);
$qt['OTHR.PUBLICSERVICE.PROBLEM']=array(
					'label'=>'ปัญหาในด้านสิ่งอำนวยความสะดวก   การเข้าถึงและใช้ประโยชน์บริการสาธารณะ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ปัญหา',
					);
$qt['OTHR.PUBLICSERVICE.WANT']=array(
					'label'=>'ความต้องการในด้านสิ่งอำนวยความสะดวก   การเข้าถึงและใช้ประโยชน์บริการสาธารณะ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ความต้องการ',
					);

$qt['OTHR.6.4.1']=array(
					'label'=>'การจดทะเบียนคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การจดทะเบียนคนพิการ',
					);
$qt['OTHR.6.4.2']=array(
					'label'=>'การมีผู้ช่วยคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การมีผู้ช่วยคนพิการ',
					);
$qt['OTHR.6.4.3']=array(
					'label'=>'การปรับสภาพแวดล้อม/ปรับปรุงซ่อมแซมบ้าน/ปรับสภาพบ้านให้เอื้อต่อคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การปรับสภาพแวดล้อม/ปรับปรุงซ่อมแซมบ้าน/ปรับสภาพบ้านให้เอื้อต่อคนพิการ',
					);
$qt['OTHR.6.4.4']=array(
					'label'=>'การอุปการะเลี้ยงดู (สถานสงเคราะห์)',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การอุปการะเลี้ยงดู (สถานสงเคราะห์)',
					);
$qt['OTHR.6.4.5']=array(
					'label'=>'การกู้ยืม/เงินทุนประกอบอาชีพ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การกู้ยืม/เงินทุนประกอบอาชีพ',
					);
$qt['OTHR.6.4.6']=array(
					'label'=>'การฝึกอาชีพในชุมชน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การฝึกอาชีพในชุมชน',
					);
$qt['OTHR.6.4.7']=array(
					'label'=>'การจัดหางาน/อาชีพให้คนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'การจัดหางาน/อาชีพให้คนพิการ',
					);
$qt['OTHR.6.4.8']=array(
					'label'=>'ยื่นเรื่องขอเบี้ยคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ยื่นเรื่องขอเบี้ยคนพิการ',
					);
$qt['OTHR.6.4.9']=array(
					'label'=>'การได้รับสวัสดิการจากชุมชน/สังคม-อื่น ๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'อื่น ๆ',
					);
$qt['OTHR.6.4.9.1']=array(
					'label'=>'การได้รับสวัสดิการจากชุมชน/สังคม-อื่น ๆ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);
$qt['OTHR.WELFARECOMMUNITY.PROBLEM']=array(
					'label'=>'ปัญหาในด้านสวัสดิการจากชุมชน/สังคม',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ปัญหา',
					);
$qt['OTHR.WELFARECOMMUNITY.WANT']=array(
					'label'=>'ความต้องการในด้านสวัสดิการจากชุมชน/สังคม',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-10',
					'pretext'=>'ความต้องการ',
					);

$qt['OTHR.6.2.1']=array(
					'label'=>'ควรส่งต่อคนพิการเข้ารับการศึกษาที่โรงเรียนเรียนร่วม',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ควรส่งต่อคนพิการเข้ารับการศึกษาที่โรงเรียนเรียนร่วม',
					);
$qt['OTHR.6.2.2']=array(
					'label'=>'ควรส่งต่อคนพิการเข้ารับการศึกษาที่ศูนย์การศึกษาพิเศษ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ควรส่งต่อคนพิการเข้ารับการศึกษาที่ศูนย์การศึกษาพิเศษ',
					);
$qt['OTHR.6.2.3']=array(
					'label'=>'เรียนเฉพาะความพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'เรียนเฉพาะความพิการ',
					);
$qt['OTHR.6.2.4']=array(
					'label'=>'ควรส่งต่อคนพิการเข้ารับการฝึกอาชีพในศูนย์ฝึกอาชีพคนพิการ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'ควรส่งต่อคนพิการเข้ารับการฝึกอาชีพในศูนย์ฝึกอาชีพคนพิการ',
					);
$qt['OTHR.6.2.5']=array(
					'label'=>'อื่น ๆ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'อื่น ๆ',
					);
$qt['OTHR.6.2.5.1']=array(
					'label'=>'อื่น ๆ-ระบุ',
					'type'=>'text',
					'group'=>'qt',
					'class'=>'w-5',
					'pretext'=>'ระบุ',
					);

$qt['OTHR.6.6.1']=array(
					'label'=>'ความสามารถของคนพิการ',
					'type'=>'textarea',
					'group'=>'qt',
					'pretext'=>'ความสามารถของคนพิการ',
					);
$qt['OTHR.6.6.2']=array(
					'label'=>'อุปสรรคในการดำรงชีวิตของคนพิการ',
					'type'=>'textarea',
					'group'=>'qt',
					'pretext'=>'อุปสรรคในการดำรงชีวิตของคนพิการ',
					);
$qt['OTHR.6.6.3']=array(
					'label'=>'เรื่องเร่งด่วน /สำคัญที่ต้องดำเนินการ/ มีเรื่องอะไร /เพราะอะไร โปรดระบุ',
					'type'=>'textarea',
					'group'=>'qt',
					'pretext'=>'เรื่องเร่งด่วน /สำคัญที่ต้องดำเนินการ/ มีเรื่องอะไร /เพราะอะไร โปรดระบุ',
					);

// ลักษณะความพิการที่ผู้เก็บข้อมูลเห็น
$qt['DSBL.SEE.ไม่มีลูกตาทั้งสองข้าง']=array(
					'label'=>'ไม่มีลูกตาทั้งสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.1:ไม่มีลูกตาทั้งสองข้าง',
					);
$qt['DSBL.SEE.ไม่มีลูกตาดำทั้งสองข้าง']=array(
					'label'=>'ไม่มีลูกตาดำทั้งสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.2:ไม่มีลูกตาดำทั้งสองข้าง',
					);
$qt['DSBL.SEE.ลูกตาสีขาวขุ่นทั้งสองข้าง']=array(
					'label'=>'ลูกตาสีขาวขุ่นทั้งสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.3:ลูกตาสีขาวขุ่นทั้งสองข้าง',
					);
$qt['DSBL.SEE.ลูกตาฝ่อทั้งสองข้าง']=array(
					'label'=>'ลูกตาฝ่อทั้งสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.4:ลูกตาฝ่อทั้งสองข้าง',
					);
$qt['DSBL.SEE.ตาบอดสนิทสองข้าง']=array(
					'label'=>'ตาบอดสนิทสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.5:ตาบอดสนิทสองข้าง',
					);
$qt['DSBL.SEE.สายตาเลือนรางสองข้าง']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'1.6:สายตาเลือนรางสองข้าง',
					);

$qt['DSBL.SEE.ไม่มีรูหูทั้งสองข้าง']=array(
					'label'=>'ไม่มีรูหูทั้งสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'2.1:ไม่มีรูหูทั้งสองข้าง',
					);
$qt['DSBL.SEE.หูหนวกสองข้าง']=array(
					'label'=>'หูหนวกสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'2.2:หูหนวกสองข้าง',
					);
$qt['DSBL.SEE.หูตึงสองข้าง']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'2.3:หูตึงสองข้าง',
					);
$qt['DSBL.SEE.เป็นใบ้']=array(
					'label'=>'เป็นใบ้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'2.4:เป็นใบ้',
					);

$qt['DSBL.SEE.แขนและขาซีกซ้ายอ่อนแรง']=array(
					'label'=>'แขนและขาซีกซ้ายอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.1:แขนและขาซีกซ้ายอ่อนแรง',
					);
$qt['DSBL.SEE.แขนและขาซีกขวาอ่อนแรง']=array(
					'label'=>'แขนและขาซีกขวาอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.2:แขนและขาซีกขวาอ่อนแรง',
					);
$qt['DSBL.SEE.แขนและขาซีกซ้ายขยับไม่ได้']=array(
					'label'=>'แขนและขาซีกซ้ายขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.3:แขนและขาซีกซ้ายขยับไม่ได้',
					);
$qt['DSBL.SEE.แขนและขาซีกขวาขยับไม่ได้']=array(
					'label'=>'แขนและขาซีกขวาขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.4:แขนและขาซีกขวาขยับไม่ได้',
					);
$qt['DSBL.SEE.แขนและขาทั้งสองข้างขยับไม่ได้']=array(
					'label'=>'แขนและขาทั้งสองข้างขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.5:แขนและขาทั้งสองข้างขยับไม่ได้',
					);
$qt['DSBL.SEE.ขาข้างซ้ายอ่อนแรง']=array(
					'label'=>'ขาข้างซ้ายอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.6:ขาข้างซ้ายอ่อนแรง',
					);
$qt['DSBL.SEE.ขาข้างขวาอ่อนแรง']=array(
					'label'=>'ขาข้างขวาอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.7:ขาข้างขวาอ่อนแรง',
					);
$qt['DSBL.SEE.ขาข้างซ้ายขยับไม่ได้']=array(
					'label'=>'ขาข้างซ้ายขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.8:ขาข้างซ้ายขยับไม่ได้',
					);
$qt['DSBL.SEE.ขาข้างขวาขยับไม่ได้']=array(
					'label'=>'ขาข้างขวาขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.9:ขาข้างขวาขยับไม่ได้',
					);
$qt['DSBL.SEE.ขาทั้งสองข้างขยับไม่ได้']=array(
					'label'=>'ขาทั้งสองข้างขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.10:ขาทั้งสองข้างขยับไม่ได้',
					);

$qt['DSBL.SEE.แขนข้างซ้ายอ่อนแรง']=array(
					'label'=>'แขนข้างซ้ายอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.11:แขนข้างซ้ายอ่อนแรง',
					);
$qt['DSBL.SEE.แขนข้างขวาอ่อนแรง']=array(
					'label'=>'แขนข้างขวาอ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.12:แขนข้างขวาอ่อนแรง',
					);
$qt['DSBL.SEE.แขนข้างซ้ายขยับไม่ได้']=array(
					'label'=>'แขนข้างซ้ายขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.13:แขนข้างซ้ายขยับไม่ได้',
					);
$qt['DSBL.SEE.แขนข้างขวาขยับไม่ได้']=array(
					'label'=>'แขนข้างขวาขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.14:แขนข้างขวาขยับไม่ได้',
					);
$qt['DSBL.SEE.แขนทั้งสองข้างขยับไม่ได้']=array(
					'label'=>'แขนทั้งสองข้างขยับไม่ได้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.15:แขนทั้งสองข้างขยับไม่ได้',
					);

$qt['DSBL.SEE.กล้ามเนื้อเกร็ง']=array(
					'label'=>'กล้ามเนื้อเกร็ง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.16:กล้ามเนื้อเกร็ง',
					);
$qt['DSBL.SEE.อ่อนแรง']=array(
					'label'=>'อ่อนแรง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.17:อ่อนแรง',
					);
$qt['DSBL.SEE.มีข้อยึดติด']=array(
					'label'=>'มีข้อยึดติด',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.18:มีข้อยึดติด',
					);
$qt['DSBL.SEE.แขนขาดข้างขวา']=array(
					'label'=>'แขนขาดข้างขวา',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.19:แขนขาดข้างขวา',
					);
$qt['DSBL.SEE.แขนขาดข้างซ้าย']=array(
					'label'=>'แขนขาดข้างซ้าย',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.20:แขนขาดข้างซ้าย',
					);
$qt['DSBL.SEE.ขาขาดข้างขวา']=array(
					'label'=>'ขาขาดข้างขวา',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.21:ขาขาดข้างขวา',
					);
$qt['DSBL.SEE.ขาขาดข้างซ้าย']=array(
					'label'=>'ขาขาดข้างซ้าย',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'3.22:ขาขาดข้างซ้าย',
					);

$qt['DSBL.SEE.ประสาทหลอน']=array(
					'label'=>'ประสาทหลอน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'4.1:ประสาทหลอน',
					);
$qt['DSBL.SEE.หูแว่ว']=array(
					'label'=>'หูแว่ว',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'4.2:หูแว่ว',
					);
$qt['DSBL.SEE.หลงผิดหวาดระแวง']=array(
					'label'=>'หลงผิดหวาดระแวง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'4.3:หลงผิดหวาดระแวง',
					);
$qt['DSBL.SEE.พูดคนเดียว']=array(
					'label'=>'พูดคนเดียว',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'4.4:พูดคนเดียว',
					);

$qt['DSBL.SEE.กลุ่มอาการดาวน์']=array(
					'label'=>'กลุ่มอาการดาวน์',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'5.1:กลุ่มอาการดาวน์',
					);

$qt['DSBL.SEE.ด้านการอ่าน']=array(
					'label'=>'ด้านการอ่าน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6.1:ด้านการอ่าน',
					);
$qt['DSBL.SEE.ด้านการเขียน']=array(
					'label'=>'ด้านการเขียน',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6.2:ด้านการเขียน',
					);
$qt['DSBL.SEE.ด้านการคำนวณ']=array(
					'label'=>'ด้านการคำนวณ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6.3:ด้านการคำนวณ',
					);
$qt['DSBL.SEE.ด้านการเรียนรู้']=array(
					'label'=>'ด้านการเรียนรู้',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6.4:ด้านการเรียนรู้',
					);

$qt['DSBL.SEE.บกพร่องในการใช้ท่าทาง']=array(
					'label'=>'บกพร่องในการใช้ท่าทาง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'7.1:บกพร่องในการใช้ท่าทาง เช่น การสบตา การแสดงสีหน้า การแสดงความต้องการ เป็นต้น',
					);
$qt['DSBL.SEE.ทำกิริยาซ้ำ']=array(
					'label'=>'ทำกิริยาซ้ำ',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'7.2:ทำกิริยาซ้ำ ๆ เช่น โบกมือ โยกตัว เป็นต้น',
					);

$qt['DSBL.SEE.other']=array(
					'label'=>'อื่น ๆ',
					'type'=>'textarea',
					'group'=>'qt',
					'class'=>'w-8',
					);



$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);
$qt['DSBL.SEE.XXX']=array(
					'label'=>'สายตาเลือนรางสองข้าง',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'6:สายตาเลือนรางสองข้าง',
					);

















/*
$qt['DSBL.SHOULDHELP.XXX']=array(
					'label'=>'XXXX',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'XXXX',
					);
$qt['DSBL.SHOULDHELP.XXX']=array(
					'label'=>'XXXX',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'XXXX',
					);
$qt['DSBL.SHOULDHELP.XXX']=array(
					'label'=>'XXXX',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'XXXX',
					);
$qt['DSBL.SHOULDHELP.XXX']=array(
					'label'=>'XXXX',
					'type'=>'checkbox',
					'group'=>'qt',
					'option'=>'XXXX',
					);
*/
?>