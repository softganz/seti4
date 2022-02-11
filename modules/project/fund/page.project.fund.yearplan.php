<?php
/**
* Project :: Fund Year Plan
* Created 2017-06-28
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/yearplan
*/

$debug = true;

function project_fund_yearplan($self,$fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	R::View('project.toolbar',$self,$fundInfo->name,'fund',$fundInfo);

	$ret.='<h2>แผนสุขภาพตำบลกองทุนหลักประกันสุขภาพระดับพื้นที่ ปี ????</h2>';
	$ret.='<h3>สถานการณ์สุขภาพ</h3>';
	$ret.='<p>1. ปัจจัยเเสี่ยงทางสุขภาพ สารเสพติด บุหรี่ เหล้า<br /><textarea class="form-text -fill"></textarea>';
	$ret.='<p>2. ปัจจัยเสี่ยงทางความปลอดภัย เช่น อุบัติเหตุ<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>3. สถานการณ์ภาวะโภชนาการเด็ก<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>4. สถานการ์เกี่ยวกับพฤติกรรมสุขภาพ เช่น เพิ่มการออกกำลังกายหรือขยับร่างกาย<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>5. สถานการณ์ตามกลุ่มวัย</p>';
	$ret.='<p>กลุ่มหญิงตั้งครรภ์<br /><textarea class="form-text -fill"></textarea>';
	$ret.='<p>กลุ่มเด็กก่อนวัยเรียน<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>กลุ่มเด็กวัยเรียน<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>กลุ่มวัยรุ่น<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>กลุ่มผู้สูงอายุ<br /><textarea class="form-text -fill"></textarea>';
	$ret.='<p>กลุ่มคนพิการ<br /><textarea class="form-text -fill"></textarea>';
	$ret.='<p>6. สถานการณ์โรคเรื้อรัง<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>7. สถานการณ์กลุ่มผู้ประกอบอาชีพที่มีเสี่ยง<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>8. สถานการณ์โรคระบาด เช่น ไข้เลือดออก มือเท้าปาก โรคมาลาเรีย<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<p>9. สถานการณ์เกี่ยวกับอนามัยสิ่งแวดล้อม เช่น ขยะ<br /><textarea class="form-text -fill"></textarea></p>';
	$ret.='<h3<การจัดลำดับความสำคัญของปัญหา</h3>';
	$ret.='<p>ให้ระบุปัญหาในชุมชนและจัดลำดับความสำคัญจากปัญหาที่สำคัญมากไปน้อย และให้รายละเอียดแต่ละปัญหาในตารางต่อไปนี้</p>';
	$tables = new Table();
	$tables->thead=array('ประเด็นปัญหา','ขนาดของปัญหา','ความรุนแรงและผลกระทบ','ความตระหนักในการแก้ปัญหา','ความยากง่ายในการแก้ปัญหา','รวมคะแนน');
	for($i=1;$i<=5;$i++) {
		$options='';
		for ($j=0;$j<=4;$j++) $options.='<label style="display:inline-block;text-align:center;width:2em;border:1px #ddd solid;border-radius:4px;margin:2px;padding:4px;"><input type="radio" value="'.$j.'"><br />'.$j.'</label>';
		$tables->rows[]=array(
											'<input type="text" />',
											'<textarea class="form-textarea -fill"></textarea><div style="margin:8px 0;text-align:center;">'.$options.'</div>',
											'<textarea class="form-textarea -fill"></textarea><div style="margin:8px 0;text-align:center;">'.$options.'</div>',
											'<textarea class="form-textarea -fill"></textarea><div style="margin:8px 0;text-align:center;">'.$options.'</div>',
											'<textarea class="form-textarea -fill"></textarea><div style="margin:8px 0;text-align:center;">'.$options.'</div>',
											'0'
											);
	}
	$ret.=$tables->build();
	$ret.='<p><b>หมายเหตุ :</b><br />ขนาดของปัญหา 	คะแนนจาก 1 ถึง 4 คะแนน (ถ้ามีผลกระทบต่อคนจำนวนมากให้ 4 คะแนน)<br />
ความรุนแรงของปัญหา 	คะแนนจาก 1 ถึง 4 คะแนน (ถ้ามีผลกระทบต่อคนจำนวนมากให้ 4 คะแนน)<br />
ความตระหนักในปัญหา 	คะแนนจาก 1 ถึง 4 คะแนน (ถ้าคนในชุมชนให้ความตระหนักมากคะแนนจะสูง)<br />
ความยากง่ายในการแก้ปัญหา คะแนนจาก 1 ถึง 4 คะแนน (ถ้าแก้ปัญหาง่ายคะแนนให้ 1 คะแนนและยากให้ 4 คะแนน)</p>';
	$ret.='<h3>แผนงาน</h3>';
	$ret.='<h4>แผนงานที่ 1</h4>';
	$ret.='<h3>ชื่อแผนงาน : บุหรี่</h3>
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
$ret.=$tables->build();

$ret.='<h3>แผนงานที่ 4 การบริหารจัดการกองทุนสุขภาพตำบล</h3>';
$tables = new Table();
$tables->thead=array('ประเด็น','เป้าหมาย/ตัวชี้วัด','โครงการ','หน่วยงานที่รับการสนับสนุน','งบประมาณ');
$tables->rows[]=array(
									'เพิ่มประสิทธิภาพการบริหารจัดการกองทุนสุขภาพตำบล',
									'1. คณะกรรมการกองทุนสุขภาพตำบล จัดประชุมอย่างน้อย 4 ครั้ง/ปี',
									'โครงการบริหารจัดการกองทุนหลักประกันสุขภาพระดับพื้นที่ ปี 2561 แยกเป็นกิจกรรมหลัก ดังนี้',
									'สำนักเลขานุการกองทุน',
									'15% ของรายรับปี 61'
									);
$tables->rows[]=array(
									'',
									'2. โครงการสุขภาพได้รับการอนุมัติจนเงินกองทุนเหลือไม่เกิน ร้อยละ 10 ของเงินทั้งหมด',
									'4.1 ประชุมคณะกรรมการบริหารกองทุน จำนวน 4 ครั้ง/ปี*',
									'',
									'20,000 บาท'
									);
$tables->rows[]=array(
									'',
									'',
									'4.2 ประชุมคณะอนุกรรมการ.......ชุดๆละ 2 ครั้ง',
									'',
									'5,000 บาท'
									);
$tables->rows[]=array(
									'',
									'',
									'4.3 จัดซื้อวัสดุและครุภัณฑ์ จำนวน .....รายการ',
									'',
									'ไม่เกิน20,000 บ./รายการ'
									);
$tables->rows[]=array(
									'',
									'3. คณะกรรมการบริหารกองทุนมีศักยภาพเกี่ยวกับการดำเนินงานกองทุนสุขภาพตำบล',
									'4.4 พัฒนาศักยภาพคณะกรรมการ/คณะอนุกรรมการ/เจ้าหน้าที่/ผู้ที่เกี่ยวข้อง เช่น ไปประชุม สัมมนา อบรม งานสร้างสุขภาคใต้ เป็นต้น*',
									'',
									'5,000-10,000 บาท'
									);
$tables->rows[]=array(
									'',
									'4. เกิดแผนสุขภาพตำบลที่มีความครอบคลุมด้านการแก้ปัญหาสุขภาพของพื้นที่	',
									'4.5 จัดทำแผนสุขภาพตำบล ปี 2562*',
									'',
									'20,000 บาท'
									);
$ret.=$tables->build();
$ret.='<p>หมายเหตุ * กิจกรรมที่กองทุนสุขภาพตำบลต้องทำ</p>';
return $ret;
}
?>