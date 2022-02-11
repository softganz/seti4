<?php
/**
* iMed Home Visit Report
* Created 2019-10-01
* Modify  2019-10-08
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_report_homevisit($self) {
	$getYear = SG\getFirst(post('y'),date('Y'));
	$getByMonth = post('bymonth');

	$isAdmin=user_access('administer imeds');

	if ($areaCode = post('area')) {
		$ret  .= '<header class="header">'._HEADER_BACK.'<h3>รายละเอียดการเยี่ยมบ้าน</h3></header>';
		$zones = imed_model::get_user_zone(i()->uid,'imed');

		$prov = substr($areaCode,0,2);
		$ampur = substr($areaCode,2,2);
		$tambon = substr($areaCode,4,2);
		if ($prov) mydb::where('p.`changwat` = :changwat', ':changwat', $prov);
		if ($ampur) mydb::where('p.`ampur` = :ampur', ':ampur', $ampur);
		if ($tambon) mydb::where('p.`tambon` = :tambon', ':tambon', $tambon);

		list($year,$month) = explode('-', post('period'));
		if ($year) mydb::where('FROM_UNIXTIME(s.`timedata`, "%Y") = :year', ':year', $year);
		if ($month) mydb::where('FROM_UNIXTIME(s.`timedata`, "%m") = :month', ':month', $month);

		if ($isAdmin) {

		} else  if ($zones) {
			mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
		} else {
			mydb::where('p.`uid`=:uid',':uid',i()->uid);
		}

		$stmt='SELECT
			  p.`psnid`, s.*
			, u.`username`, u.`name`, CONCAT(p.`prename`, " ", p.`name`," ",p.`lname`) `fullname`
			, cosd.`subdistname`, codist.`distname`, copv.`provname`
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
				LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
				LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			%WHERE%
			ORDER BY seq DESC';

		$dbs = mydb::select($stmt);
		//$ret .= mydb()->_query;
		//$ret.=print_o($dbs,'$dbs');

		$tables = new Table();
		$tables->thead=array('no'=>'','ผู้บันทึก','รายละเอียดการเยี่ยมบ้าน','date'=>'วันที่บันทึก');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				$isAdmin ? '<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box">'.$rs->name.'</a>' : $rs->name,
				(callFromApp() ? '<a class="sg-action" href="'.url('imed/app/'.$rs->psnid).'" data-webview="'.htmlspecialchars($rs->fullname).'">' : '<a class="sg-action" href="'.url('imed/patient/view/'.$rs->psnid).'" data-rel="box" target="_blank">')
				. '<strong>'.SG\getFirst($rs->fullname,'...').'</strong></a>'
				. '<br />'
				. SG\implode_address($rs).'<p><img src="'.imed_model::patient_photo($rs->psnid).'" class="patient-photo left" width="48" />'.$rs->rx.'</p>',
				sg_date($rs->created,'d-m-ปปปป H:i')
			);
		}

		$ret .= $dbs->_empty ? message('notify','ไม่มีรายการในพื้นที่รับผิดชอบ') : $tables->build();

		return $ret;
	}



	$self->theme->title='รายงานการเยี่ยมบ้าน';




	// Show form and summary table
	if (!post('f')) {
		$stmt = 'SELECT FROM_UNIXTIME(s.`created`, "%Y") year, COUNT(*) amt
			FROM %imed_service% s
			GROUP BY FROM_UNIXTIME(s.`created`, "%Y")
			ORDER BY `year` DESC';
		$dbs = mydb::select($stmt,$where['value']);

		$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
		$ret .= '<h3>รายงานการเยี่ยมบ้าน</h3>';
		$ret.='<div class="form-item">'._NL;
		$ret.='<select class="form-select" name="y">';
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.$rs->year.'">พ.ศ.'.($rs->year+543).'</option>';
		}
		$ret.='</select>';

		//$stmt = 'SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC';
		$stmt = 'SELECT cop.`provid`, cop.`provname` FROM (SELECT `pid` `psnid` FROM %imed_service% GROUP BY `psnid`) s LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` GROUP BY `provid` HAVING `provid` IS NOT NULL';
		$provdbs = mydb::select($stmt);
		//$ret .= print_o($provdbs,'$provdbs');

		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" class="form-select sg-changwat" data-change="submit">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" class="form-select sg-ampur -hidden" data-change="submit">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
		$ret.='</select>'._NL;
		//$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select sg-tambon">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;

		$ret .= '<label><input type="checkbox" name="bymonth" value="1" > รายเดือน</label>';

		//$ret .= '<label><input type="checkbox" name="people" value="1" > จำนวนคน</label>';
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>';
		$ret.='</form>';
	}



	// Start show report
	$ret.='<div id="report-output">';

	//$ret .= print_o(post(),'post()');
	$getChangwat = post('p');
	$getAmpur = post('a');
	$getTambon = post('t');

	mydb::value('$GROUPBY$', '`changwat`, `period`', false);
	mydb::value('$AREA$', 'copv.`provname`');
	mydb::value('$AREACODE$', 'p.`changwat`');

	mydb::where('FROM_UNIXTIME(s.`timedata`, "%Y") >= 2012');

	if ($getByMonth) mydb::where('FROM_UNIXTIME(s.`timedata`, "%Y") = :year', ':year',$getYear);
	if ($getChangwat) {
		mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
		mydb::value('$GROUPBY$', '`changwat`, `ampur`, `period`', false);
		mydb::value('$AREA$', 'CONCAT("อ.",codist.`distname`," จ.", copv.`provname`)', false);
		mydb::value('$AREACODE$', 'CONCAT(p.`changwat`,p.`ampur`)');
	}
	if ($getAmpur) {
		mydb::where('p.`ampur` = :ampur', ':ampur', $getAmpur);
		mydb::value('$GROUPBY$', '`changwat`, `ampur`, `tambon`, `period`', false);
		mydb::value('$AREA$', 'CONCAT("ต.",cosd.`subdistname`, " อ.",codist.`distname`," จ.", copv.`provname`)', false);
		mydb::value('$AREACODE$', 'CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)');
	}
	if ($getTambon) {
		mydb::where('p.`tambon` = :tambon', ':tambon', $getTambon);
		mydb::value('$GROUPBY$', '`changwat`, `ampur`, `tambon`, `period`', false);
	}

	/*
	$stmt = 'SELECT FROM_UNIXTIME(s.`created`, "%m") month,
			p.`changwat`, p.`ampur`, p.`tambon`,
			cosd.`subdistname`, codist.`distname`, copv.`provname`,
			COUNT(*) amt
		FROM %imed_service% s
			LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
			LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		GROUP BY `changwat`, `ampur`, `tambon`, FROM_UNIXTIME(s.`created`, "%m")
		ORDER BY `provname` ASC, `distname` ASC, `subdistname` ASC';
	*/

	if ($getByMonth) {
		mydb::value('$PERIOD$', 'FROM_UNIXTIME(s.`timedata`, "%m")', false);
	} else {
		mydb::value('$PERIOD$', 'FROM_UNIXTIME(s.`timedata`, "%Y")', false);
	}

	$stmt = 'SELECT
		*
		, COUNT(`amt`) `totalPerson`
		, SUM(`amt`) `totalVisit`
		FROM 
		(
		SELECT
			  $PERIOD$ `period`
			, $AREA$ `area`
			, $AREACODE$ `areacode`
			, `psnid`
			, p.`changwat`
			, p.`ampur`
			, p.`tambon`
			, cosd.`subdistname`
			, codist.`distname`
			, copv.`provname`
			, COUNT(*) `amt`
			, s.`timedata`
		FROM %imed_service% s
			LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		GROUP BY `period`, p.`psnid`
		HAVING `provname` IS NOT NULL
		) a
		GROUP BY `area`, `period`
		ORDER BY CONVERT(`area` USING tis620) ASC
		';

	$dbs=mydb::select($stmt);

	//$ret .= mydb()->_query;
	//$ret .= print_o(post(),'post()');
	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('homevisit');
	$tables->caption='รายงานการเยี่ยมบ้าน'.($getByMonth ? ' ประจำปี '.($getYear+543) : '');
	$tables->thead[0]='พื้นที่';
	$tables->tfoot[1][0]='รวม';
	if ($getByMonth) {
		for ($i=1; $i<=12; $i++) {
			$tables->thead[sprintf('%02d',$i)]=sprintf('%02d',$i);
			$tables->tfoot[1][sprintf('%02d',$i)]='-';
		}
	} else {
		foreach ($dbs->items as $rs) {
			$tables->thead[$rs->period] = $rs->period+543;
			//$tables->tfoot[1][intval($rs->period)] = '-';
		}
		asort($tables->thead);
		foreach ($tables->thead as $key => $value) {
			if ($key != 0) $tables->tfoot[1][$key] = '-';
		}
	}
	foreach ($dbs->items as $rs) {
		$idx = $rs->area;
		if (!$tables->rows[$idx]) {
			$tables->rows[$idx][0] = $rs->area;
			if ($getByMonth) {
				for ($i=1; $i<=12; $i++) $tables->rows[$idx][sprintf('%02d',$i)]='-';
			} else {
				foreach ($tables->thead AS $key=>$item) if ($key != 0) $tables->rows[$idx][$key]='-';
			}
		}
		$tables->rows[$idx][$rs->period] = '<a class="sg-action" href="'.url('imed/report/homevisit',array('period'=>($getByMonth ? $getYear.'-' : '').$rs->period,'area'=>$rs->areacode)).'" data-rel="box" data-width="640">'.$rs->totalPerson.'/'.$rs->totalVisit.'</a>';
		list($totalPerson, $totalVisit) = explode('/', $tables->tfoot[1][$rs->period]);
		$totalVisit += $rs->totalVisit;
		$totalPerson += $rs->totalPerson;
		$tables->tfoot[1][$rs->period] = $totalPerson.'/'.$totalVisit;
	}

	$ret .= $tables->build();
	$ret.='<p>หมายเหตุ<br />* จำนวนคนเยี่ยมบ้าน/จำนวนครั้งเยี่ยมบ้าน<br />** คลิกบนตัวเลขเพื่อดูรายละเอียดการเยี่ยมบ้าน</p>';

	$ret.='</div><!-- report-output -->';

	$ret.='<style>
	.homevisit>tbody>tr>td:nth-child(n+2) {text-align:center;}
	.homevisit>tfoot>tr>td:nth-child(n+2) {text-align:center;}
	</style>';

	//$ret .= print_o($tables, '$tables');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>