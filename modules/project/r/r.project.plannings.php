<?php
/**
* Model :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @return Object Data Set
*
* @usage R::Model("module.method", $conditions)
*/

$debug = true;

function r_project_plannings($conditions = [], $options = '{}') {
	$conditions = (Object) $conditions;

	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	if ($debug) debugMsg($conditions, '$conditions');
	if ($debug) debugMsg($options, '$options');

	$result = (Object)[];

	// $issueId = $this->issueId;
	// $planTypeSelect = SG\getFirst(post('type'),NULL);
	// $planSelect = SG\getFirst(post('pn'),NULL);
	// $provinceSelect = SG\getFirst(post('pv'),NULL);
	// $ampurSelect = SG\getFirst(post('am'), NULL);
	// $areaSelect = SG\getFirst(post('ar'),NULL);
	// $sectorSelect = SG\getFirst(post('s'),NULL);
	// $yearSelect = SG\getFirst(post('yr'),NULL);

	$orderKey = SG\getFirst($options->order,'date');
	$orderList = [
		'tpid'=>'p.`tpid`',
		'title' => 'CONVERT(`title` USING tis620)',
		'prov' => 'CONVERT(`provname` USING tis620)',
		'date' => '`created` DESC',
		'mod' => '`lastModified` DESC',
		'tran' => '`totalTran` DESC',
		'rate' => '`rating` DESC',
	];
	$order = $orderList[$orderKey];
	if (empty($order)) $order = $orderList['date'];


	$fields = [];
	$joins = [];

	mydb::where('p.`prtype` = "แผนงาน"');
	if ($conditions->userId) mydb::where('t.`uid` = :userId', ':userId', $conditions->userId);
	if ($conditions->childOf) mydb::where('t.`parent` IN ( :parent )', ':parent', 'SET:'.$conditions->childOf);
	if ($conditions->plan) mydb::where('pt.`refid` = :planId', ':planId', $conditions->plan);
	if ($conditions->problem) {
		mydb::where('pb.`refid` = :problemId', ':problemId', $conditions->problem);
		$fields[] = 'pb.`refid` `problemId`, pb.`num1` `problemSize`, pb.`num2` `targetSize`';
		$joins[] = 'LEFT JOIN %project_tr% pb ON pb.`tpid` = p.`tpid` AND pb.`formid` = "info" AND pb.`part` = "problem"';
	}

	if ($conditions->year) mydb::where('p.`pryear` IN ( :year )', ':year', 'SET:'.$conditions->year);


	if ($conditions->planType === 'ampur') {
		mydb::where('t.`orgid` IS NULL AND LENGTH(t.`areacode` = 4)');
	} else {
		mydb::where('t.`orgid` IS NOT NULL');
	}
	if ($conditions->orgId) mydb::where('t.`orgid` = :orgId', ':orgId', $conditions->orgId);
	if ($conditions->childOfOrg) mydb::where('o.`parent` IN ( :orgParent )', ':orgParent', 'SET:'.$conditions->childOfOrg);
	if ($conditions->sector) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$conditions->sector);


	if ($conditions->area) {
		mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$conditions->area);
		$joins[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`';
	}

	if ($conditions->ampur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$conditions->ampur);
	} else if ($conditions->changwat) {
		mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$conditions->changwat);
	}

	mydb::value('$FIELDS$', ($fields ? ', ' : '').implode(_NL.', ', $fields), false);
	mydb::value('$JOINS$', implode(_NL, $joins), false);
	mydb::value('$ORDER$', $order, false);

	$stmt = 'SELECT
		  p.`tpid` `planningId`
		, t.`orgid` `orgId`
		, t.`title`
		, t.`rating`
		, o.`name` `orgName`
		, u.`username`, u.`name` `ownerName`
		, LEFT(t.`areacode`,2) `changwat`
		, cop.`provname` `changwatName`
		, LEFT(t.`areacode`,4) `ampur`
		, cod.`distname` `ampurName`
		$FIELDS$
		, t.`created`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "info" AND `part` IN ("problem", "basic", "guideline", "project")) ) `totalTran`
		, (SELECT GREATEST(IFNULL(MAX(lm.`modified`),0),MAX(lm.`created`)) FROM %project_tr% lm WHERE lm.`tpid` = p.`tpid` AND lm.`formid` = "info") `lastModified`
		FROM %project% p
			LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			LEFT JOIN %users% u ON u.`uid` = t.`uid`
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`,4)
			LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
			$JOINS$
		%WHERE%
		GROUP BY p.`tpid`
		ORDER BY $ORDER$
		';

	$result = mydb::select($stmt)->items;

	if ($options->debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

	return $result;
}
?>