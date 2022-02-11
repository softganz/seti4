<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_monitor_status($self,$para) {
	$self->theme->title.=' : โทรมาตร';
	$station=post('s');

	$stmt='SELECT * FROM %flood_station% WHERE `station`=:station LIMIT 1';
	$rs=mydb::select($stmt,':station',$station);

	list($rainHistory)=flood_model::rainavg($rs->basin);
	$rain=$rainHistory[$station];

	$ret.='<h3>'.$rs->title.' ('.$rs->station.')</h3>';
	$ret.='<p>'.$rs->description.'</p>';

	$statusCommu=sg_date($rs->waterupdate,'U')<date('U')-30*60?'ขาดการติดต่อ':'ปกติ';

	$last_photo=flood_model::sensor_photo($rs->station,$rs->last_photo);
	$ret.='<div><h4>ภาพล่าสุด เมื่อ '.sg_date($rs->last_updated,'ว ดด ปปปป H:i').' น.</h4><img src="'.$last_photo.'" width="100%" /></div>';

	$tables = new Table();
	$tables->caption='ข้อมูลการตรวจวัด';
	$tables->rows[]=array('วันที่และเวลาการตรวจวัด',sg_date($rs->waterupdate,'ว ดด ปป H:i').' น.');
	$tables->rows[]='<tr><th colspan="2">ปริมาณน้ำฝน</th></tr>';
	$tables->rows[]=array('ปริมาณน้ำฝน 15 นาที',number_format($rain['15min'],1).' ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน 1 ชั่วโมง',number_format($rain['1hr'],1).' ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน 3 ชั่วโมง',number_format($rain['3hr'],1).' ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน วันนี้',number_format($rain['today'],1).' ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน เมื่อวานนี้',number_format($rain['yesterday'],1).' ม.ม.','');

	$tables->rows[]='<tr><th colspan="2">ระดับน้ำ</th></tr>';
	$tables->rows[]=array('ขณะนี้ ('.sg_date($rs->waterupdate,'ว ดด ปป H:i').')',number_format($rs->waterlevel,2).' '.$rs->levelref);
	$tables->rows[]=array('เฉลี่ย','- ม.รทก.');
	$tables->rows[]=array('สูงสุดเมื่อวานนี้','- ม.รทก. เวลาสูงสุด - น.');
	$tables->rows[]=array('ต่ำสุดเมื่อวานนี้','- ม.รทก. เวลาสูงสุด - น.');
	$tables->rows[]=array('ตลิ่งซ้าย','- ม.รทก.');
	$tables->rows[]=array('ตลิ่งขวา','- ม.รทก.');

	$tables->rows[]='<tr><th colspan="2">ปริมาณน้ำ</th></tr>';
	$tables->rows[]=array('อัตราการไหล','- ลบ.ม./วินาที');

	$tables->rows[]='<tr><th colspan="2">สถานะของอุปกรณ์</th></tr>';
	$tables->rows[]=array('สถานะการสื่อสาร',$statusCommu);
	$tables->rows[]=array('แบตเตอรี่',$rs->batterylevel.' โวลท์');

	$ret .= $tables->build();

	//$ret.=print_o($rs,'$rs');
	//$ret.=print_o($rainAvg,'$rainAvg');

	return $ret;
}
?>