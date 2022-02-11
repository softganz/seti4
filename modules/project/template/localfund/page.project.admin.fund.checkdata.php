<?php
/**
* Check fund data error
* Created 2019-10-03
* Modify  2019-10-03
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_fund_checkdata($self) {
	R::View('project.toolbar',$self,'รายงาน','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');

	$ret = '<header class="header"><h3>เลขที่อ้างอิงใบเบิกเงินซ้ำ</h3></header>';
	$stmt = 'SELECT
		`refcode`
		, GROUP_CONCAT( CAST(p.`tpid` AS CHAR )) `tpid`
		, GROUP_CONCAT( CAST(t.`orgid` AS CHAR )) `orgid`
		, COUNT( * )  `totals`
		, GROUP_CONCAT( CAST( FROM_UNIXTIME( p.`created` ) AS CHAR ) )  `created` 
		FROM  %project_paiddoc% p 
			LEFT JOIN %topic% t USING(`tpid`)
		GROUP BY  p.`refcode` 
		HAVING  `totals` > 1';

	$dbs = mydb::select($stmt);

	$tables = new Table();

	$tables->thead = array('เลขอ้างอิง','โครงการ','องค์กร', 'dup -amt' => 'จำนวนซ้ำ','วันที่สร้าง');
	foreach ($dbs->items as $rs) {
		$url = '';
		foreach (explode(',', $rs->tpid) as $tpid) {
			$url .= '<a href="'.url('project/'.$tpid.'/info.paiddoc').'" target="_blank">'.$tpid.'</a> , ';
		}

		$url = trim($url, ' , ');

		$tables->rows[] = array(
			$rs->refcode,
			$url,
			$rs->orgid,
			$rs->totals,
			$rs->created
		);
	}

	$ret .= $tables->build();




	$ret .= '<h3>เลขที่อ้างอิงใบรับเงินคืนซ้ำ</h3>';

	$stmt = 'SELECT
		r.`detail2` `refcode`
		, GROUP_CONCAT(r.`tpid`) `tpid`
		, GROUP_CONCAT(t.`orgid`) `orgid`
		, COUNT(*) `totals`
		, GROUP_CONCAT( FROM_UNIXTIME( r.`created` ) )  `created` 
		FROM %project_tr% r
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE r.`formid` = "info" AND r.`part` = "moneyback"
		GROUP BY `detail2`
		HAVING `totals` > 1';

	$dbs = mydb::select($stmt);

	$tables = new Table();

	$tables->thead = array('เลขอ้างอิง','โครงการ','องค์กร', 'dup -amt' => 'จำนวนซ้ำ','วันที่สร้าง');
	foreach ($dbs->items as $rs) {
		$url = '';
		foreach (explode(',', $rs->tpid) as $tpid) {
			$url .= '<a href="'.url('project/'.$tpid.'/info.paiddoc').'" target="_blank">'.$tpid.'</a> , ';
		}

		$url = trim($url, ' , ');

		$tables->rows[] = array(
			$rs->refcode,
			$url,
			$rs->orgid,
			$rs->totals,
			$rs->created
		);
	}

	$ret .= $tables->build();


	return $ret;
}
?>