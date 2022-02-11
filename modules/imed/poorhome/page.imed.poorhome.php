<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome($self) {
	$self->theme->title='แบบสำรวจโครงการแก้ไขปัญหาและพัฒนาคุณภาพชีวิตคนจนในเขตชุมชนเมือง ปี 2559ตามนโยบาย “15 วาระสงขลา”';

	R::Page('imed.poorhome.toolbar',$self);

	$isAdmin=i()->admin;
	$isAccess=$isAdmin || user_access('access imed poorhomes');

	$ret = '<nav class="nav -page">';
	$dbs=mydb::select('SELECT `municipality`, COUNT(*) `amt` FROM %poor% GROUP BY `municipality` HAVING `municipality`!="" ');
	foreach ($dbs->items as $rs) {
		$ret.='<a class="btn" href="'.url('imed/poorhome',array('muni'=>$rs->municipality)).'"><i class="icon -list"></i><span>'.$rs->municipality.'</span></a> ';
	}
	$ret.='</nav>';

	if (post('muni')) mydb::where('p.`municipality` = :muni',':muni',post('muni'));

	$stmt='SELECT
				p.*
				, u.`name` `posterName`
				, COUNT(m.`poorid`) `members`
				, GROUP_CONCAT(IF(m.`reltohouseholder`="หัวหน้าครัวเรือน",CONCAT(psn.`prename`," ",psn.`name`," ",psn.`lname`),"") SEPARATOR "") householderName
				, cosub.`subdistname` subdistname
				, codist.`distname` distname
				, copv.`provname` provname
				, (SELECT COUNT(*) FROM %topic_files% ph WHERE ph.`gallery` IS NOT NULL AND ph.`gallery`=p.`gallery`) `photos`
				FROM %poor% p
					LEFT JOIN %poormember% m USING(`poorid`)
					LEFT JOIN %db_person% psn USING(`psnid`)
					LEFT JOIN %users% u ON u.`uid`=p.`uid`
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				%WHERE%
				GROUP BY `poorid`
				ORDER BY `poorid` ASC';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead=array('no'=>'','เทศบาล','ชุมชน','หัวหน้าครัวเรือน','amt members'=>'สมาชิก','amt photos'=>'ภาพถ่าย','สร้างโดย','amt refid'=>'เลขอ้างอิง','date'=>'วันที่สร้าง','');

	$uid=i()->uid;
	$no=0;
	foreach ($dbs->items as $rs) {
		if (!($isAccess || $uid==$rs->uid)) {
			unset($rs->house,$rs->village);
		}
		$tables->rows[]=array(
											++$no,
											$rs->municipality,
											$rs->commune,
											'<b>'.$rs->householderName.'</b><br />'
											.SG\implode_address($rs,'short'),
											$rs->members,
											$rs->photos?$rs->photos:'-',
											$rs->posterName,
											$rs->poorid,
											sg_date($rs->created,'d-m-ปปปป H:i'),
											'<a href="'.url('imed/poorhome/view/'.$rs->poorid).'"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a>'
											);
	}
	$ret.=$tables->build();

	return $ret;
}
?>