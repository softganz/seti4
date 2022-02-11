<?php
function imed_app_poorman_admin_export($self) {
	$prov=post('prov');
	$ampur=post('ampur');
	$tambon=post('tambon');
	$export=post('export');

	$isAdmin=user_access('administer imeds');
	$zones=imed_model::get_user_zone(i()->uid,'imed.poorman');

	R::View('imed.toolbar',$self,'Export','app.poorman');

	//if (!$isAdmin) return message('error','access denied');

	$ret.='<nav class="nav -page">';
	$ret.='<form class="form -report" method="get" action="'.url('imed/app/poorman/admin/export').'">';
	$ret.='<ul>';

	// Select province
	$ret.='<li class="ui-nav">';
	$ret.='<select id="changwat" class="form-select sg-changwat" name="prov"><option value="">==ทุกจังหวัด==</option>';
	mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %qtmast% q LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` %WHERE% GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret.='</select> ';
	$ret.='<select id="ampur" class="form-select sg-ampur'.($prov?'':' -hidden').'" name="ampur"><option value="">== ทุกอำเภอ ==</option>';
	if ($prov) {
		$stmt='SELECT * FROM %co_district% WHERE LEFT(`distid`,2)=:prov';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.substr($rs->distid,2,2).'" '.($ampur==substr($rs->distid,2,2)?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
	}
	$ret.='</select> ';
	$ret.='<select id="tambon" class="form-select sg-tambon'.($ampur?'':' -hidden').'" name="tambon"><option value="">== ทุกตำบล ==</option>';
	if ($ampur) {
		$stmt='SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4)=:ampur';
		$dbs=mydb::select($stmt,':ampur',$prov.$ampur);
		//debugMsg($dbs,'$dbs');
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.substr($rs->subdistid,4,2).'" '.($tambon==substr($rs->subdistid,4,2)?'selected="selected"':'').'>'.$rs->subdistname.'</option>';
		}
	}
	$ret.='</select>';


	$ret.='&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	$ret.='&nbsp;&nbsp;<button type="submit" class="btn" name="export" value="excel"><i class="icon -download"></i><span>Export</span></button>';
	$ret.='</li>';
	//$ret.='<li>';
	//$ret.='<select class="form-select" name="sex"><option value="" />ทุกเพศ</option><option value="1">ชาย</option><option value="2">หญิง</option></option></select>';
	//$ret.='</li>';
	$ret.='</ul></form>';
	$ret.='</nav>';


$fldList['col-no QTMAST.NO']='ลำดับ';
$fldList['QTMAST.QTREF']='แบบสำรวจลำดับที่';
$fldList['PSNL.PRENAME']='คำนำหน้า';
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
$fldList['POOR.CAUSE.LIST.99']='ไม่มีสถานะทางทะเบียนราษฎร์';
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

					
/*
ลำดับ	แบบสำรวจลำดับที่	คำนำหน้า	ชื่อ	นามสกุล	เลขที่บัตรประชาชน	กรณีไม่มีบัตรเนื่องจาก	วันเดือนปีเกิด	เพศ	เชื้อชาติ	สัญชาติ	ศาสนา	สถานภาพสมรส				

ลำดับ	แบบสำรวจลำดับที่	ชื่อสถานที่	รหัสประจำบ้าน	บ้านเลขที่	หมู่ที่	ตรอก	ซอย	ถนน	ตำบล	อำเภอ	จังหวัด	ที่อยู่ปัจจุบัน	ชื่อสถานที่	รหัสประจำบ้าน	บ้านเลขที่	หมู่ที่	ตรอก	ซอย	ถนน	ตำบล	อำเภอ	จังหวัด	โทรศัพท์ 1	โทรศัพท์2

ลำดับ	แบบสำรวจลำดับที่	ระดับการศึกษา	อาชีพ	ระบุรายละเอียด	รายได้เฉลี่ยต่อเดือน	ที่มาของรายได้	ผู้ให้ข้อมูล	ระบุชื่อผู้ให้ข้อมูลแทน						

ลำดับ	แบบสำรวจลำดับที่	สภาวะความยากลำบาก	1	2	3	4	5	6	7	8	9	10	11	12	13	14	ระบุสาเหตุความยากลำบาก				

ลำดับ	แบบสำรวจลำดับที่	สถานะทางสุขภาพ	ระบุรายละเอียดสุขภาพ	สิ่งที่ต้องการให้รัฐช่วยเหลือ	ระบุรายละเอียดความช่วยเหลืออื่น ๆ ที่ต้องการ	เคยได้รับความช่วยเหลือจากหน่วยงานใดบ้าง	รายละเอียดความช่วยเหลือจากหน่วยงาน									
ลำดับ	แบบสำรวจลำดับที่	สิ่งที่ต้องการให้ชุมชนช่วยเหลือ	สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน	ประวัติเพิ่มเติม	ประวัติครอบครัว	ผู้สำรวจ	วันที่สำรวจ												
*/

	mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
	if ($prov) mydb::where('p.`changwat`=:changwat',':changwat',$prov);
	if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
	if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);


	// Sex
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND tr.`part` IN ("PSNL.SEX")
					GROUP BY `value`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('หัวข้อแบบสำรวจ','amt'=>'จำนวน');
	$tables->rows[]=array('เพศ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->value,$rs->amt);
	}

	// Married
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND tr.`part` IN ("PSNL.MARRIED")
					GROUP BY `value`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);
	$tables->rows[]=array('สถานภาพสมรส','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->value,$rs->amt);
	}

	$qtChoices['PSNL.HOME.STATUS']=array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง');

	// ที่อยู่อาศัย
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND tr.`part` IN ("PSNL.HOME.STATUS")
					GROUP BY `value`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('ที่อยู่อาศัย','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.HOME.STATUS'][$rs->value],$rs->amt);
	}

	$qtChoices['PSNL.EDUCA']=array(1=>'ไม่ได้รับการศึกษา/ไม่จบชั้นประถมศึกษาตอนต้น','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษาตอนต้น','มัธยมศึกษาตอนปลาย','ปวช./อนุปริญญา','ปริญญาตรี','สูงกว่าปริญญาตรี');

	// ระดับการศึกษา
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND tr.`part` IN ("PSNL.EDUCA")
					GROUP BY `value`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('ระดับการศึกษา','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.EDUCA'][$rs->value],$rs->amt);
	}

	$qtChoices['PSNL.OCCUPA']=array(1=>'ไม่มีอาชีพ/ว่างงาน','นักเรียน/นักศึกษา','ค้าขาย/ธุรกิจส่วนตัว','ภิกษุ/สามเณร/แม่ชี','เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)','ข้าราชการ/พนักงานของรัฐ','พนักงานรัฐวิสาหกิจ','พนักงานบริษัท','รับจ้าง',99=>'อื่น ๆ');

	// อาชีพ
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND tr.`part` IN ("PSNL.OCCUPA")
					GROUP BY `value`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('อาชีพ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.OCCUPA'][$rs->value],$rs->amt);
	}

	// สภาวะความยากลำบาก
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,15) IN ("POOR.TYPE.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สภาวะความยากลำบาก','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สาเหตุความยากลำบาก
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,16) IN ("POOR.CAUSE.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สาเหตุความยากลำบาก','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สถานะทางสุขภาพ
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,17) IN ("POOR.HEALTH.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สถานะทางสุขภาพ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สิ่งที่ต้องการให้รัฐช่วยเหลือ
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,19) IN ("POOR.NEED.GOV.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สิ่งที่ต้องการให้รัฐช่วยเหลือ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// เคยได้รับความช่วยเหลือจาก
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,19) IN ("POOR.HELP.ORG.YES","POOR.HELP.ORG.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('เคยได้รับความช่วยเหลือจาก','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สิ่งที่ต้องการให้รัฐช่วยเหลือ
	$stmt='SELECT
					  tr.`part`
					, tr.`value`
					, COUNT(*) `amt`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE% AND SUBSTR(tr.`part`,1,25) IN ("POOR.NEED.COMMUNITY.LIST.")
					GROUP BY `part`;
					-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สิ่งที่ต้องการให้รัฐช่วยเหลือ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');









	if (!$prov) return $ret;


	if (!$isAdmin) mydb::where('(q.`uid`=:uid'.($zones?' OR ('.R::Model('imed.person.zone.condition',$zones).')':'').')',':uid',i()->uid);

	$orderby='`areacode`';
	if ($ampur) $orderby='`qtref`';
	$stmt='SELECT
					  q.`psnid`
					, tr.`part`
					, tr.`value`
					, q.`qtref`
					, q.`collectname`
					, q.`qtdate`
					, q.`qtstatus`
					, CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,p.`village`) `areacode`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
						LEFT JOIN %qttran% tr USING(`qtref`)
					%WHERE%
					ORDER BY '.$orderby.' ASC ;
					-- {sum:"totalType"}';
	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query;

	$tables = new Table();

	$tables->thead=$fldList;
	$no=0;
	foreach ($dbs->items as $rs) {
		if (!in_array($rs->part, array_keys($fldList))) continue;
		if (empty($tables->rows[$rs->qtref])) {
			foreach ($fldList as $k=>$v) $tables->rows[$rs->qtref][$k]='';
			$tables->rows[$rs->qtref]['col-no QTMAST.NO']=++$no;
			$tables->rows[$rs->qtref]['QTMAST.QTREF']=$rs->qtref;
			$tables->rows[$rs->qtref]['COLLECTOR.NAME']=$rs->collectname;
			$tables->rows[$rs->qtref]['COLLECTOR.DATE']=$rs->qtdate;
			$tables->rows[$rs->qtref]['QTMAST.QTSTATUS']=$rs->qtstatus;
		}
		$tables->rows[$rs->qtref][$rs->part]=(string)$rs->value;
	}

	if ($export) {
		die(R::Model('excel.export',$tables,'คนยากลำบาก-'.$prov.'-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
		return $ret;
	}

	$ret.=$tables->build();

	$ret.='<p>รวมทั้งสิ้น '.count($tables->rows).' รายการ</p>';
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>