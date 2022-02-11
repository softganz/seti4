<?php
/**
* รายงานคนจนที่มีบ้านของตนเองและชำรุด
*
* @param Object $self
* @return String
*/

function imed_poorhome_report_housebroke($self) {
	$self->theme->title='รายงานคนจนที่มีบ้านของตนเองและชำรุด';
	$self->theme->toolbar=R::Page('imed.poorhome.toolbar',$self);

	$act=post('act');
	$muni=post('muni');
	$checkValue=post('v');

	$isAdmin=i()->admin;
	$isAccess=$isAdmin || user_access('access imed poorhomes');

	$ret.='<nav class="nav -page"><form method="get">';
	$ret.='เงื่อนไข : <select name="muni" class="form-select"><option value="">** ทุกเทศบาล **</option>';
	$dbs=mydb::select('SELECT `municipality`, COUNT(*) `amt` FROM %poor% GROUP BY `municipality` ');
	foreach ($dbs->items as $rs) {
		$ret.='<option value="'.$rs->municipality.'" '.($rs->municipality==$muni?'selected="selected"':'').'>'.$rs->municipality.' ('.$rs->amt.' ครัวเรือน)</option>';
	}
	$ret.='</select> ';
	$ret.='<button class="btn -primary" type="submit"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	$ret.='</form></nav>';

	mydb::where('p.`housingowner`="บ้านของตนเอง" AND p.`housingstatus`="ชำรุด"');
	if (post('muni')) mydb::where('p.`municipality` = :muni',':muni',$muni);
	$stmt='SELECT `poorid`, p.`uid`, `municipality`
				, p.`housingowner`
				, p.`housingstatus`
				, p.`commune`
				, p.`house`
				, p.`village`
				, cosub.`subdistname` subdistname
				, codist.`distname` distname
				, copv.`provname` provname
				FROM %poor% p
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				%WHERE%
				';
	$dbs=mydb::select($stmt);
	$no=0;
	$tables = new Table();
	$tables->thead=array('no'=>'','เทศบาล','ชุมชน','ที่อยู่','สถานะที่อยู่อาศัย','สภาพที่อยู่อาศัย','');
	foreach ($dbs->items as $rs) {
		if (!($isAccess || $uid==$rs->uid)) unset($rs->house,$rs->village);
		$tables->rows[]=array(
											++$no,
											$rs->municipality,
											$rs->commune,
											SG\implode_address($rs,'short'),
											$rs->housingowner,
											$rs->housingstatus,
											'<a href="'.url('imed/poorhome/view/'.$rs->poorid).'" target="_blank"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a>'
											);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

?>