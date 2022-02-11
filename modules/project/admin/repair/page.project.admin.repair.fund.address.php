<?php
/**
* Repair LocalFund Address That Missing Tambon Code
* Created 2020-07-20
* Modify  2020-07-20
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_repair_fund_address($self) {
	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>ซ่อมแซมรหัสพื้นที่กองทุน</h3></header>';

	$stmt = 'SELECT
		COUNT(IF(o.`tambon` IS NULL OR o.`tambon` = "",1,NULL)) `totalTambon`
		,COUNT(IF(o.`ampur` IS NULL OR o.`ampur` = "",1,NULL)) `totalAmpur`
		,COUNT(IF(o.`changwat` IS NULL OR o.`changwat` = "",1,NULL)) `totalChangwat`
		FROM %project_fund% f
			LEFT JOIN %db_org% o USING(`orgid`)
		';

	$dbs = mydb::select($stmt);
	$ret .= mydb::printtable($dbs,'{class: "-center"}');
	$ret .= 'totalTambon = 6862 totalAmpur = 142 totalChangwat = 1';

	$stmt = 'SELECT
		o.`orgid`, o.`shortname`, o.`name`
		, o.`tambon`, o.`ampur`, o.`changwat`
		, o.`areacode`
		, f.`areaid`
		, cop.`provname` `changwatName`
		, cod.`distname` `ampurName`
		FROM %project_fund% f
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(o.`areacode`,4)
		WHERE
			(o.`tambon` IS NULL OR o.`tambon` = "")
			OR (o.`ampur` IS NULL OR o.`ampur` = "")
			OR (o.`changwat` IS NULL OR o.`changwat` = "")
		ORDER BY `areaid` ASC,`changwat` ASC, `ampur` ASC, `tambon` ASC
		;';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array('id','','name -left' => 'name','areaid','areacode','changwat','ampur');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->orgid,
			$rs->shortname,
			'<a class="sg-action" href="'.url('project/fund/'.$rs->orgid.'/info.address').'" data-rel="box" data-width="480">'.$rs->name.'</a><br />'
			. 'อ.'.$rs->ampurName.' จ.'.$rs->changwatName,
			$rs->areaid,
			$rs->areacode,
			$rs->changwat,
			$rs->ampur,
		);
	}
	$ret .= $tables->build();

	return $ret;
}
?>