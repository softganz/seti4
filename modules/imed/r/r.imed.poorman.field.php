<?php
function r_imed_poorman_field() {
	$fldList['col-no QTMAST.NO']='ลำดับ';
	$fldList['QTMAST.QTREF']='แบบสำรวจลำดับที่';
	$fldList['PSNL.PRENAME']='คำนำหน้า';
	$fldList['PSNL.FULLNAME']='ชื่อ-นามสกุล';
	$fldList['PSNL.NAME']='ชื่อ';
	$fldList['PSNL.LNAME']='นามสกุล';
	$fldList['PSNL.CID']='เลขที่บัตรประชาชน';
	$fldList['PSNL.NOIDCARD']='กรณีไม่มีบัตรเนื่องจาก';
	$fldList['PSNL.BIRTH']='วันเดือนปีเกิด';
	$fldList['PSNL.SEX']='เพศ';
	$fldList['PSNL.RACE']='เชื้อชาติ';
	$fldList['PSNL.NATION']='สัญชาติ';
	$fldList['PSNL.RELIGION']='ศาสนา';
	$fldList['PSNL.MARRIED']='สถานภาพสมรส';
	$fldList['PSNL.REGIST.PLACENAME']='ชื่อสถานที่';
	$fldList['PSNL.REGIST.HOUSEID']='รหัสประจำบ้าน';
	$fldList['PSNL.REGIST.ADDRESS']='ที่อยู่ตามทะเบียนบ้าน';
	$fldList['PSNL.REGIST.ZIP']='รหัสไปรษณีย์';
	$fldList['PSNL.REGIST.PHONE']='โทรศัพท์ 1';
	$fldList['PSNL.REGIST.MOBILE']='โทรศัพท์ 2';
	$fldList['PSNL.REGIST.AREACODE']='รหัสพื้นที่';
	$fldList['PSNL.HOME.STATUS']='สถานะของที่พักอาศัย';
	$fldList['PSNL.HOME.NOTSAMEADDRESS']='ที่อยู่ปัจจุบันกับที่อยู่ตามทะเบียนบ้าน';
	$fldList['PSNL.HOME.PLACENAME']='ชื่อสถานที่';
	$fldList['PSNL.HOME.HOUSEID']='รหัสประจำบ้าน';
	$fldList['PSNL.HOME.ADDRESS']='ที่อยู่ปัจจุบัน';
	$fldList['PSNL.HOME.ZIP']='รหัสไปรษณีย์';
	$fldList['PSNL.HOME.PHONE']='โทรศัพท์ 1';
	$fldList['PSNL.HOME.MOBILE']='โทรศัพท์ 2';
	$fldList['PSNL.HOME.AREACODE']='รหัสพื้นที่';
	$fldList['PSNL.EDUCA']='ระดับการศึกษา';
	$fldList['PSNL.OCCUPA']='อาชีพ';


	$fldList['ECON.OCCUPY.DETAIL']='รายละเอียดอาชีพ';
	$fldList['ECON.INCOME.MONTHLY.AMT']='รายได้เฉลี่ยต่อเดือน';
	$fldList['ECON.INCOME.FROM']='ที่มาของรายได้';
	$fldList['PSNL.INFO.BYTYPE']='ผู้ให้ข้อมูล';
	$fldList['PSNL.INFO.BYNAME']='ชื่อผู้ให้ข้อมูล';

	$fldList['POOR.TYPE.LIST.1']='คนไร้บ้าน';
	$fldList['POOR.TYPE.LIST.2']='คนไร้สัญชาติ';
	$fldList['POOR.TYPE.LIST.3']='ผู้สูงอายุที่ถูกทอดทิ้ง';
	$fldList['POOR.TYPE.LIST.4']='ผู้ติดเชื้อ';
	$fldList['POOR.TYPE.LIST.5']='ผู้ป่วยติดบ้าน/ติดเตียง';
	$fldList['POOR.TYPE.LIST.6']='อดีตผู้ต้องขัง';
	$fldList['POOR.TYPE.LIST.7']='คนพิการ';
	$fldList['POOR.TYPE.LIST.8']='ผู้ได้รับผลกระทบจากสถานการณ์';
	$fldList['POOR.TYPE.LIST.9']='เด็กกำพร้า (ทั่วไป/สถานการณ์)';
	$fldList['POOR.TYPE.LIST.10']='ผู้มีรายได้น้อย/ผู้ขัดสน(ซะกาต)';
	$fldList['POOR.TYPE.LIST.11']='กลุ่มชาติพันธุ์';

	$fldList['POOR.CAUSE.LIST.1']='ยากจน / รายได้น้อย';
	$fldList['POOR.CAUSE.LIST.2']='มีหนี้สิน';
	$fldList['POOR.CAUSE.LIST.3']='ตกงาน / ไม่มีงานทำ / ไม่มีอาชีพ';
	$fldList['POOR.CAUSE.LIST.4']='ขาดผู้อุปการะ';
	$fldList['POOR.CAUSE.LIST.5']='ขาดความรู้ที่จะประกอบอาชีพ';
	$fldList['POOR.CAUSE.LIST.6']='ปัญหาครอบครัว';
	$fldList['POOR.CAUSE.LIST.7']='ไม่มีที่อยู่อาศัย / ไม่มีที่ดินทำกิน';
	$fldList['POOR.CAUSE.LIST.8']='ถูกชักจูงโดยคนรู้จัก / เพื่อน';
	$fldList['POOR.CAUSE.LIST.9']='ถูกบังคับ / ล่อลวง / แสวงหาผลประโยชน์';
	$fldList['POOR.CAUSE.LIST.10']='ไม่มีสถานะทางทะเบียนราษฎร์';
	$fldList['POOR.CAUSE.LIST.11']='ขาดโอกาสทางการศึกษาตามเกณฑ์';
	$fldList['POOR.CAUSE.LIST.12']='เจ็บป่วยเรื้อรัง';
	$fldList['POOR.CAUSE.LIST.13']='ช่วยเหลือตนเองไม่ได้ในชีวิตประจำวัน';
	$fldList['POOR.CAUSE.LIST.99']='อื่น ๆ';
	$fldList['POOR.CAUSE.DETAIL']='สาเหตุความยากลำบาก';

	$fldList['POOR.HEALTH.LIST.1']='สุขภาพ - ปกติ';
	$fldList['POOR.HEALTH.LIST.2']='สุขภาพ - ผู้สูงอายุ';
	$fldList['POOR.HEALTH.LIST.3']='สุขภาพ - เจ็บป่วย';
	$fldList['POOR.HEALTH.LIST.4']='สุขภาพ - พิการ';
	$fldList['POOR.HEALTH.LIST.99']='สุขภาพ - อื่น ๆ';
	$fldList['POOR.HEALTH.DETAIL']='รายละเอียดสถานะทางสุขภาพในปัจจุบัน';
	$fldList['POOR.HEALTH.DISABLED.1']='พิการ - ทางการเห็น';
	$fldList['POOR.HEALTH.DISABLED.2']='พิการ - ทางการได้ยินหรือสื่อความหมาย';
	$fldList['POOR.HEALTH.DISABLED.3']='พิการ - ทางการเคลื่อนไหวหรือทางร่างกาย';
	$fldList['POOR.HEALTH.DISABLED.4']='พิการ - ทางจิตใจหรือพฤติกรรม';
	$fldList['POOR.HEALTH.DISABLED.5']='พิการ - ทางสติปัญญา';
	$fldList['POOR.HEALTH.DISABLED.6']='พิการ - ทางการเรียนรู้';
	$fldList['POOR.HEALTH.DISABLED.7']='พิการ - ทางออทิสติก';

	$fldList['POOR.NEED.GOV.LIST.1']='รัฐช่วย - เข้าสถานสงเคราะห์';
	$fldList['POOR.NEED.GOV.LIST.2']='รัฐช่วย - กลับภูมิลำเนา';
	$fldList['POOR.NEED.GOV.LIST.3']='รัฐช่วย - ฝึกอาชีพ';
	$fldList['POOR.NEED.GOV.LIST.4']='รัฐช่วย - หางานทำ';
	$fldList['POOR.NEED.GOV.LIST.5']='รัฐช่วย - ที่พักชั่วคราว';
	$fldList['POOR.NEED.GOV.LIST.6']='รัฐช่วย - เงินทุนประกอบอาชีพ';
	$fldList['POOR.NEED.GOV.LIST.7']='รัฐช่วย - เงินสงเคราะห์ช่วยเหลือ';
	$fldList['POOR.NEED.GOV.LIST.8']='รัฐช่วย - รักษาพยาบาล';
	$fldList['POOR.NEED.GOV.LIST.9']='รัฐช่วย - ทำบัตรประชาชน';
	$fldList['POOR.NEED.GOV.LIST.99']='รัฐช่วย - อื่น ๆ';
	$fldList['POOR.NEED.GOV.DETAIL']='รายละเอียดสิ่งที่ต้องการให้รัฐช่วยเหลือ';

	$fldList['POOR.HELP.ORG.YES']='เคยได้รับความช่วยเหลือจากหน่วยงาน';
	$fldList['POOR.HELP.ORG.LIST.1']='บริการ';
	$fldList['POOR.HELP.ORG.LIST.2']='เงิน';
	$fldList['POOR.HELP.ORG.LIST.3']='สิ่งของ';
	$fldList['POOR.HELP.ORG.DETAIL']='รายละเอียดความช่วยเหลือจากหน่วยงาน';
	$fldList['POOR.HELP.ORG.YEAR']='เมื่อปี';
	$fldList['POOR.HELP.ORG.NAME']='จากหน่วยงาน';


	$fldList['POOR.NEED.COMMUNITY.LIST.1']='ชุมชนช่วย - ซ่อมแซมที่อยู่อาศัย';
	$fldList['POOR.NEED.COMMUNITY.LIST.2']='ชุมชนช่วย - อาหาร';
	$fldList['POOR.NEED.COMMUNITY.LIST.3']='ชุมชนช่วย - ฝึกอาชีพ';
	$fldList['POOR.NEED.COMMUNITY.LIST.4']='ชุมชนช่วย - ให้งานทำ';
	$fldList['POOR.NEED.COMMUNITY.LIST.5']='ชุมชนช่วย - ของใช้ในชีวิต';
	$fldList['POOR.NEED.COMMUNITY.LIST.6']='ชุมชนช่วย - เครื่องนุ่งห่ม';
	$fldList['POOR.NEED.COMMUNITY.LIST.7']='ชุมชนช่วย - เงินสงเคราะห์ช่วยเหลือ';
	$fldList['POOR.NEED.COMMUNITY.LIST.8']='ชุมชนช่วย - วัสดุเพื่อการรักษาพยาบาล';
	$fldList['POOR.NEED.COMMUNITY.LIST.99']='ชุมชนช่วย - อื่นๆ';
	$fldList['POOR.NEED.COMMUNITY.DETAIL']='รายละเอียดสิ่งที่ต้องการ';


	$fldList['POOR.ECON.INCOME.YES']='รายรับ';
	$fldList['POOR.ECON.EXPENSE.YES']='รายจ่าย';
	$fldList['POOR.ECON.LOAN.YES']='หนี้สิน';
	$fldList['POOR.ECON.DETAIL']='รายละเอียด สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน';



	$fldList['POOR.PSNL.CARETAKER.NAME']='ชื่อผู้ดูแลหรือผู้ปกครอง';
	$fldList['POOR.PSNL.CARETAKER.RELATION']='ความสัมพันธ์';
	$fldList['POOR.PSNL.CARETAKER.ADDRESS']='ที่อยู่ผู้ดูแล';
	$fldList['POOR.PSNL.CARETAKER.ZIP']='รหัสไปรษณีย์';
	$fldList['POOR.PSNL.CARETAKER.PHONE']='เบอร์โทรศัพท์บ้าน';
	$fldList['POOR.PSNL.CARETAKER.MOBILE']='เบอร์มือถือ';
	$fldList['POOR.PSNL.HISTORY.MORE']='ประวัติ(เพิ่มเติม)';
	$fldList['POOR.PSNL.HISTORY.FAMILY']='ประวัติครอบครัว';


	$fldList['COLLECTOR.NAME']='ผู้จัดเก็บข้อมูล';
	$fldList['COLLECTOR.DATE']='วันที่เก็บข้อมูล';
	$fldList['COLLECTOR.PHONE']='เบอร์โทรศัพท์';
	$fldList['COLLECTOR.IDLINE']='ID LINE';

	$fldList['QTMAST.QTSTATUS']='สถานะแบบสอบถาม';


	$fldList['PSNL.OCCUPA.CHOICE']=array(
		1=>'ไม่มีอาชีพ/ว่างงาน',
		'นักเรียน/นักศึกษา',
		'ค้าขาย/ธุรกิจส่วนตัว',
		'ภิกษุ/สามเณร/แม่ชี',
		'เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)',
		'ข้าราชการ/พนักงานของรัฐ',
		'พนักงานรัฐวิสาหกิจ',
		'พนักงานบริษัท',
		'รับจ้าง',
		'99'=>'อื่น ๆ'
	);

	$fldList['PSNL.EDUCA.CHOICE']=array(1=>'ไม่ได้รับการศึกษา/ไม่จบชั้นประถมศึกษาตอนต้น','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษาตอนต้น','มัธยมศึกษาตอนปลาย','ปวช./อนุปริญญา','ปริญญาตรี','สูงกว่าปริญญาตรี');


	$fldList['PSNL.HOME.STATUS.CHOICE']=array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง');

	$fldList['PSNL.MARRIED.CHOICE']=array('โสด'=>'โสด','สมรสอยู่ด้วยกัน'=>'สมรสอยู่ด้วยกัน','สมรสแยกกันอยู่'=>'สมรสแยกกันอยู่','หย่าร้าง'=>'หย่าร้าง','ไม่ได้สมรสแต่อยู่ด้วยกัน'=>'ไม่ได้สมรสแต่อยู่ด้วยกัน','หม้าย (คู่สมรสเสียชีวิต)'=>'หม้าย (คู่สมรสเสียชีวิต)');


	return $fldList;
}
?>