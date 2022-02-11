<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_situation($self,$topic) {
	$tpid=$topic->tpid;
	$ret.='<h3>สถานการณ์โครงการ</h3>';

	$stmt='SELECT `formid`,COUNT(*) amt FROM %project_tr% WHERE `tpid`=:tpid AND formid IN ("weight","schooleat","kamsaiindi","learn") AND `part`="title" GROUP BY `formid` ';
	$dbs=mydb::select($stmt,':tpid',$tpid);
	foreach ($dbs->items as $rs) $counts[$rs->formid]=$rs->amt;

	$tables = new Table();
	$tables->thead=array('ชื่อสถานการณ์','amt'=>'จำนวนบันทึก');

	//$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/school').'">ข้อมูลโรงเรียน</a>','');
	//$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/eat').'">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</a>',number_format($counts['schooleat']));
	//$tables->rows[]=array('<a href="">ข้อมูลการสำรวจสถานการณ์การกินอาหารและออกกำลังกายของนักเรียนโดย สอส.</a>','');
	//$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/weight').'">สถานการณ์ภาวะโภชนาการนักเรียน</a>',number_format($counts['weight']));
	//$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/learn').'">ผลสัมฤทธิ์ทางการเรียน</a>',number_format($counts['learn']));
	//$ret.=$tables->build();

	//$ret.='<h3>แบบฟอร์ม</h3>';

	//$tables = new Table();
	//$tables->thead=array('ชื่อแบบฟอร์ม','amt'=>'จำนวนบันทึก');

	/*
	$tables->rows[]=array('<td colspan="2"><h3>แบบฟอร์ม</h3></td>');
	$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/kamsaiindicator').'">การประเมิน "ศูนย์เรียนรู้เด็กไทยแก้มใส"</a>',number_format($counts['kamsaiindi']));

	$valuationCount=mydb::select('SELECT COUNT(*) `total` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ประเมิน" LIMIT 1',':tpid',$tpid)->total;
	$tables->rows[]=array('<a href="'.url('paper/'.$tpid.'/situation/valuation').'">แบบฟอร์มการสังเคราะห์คุณค่าของโครงการ</a>',$valuationCount?1:'-');
	*/

	$ret.=$tables->build();

	/*
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/kamsaiindicator').'">แบบฟอร์มการประเมิน "โรงเรียนต้นแบบเด็กไทยแก้มใส"</a>');
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/eat').'">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</a>');
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/weight').'">สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย</a>');
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/height').'">สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูง</a>');
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/vegetable').'">สถานการณ์การกินผัก</a>');
	$ret.=$ui->build('ul');

	$ret.='<h3>เงื่อนไข 9 ข้อ</h3>';

	$ret.='<h3>ตัวชี้วัด 16 ตัว</h3>';
	*/

/*
ประเมินผล
- การสังเคราะห์เพื่อการเป็นศูนย์เรียนรู้ : ภาพรวมของโรงเรียน
- รูปแบบที่เป็นตัวอย่างดี ๆ ๑.ชื่อประเด็น ๒.ลักษณะ/ขั้นตอน/รายละเอียด
- ปัจจัยสำคัญที่ทำให้เกิดการพัฒนาเป็นรูปแบบดีๆ : กลุ่มคน/ภาคีหลัก/ภาคียุทธศาสตร์ ๒. สภาพแวดล้อมที่เอื้ ๓.กลไก ๔.กระบวนการเรียนรู้ ๕.กระบวนการมีส่วนร่วม
- สรุปผลลัพธ์สำคัญ : ความรู้ นวัตกรรม เศรษฐกิจชุมชน ความมั่นคงทางอาหาร ควาปลอดภัยด้านอาหาร โภชนากร
*/

/*
	$ret.='<h3>รายงานสรุป</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('paper/'.$tpid.'/situation/standardsum').'">สรุปการประเมินผล</a>');
	$ret.=$ui->build('ul');
*/
	return $ret;
}
?>