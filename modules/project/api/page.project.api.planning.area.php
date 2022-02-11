<?php
/**
* Project API :: Planning of Area
* Created 2020-06-20
* Modify  2020-06-20
*
* @param Object $self
* @param Array $REQUEST
* @return JSON
*/
function project_api_planning_area($self) {
	$filterPlan = SG\getFirst(post('pn'),implode(',',post('for_plan')));
	$filterArea = SG\getFirst(post('ar'),implode(',',post('for_area')));
	$filterChangwat = SG\getFirst(post('pv'),implode(',',post('for_changwat')));
	$filterYear = SG\getFirst(post('yr'),implode(',',post('for_year')));
	$filterSector = SG\getFirst(post('s'),implode(',',post('for_sector')));

	$orderKey = SG\getFirst(post('o'),'date');

	$isDebug = user_access('access debugging program') && post('debug');

	header('Access-Control-Allow-Origin: *');
	//$headerResult = http_response_code(200);

	$result->status = true;
	$result->count = 0;
	$result->debug = $isDebug;

	if ($isDebug) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
		$result->process[] = print_o(post(),'post()');
	}

	if (!$filterPlan || !$filterYear) $error = '<p class="notify">กรุณาเลือกแผนงาน และ ปี พ.ศ.</p>';

	if ($error) {
		$result->html = $error;
		return $result;
	}


	$ret = '<header id="top" class="header"><h3>แผนกองทุนระดับพื้นที่ ประจำปี '.($filterYear + 543).'</h3><nav><a class="btn -link"><i class="icon"></i></a><a class="btn -link"><i class="icon"></i></a><a class="btn -link" href="#proposal"><i class="icon -material">arrow_downward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">vertical_align_bottom</i></a></nav></header>';

	$ret .= '<header id="situation" class="header"><h3>สถานการณ์</h3><nav><a class="btn -link" href="#page-wrapper"><i class="icon -material">vertical_align_top</i></a><a class="btn -link" href="#page-wrapper"><i class="icon -material">arrow_upward</i></a><a class="btn -link" href="#proposal"><i class="icon -material">arrow_downward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">vertical_align_bottom</i></a></nav></header>';

	$apiParam = new stdClass();
	$apiParam->filterPlan = $filterPlan;
	$apiParam->filterArea = $filterArea;
	$apiParam->filterChangwat = $filterChangwat;
	$apiParam->filterYear = $filterYear;
	$apiParam->filterSector = $filterSector;
	$apiParam->debug = post('debug');

	$situationData = R::Page('project.api.planning.situation', NULL, $apiParam);
	$result->process = array_merge($result->process,$situationData->process);
	//$ret .= print_o($situationData, 'situationData');

	$tables = new Table();
	$tables->thead = array('สถานการณ์', 'problem -amt' => 'ค่าเฉลี่ยขนาดปัญหา', 'target -amt' => 'ค่าเฉลี่ยเป้าหมาย');
	foreach ($situationData->summary as $rs) {
		$tables->rows[] = array(
			$rs->label,
			number_format($rs->problemAvg,2),
			number_format($rs->targetAvg,2),
		);
	}

	$ret .= $tables->build();


	$orderList = array(
		'tpid'=>'`tpid`',
		'title' => 'CONVERT(`title` USING tis620)',
		'prov' => 'CONVERT(`provname` USING tis620)',
		'date' => '`created` DESC',
		'mod' => '`lastModified` DESC',
		'tran' => '`totalTran` DESC',
		'rate' => '`rating` DESC',
	);
	$order = $orderList[$orderKey];
	if (empty($order)) $order=$orderList['date'];

	// Show Project Proposal
	mydb::where('tr.`formid` = "develop" AND tr.`part` = "supportplan" AND tr.`refid` = :refid', ':refid', $filterPlan);
	if ($filterChangwat) mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$filterChangwat);
	if ($filterArea) mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$filterArea);
	mydb::value('$ORDER$', $order);
	if ($filterSector) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('d.`pryear` IN ( :year )', ':year', 'SET:'.$filterYear);

	$stmt = 'SELECT
		tr.`tpid`, t.`orgid`, t.`title`
		, t.`rating`
		, d.`budget`
		, o.`name` `orgName`
		, t.`changwat`, cop.`provname`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "develop") ) `totalTran`
		, t.`created`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project_dev% d USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			LEFT JOIN %co_province% cop ON cop.`provid` = o.`changwat`
		%WHERE%
		ORDER BY $ORDER$;
		-- {sum: "budget"}
		';

	$dbs = mydb::select($stmt);
	if ($isDebug) $result->process[] = mydb()->_query;
	//$ret .= mydb()->_query;


	$ret .= '<header id="proposal" class="header"><h3>โครงการที่พัฒนา '.$dbs->count().' โครงการ</h3><nav><a class="btn -link" href="#page-wrapper"><i class="icon -material">vertical_align_top</i></a><a class="btn -link" href="#situation"><i class="icon -material">arrow_upward</i></a><a class="btn -link" href="#follow"><i class="icon -material">arrow_downward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">vertical_align_bottom</i></a></nav></header>';



	$tables = new Table();
	/*
	$tables->thead = array(
		'<a class="sg-action" href="'.url('project/planning/dev/'.$issueid,array('o'=>'title', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">พัฒนาโครงการ</a>'.($orderKey == 'title' ? ' <i class="icon -sort"></i>' : ''),
		'tran -center -nowrap'.($orderKey == 'tran' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueid,array('o'=>'tran', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box" title="เรียงตามจำนวนรายการข้อมูล"><i class="icon -material">playlist_add_check</i></a>'.($orderKey == 'tran' ? ' <i class="icon -sort"></i>' : ''),
		'rate -center -nowrap' => '<i class="icon -material">star</i>',
		'center -chanhwat' => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueid,array('o'=>'prov', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">จังหวัด</a>'.($orderKey == 'prov' ? ' <i class="icon -sort"></i>' : ''),
		'budget -money' => 'งบประมาณ(บาท)',
		'date' => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueid,array('o'=>'date', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">วันที่เริ่มพัฒนา</a>'.($orderKey == 'date' ? ' <i class="icon -sort"></i>' : '')
	);
	*/

	$tables->thead = array(
		'พัฒนาโครงการ',
		'tran -center -nowrap' => '<i class="icon -material">playlist_add_check</i></a>',
		'rate -center -nowrap' => '<i class="icon -material">star</i>',
		'changwat -center' => 'จังหวัด',
		'budget -money' => 'งบประมาณ(บาท)',
		'date' => 'วันที่เริ่มพัฒนา'
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="' . url('project/develop/' . $rs->tpid) . '" target="_blank">' . SG\getFirst($rs->title, 'ไม่ระบุชื่อ') . '</a>'
			. '<br /><em>'.$rs->orgName.'</em>',
			$rs->totalTran ? '<i class="icon -material -sg-level -level-'.(round($rs->totalTran/10) + 1).'" title="จำนวน '.$rs->totalTran.' รายการ">playlist_add_check</i>' : '',
			'<i class="icon -material rating-star '.($rs->rating != '' ? '-rate-'.round($rs->rating) : '').'">star</i>',
			$rs->provname,
			number_format($rs->budget,2),
			$rs->created
		);
	}
	$tables->tfoot[] = array('รวม '.$dbs->count().' โครงการ', '', '', '', number_format($dbs->sum->budget,2), '');

	$ret .= $tables->build();




	// Project Follow
	mydb::where('p.`prtype` = "โครงการ" AND tr.`formid`="info" AND tr.`part`="supportplan" AND tr.`refid`=:refid', ':refid', $filterPlan);
	if ($filterChangwat) mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$filterChangwat);
	if ($filterArea) mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$filterArea);
	if ($filterSector) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('p.`pryear` IN ( :year )', ':year', 'SET:'.$filterYear);

	mydb::value('$ORDER$', $order);

	$stmt = 'SELECT
		  tr.`tpid`, t.`orgid`, t.`title`
		, t.`rating`
		, p.`budget`
		, o.`name` `orgName`
		, t.`changwat`, cop.`provname`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "info") ) `totalTran`
		, t.`created`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			LEFT JOIN %co_province% cop ON cop.`provid` = o.`changwat`
		%WHERE%
		ORDER BY $ORDER$;
		-- {sum: "budget"}
		';

	$dbs = mydb::select($stmt);
	if ($isDebug) $result->process[] = mydb()->_query;


	$ret .= '<header id="follow" class="header"><h3>โครงการที่ติดตามประเมินผล '.$dbs->count().' โครงการ</h3><nav><a class="btn -link" href="#page-wrapper"><i class="icon -material">vertical_align_top</i></a><a class="btn -link" href="#proposal"><i class="icon -material">arrow_upward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">arrow_downward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">vertical_align_bottom</i></a></nav></header>';


	$tables = new Table();
	/*
	$tables->thead = array(
		'<a class="sg-action" href="'.url('project/planning/follow/'.$issueid,array('o'=>'title', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">ติดตามโครงการ</a>'.($orderKey == 'title' ? ' <i class="icon -sort"></i>' : ''),
		'tran -center -nowrap'.($orderKey == 'tran' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/follow/'.$issueid,array('o'=>'tran', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box" title="เรียงตามจำนวนรายการข้อมูล"><i class="icon -material">playlist_add_check</i></a>'.($orderKey == 'tran' ? ' <i class="icon -sort"></i>' : ''),
		'rate -center -nowrap' => '<i class="icon -material">star</i>',
		'center -chanhwat' => '<a class="sg-action" href="'.url('project/planning/follow/'.$issueid,array('o'=>'prov', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">จังหวัด</a>'.($orderKey == 'prov' ? ' <i class="icon -sort"></i>' : ''),
		'budget -money' => 'งบประมาณ(บาท)',
		'date' => '<a class="sg-action" href="'.url('project/planning/follow/'.$issueid,array('o'=>'date', 'ar' => $areaSelect, 'pv' => $provinceSelect, 'am' => $ampurSelect, 'yr' => $yearSelect)).'" data-rel="box">วันที่เริ่มติดตาม</a>'.($orderKey == 'date' ? ' <i class="icon -sort"></i>' : '')
	);
	*/

	$tables->thead = array(
		'>ติดตามโครงการ',
		'tran -center -nowrap' => '<i class="icon -material">playlist_add_check</i>',
		'rate -center -nowrap' => '<i class="icon -material">star</i>',
		'changwat -center' => 'จังหวัด',
		'budget -money' => 'งบประมาณ(บาท)',
		'date' => 'วันที่เริ่มติดตาม'
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="' . url('paper/' . $rs->tpid) . '" target="_blank">' . $rs->title . '</a>'
			. '<br /><em>'.$rs->orgName.'</em>',
			$rs->totalTran ? '<i class="icon -material -sg-level -level-'.(round($rs->totalTran/10) + 1).'" title="จำนวน '.$rs->totalTran.' รายการ">playlist_add_check</i>' : '',
			'<i class="icon -material rating-star '.($rs->rating != '' ? '-rate-'.round($rs->rating) : '').'">star</i>',
			$rs->provname,
			number_format($rs->budget,2),
			$rs->created
		);
	}
	$tables->tfoot[] = array('รวม '.$dbs->count().' โครงการ', '', '', '', number_format($dbs->sum->budget,2), '');

	$ret .= $tables->build();

	$ret .= '<header id="bottom" class="header"><h3></h3><nav><a class="btn -link" href="#page-wrapper"><i class="icon -material">vertical_align_top</i></a><a class="btn -link" href="#follow"><i class="icon -material">arrow_upward</i></a><a class="btn -link" href="#bottom"><i class="icon -material">arrow_downward</i></a><a class="btn -link -disalbed"><i class="icon"></i></a></nav></header>';

	$result->html = $ret;

	//debugMsg($result,'$result');
	//return sg_json_encode($result);
	return $result;
}
?>