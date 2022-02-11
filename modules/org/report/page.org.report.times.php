<?php
function org_report_times($self) {
	$times=SG\getFirst(post('times'),10);
	$prov=post('p');
	$ampur=post('a');

	$self->theme->title='รายชื่อผู้เข้าร่วมกิจกรรมนับจำนวนครั้ง';

	$isAdmin=user_access('administrator orgs');
	if (!$isAdmin) return message('error','access denied');

	$ret.='<form method="get" action="'.url(q()).'" class="report-form x-sg-form" x-data-rel="#report-output"><input type="hidden" name="f" value="n" />';
	$ret.='<h3>รายชื่อผู้เข้าร่วมกิจกรรมนับจำนวนครั้ง</h3>';
	$ret.='<div class="form-item">'._NL;
	$ret.='<label>จำนวนครั้งเข้าร่วม</label> <input class="fotm-text" type="text" name="times" value="'.$times.'" size="3" />';
	$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %db_person% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
	$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
	foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
	$ret.='</select>'._NL;
	if ($prov) {
		$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
		$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
		foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
		$ret.='</select>'._NL;
		$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
		$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
	}
	$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
	$ret.='<button class="btn" name="export" value="export" type="submit"><i class="icon -download"></i><span>Excel</span></button>'._NL;
	$ret.='</div>'._NL;
	$ret.='<div class="optionbar"><ul>';
	$ret.='</ul></div>';
	$ret.='</form>';

	if (empty($prov)) return $ret;


	mydb::where('p.`changwat`=:prov',':prov',$prov, ':times',intval($times));
	if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
	$stmt='SELECT
					p.`prename`
					, p.`name`
					, p.`lname`
					, COUNT(*) `joinTimes`
					, p.`house`, p.`village`
					,cosub.`subdistname` `subdistname`
					,codist.`distname` `distname`
					,copv.`provname` `provname`
					,p.`zip`,p.`phone`,p.`email`
					FROM %org_dos% mj
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
					%WHERE%
					GROUP BY mj.`psnid`
					HAVING `joinTimes`>=:times
					ORDER BY
						CONVERT(`distname` USING tis620) ASC
						, CONVERT(`subdistname` USING tis620) ASC
						, CONVERT(p.`name` USING tis620) ASC
						, CONVERT(p.`lname` USING tis620) ASC
					';
	$dbs=mydb::select($stmt);


	$ret.='<div id="report-output">';

	//$ret.=mydb()->_query;

	$no=0;
	$tables = new Table();
	$tables->thead=array('no'=>'','prename','name','lname','amt'=>'joinTimes','house', 'village','subdistname','distname','provname','zip','phone text'=>'phone','email');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											$rs->prename,
											$rs->name,
											$rs->lname,
											$rs->joinTimes,
											$rs->house,
											$rs->village,
											$rs->subdistname,
											$rs->distname,
											$rs->provname,
											$rs->zip,
											$rs->phone,
											$rs->email
											);
	}

	if (post('export')) {
		die(R::Model('excel.export',$tables,NULL,'{debug:false}'));
		return $ret;
	}

	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div>';
	return $ret;
}
?>