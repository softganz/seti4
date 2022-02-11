<?php
function map_history($self, $id = NULL) {
	$id=trim(SG\getFirst($id,post('id')));
	$ret.='<h3>ประวัติการแก้ไข</h3>';
	$ret.='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
	$dbs=mydb::select('SELECT h.*, u.`name` FROM %map_history% h LEFT JOIN %users% u USING(`uid`) WHERE `mapid`=:mapid ORDER BY `modifydate` DESC',':mapid',$id);
	$fields=array('privacy'=>'ความเป็นส่วนตัว', 'status'=>'สถานะ', 'who'=>'ใคร', 'dowhat'=>'ทำอะไร', 'when'=>'เมื่อไหร่', 'address'=>'ที่อยู่', 'village'=>'หมู่ที่', 'tambon'=>'ตำบล', 'ampur'=>'อำเภอ', 'changwat'=>'จังหวัด', 'detail'=>'รายละเอียดเพิ่มเติม', 'latlng'=>'พิกัด', 'prepare'=>'ก่อนเกิดเหตุ', 'during'=>'ระหว่างเกิดเหตุ', 'after'=>'หลังเกิดเหตุ');
	$ret.='<ul class="map-list">';
	foreach ($dbs->items as $rs) {
		$ret.='<li>@'.$rs->modifydate.' โดย '.$rs->name.'<br />ฟิลด์ : '.$fields[$rs->fld].'<br />จาก : '.$rs->from.'<br />เป็น : '.$rs->to.'</li>';
	}
	$ret.='</ul>';
	return $ret;
}
?>