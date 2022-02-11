<?php
function org_seedfund_report_need($self) {
	$self->theme->title='รายงานความต้องการเมล็ดพันธุ์';
	$uigencyList=array(1=>'ไม่เร่งด่วน',2=>'ปานกลาง',3=>'เร่งด่วนมาก');

	R::Page('org.seedfund.toolbar',$self);

	$stmt='SELECT * FROM %org_seedfundneed% ORDER BY `sfnid` DESC';
	$dbs=mydb::select($stmt);

	$isAdmin=user_access('administrator orgs');

	$tables = new Table();
	$tables->thead=array('date -send'=>'วันที่แจ้ง','date -use'=>'วันที่ต้องการ','ผู้ต้องการ','สถานที่/พื้นที่','ความต้องการ','center'=>'ความเร่งด่วน','สถานการณ์โดยสังเขป','ช่องทางการติดต่อ','');
	foreach ($dbs->items as $rs) {
		$itemMenu='<nav class="iconset">';
		$isEdit=user_access('administrator orgs','edit own org content',$rs->uid);
		if ($isEdit) {
			$itemMenu.='<a href="'.url('org/seedfund/need/edit/'.$rs->sfnid).'"><i class="icon -edit"></i></a>';
			$itemMenu.='<a class="sg-action" href="'.url('org/seedfund/need/delete/'.$rs->sfnid).'" data-rel="none" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -delete"></i></a>';
		}
		$ret.='</nav>';
		$tables->rows[]=array(
											sg_date($rs->daterequest,'ว ดด ปปปป'),
											sg_date($rs->dateuse,'ว ดด ปปปป'),
											$rs->who,
											$rs->address,
											nl2br($rs->need),
											$uigencyList[$rs->urgency],
											nl2br($rs->situation),
											nl2br($rs->attention),
											$itemMenu
											);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>