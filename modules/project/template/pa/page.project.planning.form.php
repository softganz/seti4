<?php
// TODO: Move $sitList to table tag::taggroup=project:planning
// XXX: Test for XXX and TODO
// FIXME: Please fix error
// CHANGED: This code was change
// NOTE: This is a note
function project_planning_form($self,$orgid,$tpid) {
	R::View('project.toolbar',$self,'แผนสุขภาพชุมชน');

	/*

	 แผน

	 สถานการณ์
	 เด็ก/วัยเรียน	...%
	 Setting
	 	บ้าน
	 	โรงเรียน

	 แนวทาง
	 	๑. การจัดการความรู้/นวัตกรรม/อื่นๆ textarea
	 	๒. 
	*/

	$sitList=array(1=>'เหล้า','บุหรี่','สารเสพติด','โรคเรื้อรัง','อาหารปลอดภัย','โภชนาการ','กิจกรรมทางกาย','อุบัติเหต','อนามัยแม่และเด็ก','เด็ก เยาวชน ครอบครัว','ผู้สูงอายุ','สิ่งแวดล้อม');

	$tables = new Table();
	$tables->thead=array('ชื่อปัญหา','center -size'=>'ขนาดปัญหา(%)','center -target'=>'เป้าหมาย(%)','icons -c1'=>'');
	$ui=new Ui(NULL,'card -planning');
	foreach ($sitList as $sitKey=>$sitValue) {
		$ui->add('<h3>'.$sitValue.'</h3><a class="btn" href="#sit-'.$sitKey.'"><i class="icon -addbig"></i></a>');
		$tables->rows[]=array($sitValue,'<input type="text" size="3" />','<input type="text" size="3" />','');
		if ($sitKey>=5) break;
	}
	$sitSelect='<select class="form-select"><option>== เลือก ==</option>';
	foreach ($sitList as $sitKey=>$sitValue) {
		if ($sitKey<=5) continue;
		$sitSelect.='<option value="'.$sitKey.'">'.$sitValue.'</option>';
	}
	$sitSelect.='</select>';
	$ui->add('<h3>สถานการณ์อื่นๆ</h3><form>'.$sitSelect.'<button class="btn"><i class="icon -addbig"></i></button>'.'</form>');
	$tables->rows[]=array($sitSelect,'<input type="text" size="3" />','<input type="text" size="3" />','<a href="javascript:void(0)"><i class="icon -addbig -gray -circle"></i></a>');

	$ret.=$ui->build();

	/*
	$ret.='<div class="box clear">';
	$ret.='<h3>สถานการณ์สุขภาพ</h3>';
	$ret.=$tables->build();
	$ret.='</div>';
	*/

	head(
		'<style type="text/css">
		.card.-planning .ui-item {height:160px; text-align:center;border-top:1px #ddd solid;border-left:1px #ddd solid;}
		.card.-planning .btn {width:24px;height:24px;padding:32px;border-radius:50%;display:block;margin:8px auto;}
		</style>');

	$ret.='<h3>รายละเอียดแผนงาน</h3>';

	foreach ($sitList as $sitKey => $sitValue) {
		$ret.='<a name="sit-'.$sitKey.'"></a>';
		$ret.='<div class="box">';
		$ret.='<h3>'.$sitValue.'</h3>';
		$ret.='<h4>สถานการณ์ปัจจุบันและเป้าหมาย</h4>';
		$ret.='สถานการณ์'.$sitValue.' ขนาดปัญหาจำนวน ?? % เป้าหมายจำนวน ?? %';
		$ret.='<h4>รายละเอียดสถานการณ์</h4>';
		$ret.='<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์"></textarea>';


		$ret.='<h4>ขนาดปัญหา</h4>';
		$tables = new Table();
		$tables->thead=array('ชื่อปัญหา','center -size'=>'ขนาดปัญหา(%)','center -target'=>'เป้าหมาย(%)','icons -c1'=>'');
		$tables->rows[]=array('<input class="form-text -fill" type="text" value="" placeholder="ระบุชื่อปัญหา" />','<input type="text" size="3" placeholder="0.00" />','<input type="text" size="3" placeholder="0.00" />','');
		$tables->rows[]=array('<input class="form-text -fill" type="text" value="" placeholder="ระบุชื่อปัญหา" />','<input type="text" size="3" placeholder="0.00" />','<input type="text" size="3" placeholder="0.00" />','');
		$tables->rows[]=array('<input class="form-text -fill" type="text" value="" placeholder="ระบุชื่อปัญหา" />','<input type="text" size="3" placeholder="0.00" />','<input type="text" size="3" placeholder="0.00" />','');
		$ret.=$tables->build();

		$ret.='<h4>วัตถุประสงค์</h4>';

		$tables = new Table();
		$tables->thead=array('no'=>'','วัตถุประสงค์','ตัวชี้วัด');
		$tables->rows[]=array(
											1,
											'<input class="form-text -fill" type="text" value="เพื่อ....." />',
											'ลดเหลือน้อยกว่า ?? %'
											);
		$tables->rows[]=array(
											2,
											'<input class="form-text -fill" type="text" value="เพื่อ....." />'
											,'ลดเหลือน้อยกว่า ?? %'
											);
		$tables->rows[]=array(
											3,'<input class="form-text -fill" type="text" value="เพื่อ....." />',
											'ลดเหลือน้อยกว่า ?? %'
											);
		$ret.=$tables->build();

		$ret.='<h4>แนวทาง/วิธีการสำคัญ</h4>';
		$tables = new Table();
		$tables->thead=array('no'=>'','แนวทาง','วิธีการ');
		$tables->rows[]=array(
											1,
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">ให้ดำเนินการลด.....</textarea>',
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">จัดให้มีการทำ...</textarea>'
											);
		$tables->rows[]=array(
											2,
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">ให้ดำเนินการลด.....</textarea>',
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">จัดให้มีการทำ...</textarea>'
											);
		$tables->rows[]=array(
											3,
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">ให้ดำเนินการปรับปรุง.....</textarea>',
											'<textarea class="form-textarea -fill" rows="10" placeholder="รายละเอียดสถานการณ์">จัดให้มีการทำ...</textarea>'
											);
		$ret.=$tables->build();

		$ret.='<h4>งบประมาณ</h4><input class="form-text" type="text" /> บาท';

		$ret.='<h4>โครงการย่อย</h4>';
		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อโครงการ','งบประมาณ');
		$tables->rows[]=array(
											1,
											'<input class="form-text -fill" value="โครงการ....." />',
											'<input class="form-text -fill -money" value="0.00" />'
											);
		$tables->rows[]=array(
											2,
											'<input class="form-text -fill" value="โครงการ....." />',
											'<input class="form-text -fill -money" value="0.00" />'
											);
		$tables->rows[]=array(
											3,
											'<input class="form-text -fill" value="โครงการ....." />',
											'<input class="form-text -fill -money" value="0.00" />'
											);
		$tables->rows[]=array(
											4,
											'<input class="form-text -fill" value="โครงการ....." />',
											'<input class="form-text -fill -money" value="0.00" />'
											);
		$tables->rows[]=array(
											5,
											'<input class="form-text -fill" value="โครงการ....." />',
											'<input class="form-text -fill -money" value="0.00" />'
											);
		$ret.=$tables->build();

		$ret.='</div>';
	}


'
1. สถานการณ์   เป้าหมาย

<h4>วิธีการ</h4>

1 จัดการความรู้
	1. วิธีการ
	2. วิธีการ ๒
2 


โครงการที่ควรทำ
1
2

<h3>ชื่อแผนงาน : บุหรี่</h3>
<h3>สถานการณ์สุขภาพ</h3>

<h3>วิธีการสำคัญ</h3>
1. จัดบริการ
	<h4>โครงการ</h4>
	1. ชื่อโครงการ
	<h4>กลุ่มเป้าหมาย</h4>
	<h4>วิธีการ</h4>
2. มาตรการทางสังคม
3. กฎหมาย
4. นโยบาย';

$tables = new Table();
$tables->thead=array('แผนงาน','วิธีการสำคัญ','เป้าหมาย','โครงการ');
$tables->rows[]=array('บุหรี่','จัดบริการ','1.<br />2.<br />3.<br />','1.<br />2.<br />3.<br />');
return $ret;
}
?>