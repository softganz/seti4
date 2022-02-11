<?php

/**
 * iMed report รายชื่อคนพิการเพิ่มใหม่
 *
 */
function imed_report_newdisability($self) {
	$self->theme->title='รายชื่อคนพิการเพิ่มใหม่';
	$prov=post('p');
	$ampur=post('a');
	$tambon=post('t');
	$village=post('v');

	$isAdmin=user_access('administer imeds');
	$zones=imed_model::get_user_zone(i()->uid,'imed');

	if (!post('f')) {
		$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
		$ret.='<h3>รายชื่อคนพิการรายใหม่</h3>';
		$ret.='<div class="form-item">'._NL;
		$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
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
		$ret.='</div>'._NL;
		$ret.='<div class="optionbar"><ul>';
		$ret.='</ul></div>';
		$ret.='</form>';
	}

	$ret.='<div id="report-output">';


	if ($prov) mydb::where('p.`changwat`=:prov',':prov',$prov);
	if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
	if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);
	if ($village) mydb::where('p.`village`=:village',':village',$village);

	if ($isAdmin) {

	} else  if ($zones) {
		mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);

	} else {
		mydb::where('p.`uid`=:uid',':uid',i()->uid);
	}

	$stmt='SELECT `prename`, CONCAT(p.`name`," ",`lname`) fullname,
						p.`created`, u.`name` createby,
						p.`village`, cod.`distname`, cop.`provname`, cos.`subdistname`,
						d.*
					FROM %imed_disabled_defect% d
						LEFT JOIN %db_person% p ON p.`psnid`=d.`pid`
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cos ON cos.`subdistid`=CONCAT(p.`changwat`, p.`ampur`, p.`tambon`)
						LEFT JOIN %users% u ON u.`uid`=p.`uid`
					%WHERE%
					GROUP BY d.`pid`
					ORDER BY p.`created` DESC LIMIT 500';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead=array('no'=>'','date created'=>'วันที่', 'title'=>'ชื่อ - สกุล','จังหวัด','สร้างโดย');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			sg_date($rs->created,'ว ดด ปปปป'),
			'<a href="'.url('imed', ['pid' => $rs->pid]).'" target="_blank">'.$rs->prename.' '.SG\getFirst($rs->fullname,'...').'</a>',
			SG\implode_address($rs,'short'),
			$rs->createby
		);
	}

	$ret .= $tables->build();
	$ret .= '</div>';

	return $ret;
}
?>