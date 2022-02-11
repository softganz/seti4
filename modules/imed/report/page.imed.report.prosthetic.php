<?php
/**
 * iMed report การได้รับกายอุปกรณ์
 *
 */
function imed_report_prosthetic($self) {
	$getStkId = post('stkid');
	$prov = SG\getFirst(post('p'));
	$ampur = post('a');
	$tambon = post('t');
	$village = post('v');
	$showRawDate = false;

	$isAdmin = user_access('administer imeds');
	$zones = imed_model::get_user_zone(i()->uid,'imed');

	if (!post('f')) {
		$ret .= '<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
		$ret .= '<h3>รายงานการได้รับกายอุปกรณ์</h3>';
		$ret .= '<div class="form-item">'._NL;

		$ret .= '<div class="form-item">'._NL;
		$ret .= '<select name="stkid" id="stkid" class="form-select" style="width: 100px;">'._NL.'<option value="">--ทุกกายอุปกรณ์--</option>'._NL;
		foreach (mydb::select('SELECT `stkid`, `name` FROM %imed_stkcode% WHERE `parent` IN ("01")')->items as $rs) {
			$ret .= '<option value="'.$rs->stkid.'"'.($rs->stkid == $getStkId?' selected="selected"':'').'>'.$rs->name.'</option>'._NL;
		}
		$ret .= '</select>'._NL;

		$provdbs = mydb::select('SELECT DISTINCT `provid`, `provname`, COUNT(*) `total`
			FROM %po_stktr% po
				LEFT JOIN %db_person% p ON p.`psnid` = po.`psnid`
				LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
			GROUP BY cop.`provid`
			HAVING `provname` IS NOT NULL
			ORDER BY CONVERT(`provname` USING tis620) ASC');

		$ret .= '<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select" style="width: 100px;">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) {
			$ret .= '<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.' ('.$rs->total.' รายการ)'.'</option>'._NL;
		}
		$ret .= '</select>'._NL;

		// if ($prov) {
		// 	$stmt = 'SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2) = :prov ORDER BY CONVERT(`distname` USING tis620) ASC';
		// 	$ret .= '<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
		// 	foreach (mydb::select($stmt,':prov',$prov)->items as $rs) {
		// 		$ret .= '<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
		// 	}
		// 	$ret .= '</select>'._NL;
		// 	$ret .= '<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
		// 	$ret .= '<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		// }
		$ret .= '<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret .= '</div>'._NL;
		$ret .= '<div class="optionbar"><ul>';
		$ret .= '</ul></div>';
		$ret .= '</form>';
	}

	$ret .= '<div id="report-output">';



	if ($getStkId) mydb::where('tr.`stkid` = :stkid', ':stkid', $getStkId);
	if ($prov) mydb::where('p.`changwat` = :prov',':prov',$prov);
	if ($ampur) mydb::where('p.`ampur` = :ampur',':ampur',$ampur);
	if ($tambon) mydb::where('p.`tambon` = :tambon',':tambon',$tambon);
	if ($village) mydb::where('p.`village` = :village',':village',$village);

	$stmt = 'SELECT
			tr.`stkid`, c.`name` `prosthetic`
		, COUNT(*) `total`
		, COUNT(DISTINCT tr.`psnid`) `persons`
		FROM %po_stktr% tr
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %imed_stkcode% c USING(`stkid`)
			LEFT JOIN %co_category% s ON s.`cat_id` = tr.`status`
			LEFT JOIN %co_province% copv ON copv.`provid` = p.`changwat`
			LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		GROUP BY tr.`stkid`
		ORDER BY `total` DESC;
		-- {reset: false}';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = ['กายอุปกรณ์', 'persons -amt' => 'จำนวนคน', 'total -amt' => 'จำนวนชิ้น'];
	foreach ($dbs->items as $rs) {
		$tables->rows[] = [$rs->prosthetic, $rs->persons, $rs->total];
	}
	$ret .= $tables->build();

	// debugMsg($dbs, '$dbs');

	if ($prov) {
		if ($isAdmin) {

		} else  if ($zones) {
			foreach ($zones as $zone) {
				if (strlen($zone->zone) == 6) {
					$zoneTambon[] = $zone->zone;
				} else if (strlen($zone->zone) == 4) {
					$zoneAmpur[] = $zone->zone;
				} else if (strlen($zone->zone) == 2) {
					$zoneProvince[] = $zone->zone;
				}
			}
			if ($zoneProvince) $zoneCondition[] = 'p.changwat IN ("'.implode('","', $zoneProvince).'")';
			if ($zoneAmpur) $zoneCondition[] = 'CONCAT(p.changwat,p.ampur) IN ("'.implode('","', $zoneAmpur).'")';
			if ($zoneTambon) $zoneCondition[] = 'CONCAT(p.changwat,p.ampur,p.tambon) IN ("'.implode('","', $zoneTambon).'")';
			mydb::where('('.'p.`uid` = :uid'.' OR '.implode(' OR ',$zoneCondition).')',':uid',i()->uid);
		} else {
			mydb::where('p.`uid` = :uid',':uid',i()->uid);
		}

		$stmt = 'SELECT
				tr.*
				, CONCAT(p.`prename`," ", p.`name`, " ", p.`lname`) `fullname`
				, c.`name` `prosthetic`
				, s.`cat_name` `statusName`
				, IFNULL(codist.`distname`,p.`t_ampur`) `ampurName`
				, IFNULL(copv.`provname`,p.`t_changwat`) `changwatName`
			FROM %po_stktr% tr
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% c USING(`stkid`)
				LEFT JOIN %co_category% s ON s.`cat_id` = tr.`status`
				LEFT JOIN %co_province% copv ON copv.`provid` = p.`changwat`
				LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			%WHERE%
			ORDER BY tr.`created` DESC';

		$dbs = mydb::select($stmt);
		// $ret .= '<pre>'.mydb()->_query.'</pre>';

		$tables = new Table();
		$tables->thead = array('no'=>'','rcvdate -date'=>'วันที่ได้รับ','ชื่อ-สกุล','กายอุปกรณ์','สถานะ','อำเภอ','จังหวัด');

		foreach ($dbs->items as $rs) {
			$name=trim($rs->prename.' '.$rs->name.' '.$rs->lname);
			$tables->rows[] = array(
					++$no,
					$rs->created ? sg_date($rs->created,'ว ดด ปปปป') : '-',
					'<a class="sg-action" href="'.url('imed/patient/view/'.$rs->psnid).'" data-rel="box" data-width="480">'.SG\getFirst($rs->fullname,'...').'</a>',
					$rs->prosthetic,
					$rs->statusName,
					$rs->ampurName,
					$rs->changwatName,
				);
		}
		$ret .= $tables->build();
	}


	$ret.='</div>';
	//$ret .= print_o($dbs,'$dbs');
	return $ret;
}
?>