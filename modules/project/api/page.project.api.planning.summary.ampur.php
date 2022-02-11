<?php
/**
* Project :: API for Apmur Planning Summary
* Created 2021-05-19
* Modify  2021-05-22
*
* @param Object $self
* @param Array $param
* @return JSON
*
* @usage project/api/planning/summary/ampur
*/

$debug = true;

class ProjectApiPlanningSummaryAmpur {
	var $parameter;

	function _construct($param = NULL) {
		$this->parameter = $param;
	}

	function build() {
		$filterPlan = SG\getFirst($this->parameter->filterPlan, post('plan'), implode(',',post('for_plan')));
		$filterYear = SG\getFirst($this->parameter->filterYear, post('year'),implode(',',post('for_year')));
		$filterProblem = SG\getFirst($this->parameter->filterProblem, post('problem'), implode(',',post('for_problem')));
		$filterArea = SG\getFirst($this->parameter->filterArea, post('area'),implode(',',post('for_area')));
		$filterChangwat = SG\getFirst($this->parameter->filterChangwat, post('changwat'),implode(',',post('for_changwat')));
		$filterAmpur = SG\getFirst($this->parameter->filterAmpur, post('ampur'),implode(',',post('for_ampur')));
		$filterFund = SG\getFirst($this->parameter->filterFund, post('fund'),implode(',',post('for_fund')));

		$filterSector = SG\getFirst($this->parameter->filterSector, post('sector'),implode(',',post('for_sector')));
		list($filterProblem) = explode(':', $filterProblem);

		$isDebug = user_access('access debugging program') && post('debug');

		header('Access-Control-Allow-Origin: *');
		//$headerResult = http_response_code(200);

		$groupBy = ['problem' => '`problemId`'][$getGroupBy];
		if (!$groupBy) $groupBy = '`year`';

		$result->status = true;
		$result->count = 0;
		$result->debug = $isDebug;
		$result->parameter = (Object) [];

		if ($filterPlan) $result->parameter->plan = $filterPlan;
		if ($filterArea) $result->parameter->area = $filterArea;
		if ($filterChangwat) $result->parameter->changwat = $filterChangwat;
		if ($filterAmpur) $result->parameter->ampur = $filterAmpur;
		if ($filterYear) $result->parameter->year = $filterYear;
		if ($filterFund) $result->parameter->fund = $filterFund;
		if ($filterType) $result->parameter->type = $filterType;

		$result->summaryFields = array(
			'planName' => 'แผนงาน',
			'planAmt' => 'จำนวนแผนงาน',
			// 'targetAvg' => 'ค่าเฉลี่ยเป้าหมาย',
			// 'planAmt' => 'จำนวนแผนงาน',
		);
		if ($filterArea) $result->summaryFields['areaProblem'] = 'เขต '.$filterArea;
		if ($filterChangwat) {
			$result->summaryFields['changwatProblem'] = 'จังหวัด'
				. mydb::select('SELECT * FROM %co_province% WHERE `provid` = :changwat LIMIT 1', ':changwat', $filterChangwat)->provname;
		}
		if ($filterAmpur) {
			$result->summaryFields['ampurProblem'] = 'อำเภอ'
				. mydb::select('SELECT * FROM %co_district% WHERE `distid` = :ampur LIMIT 1', ':ampur', $filterAmpur)->distname;
		}
		if ($filterFund) {
			$result->summaryFields['fundProblem'] = mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgid LIMIT 1', ':orgid', $filterFund)->name;
		}

		$result->summary = array();

		if ($isDebug && !$param) {
			$result->process = array();
			$urlQueryString = post();
			array_shift($urlQueryString);
			$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
			$result->process[] = print_o(post(),'post()');
		}

		$totalList = [];
		$join = [];
		$valuationCondition = $filterValuation ? ' AND v.`part` LIKE :part' : '';


		// Count Ampur Planning
		//mydb::where('p.`prtype` = "แผนงาน" AND t.`orgid` IS NULL AND LENGTH(t.`areacode`) = 4');
		//mydb::where('tg.`taggroup` = "project:planning"');
		if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);
		if ($filterPlan) mydb::where('tr.`refid` IN ( :filterPlan )', ':filterPlan', 'SET:'.$filterPlan);
		if ($filterArea) mydb::where('LEFT(p.`areacode`,2) IN ( SELECT `changwat` FROM %project_fund% f WHERE `areaid` IN ( :filterArea ))', ':filterArea', 'SET:'.$filterArea);
		if ($filterAmpur) {
			mydb::where('LEFT(p.`areacode`,4) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$filterAmpur);
		} else if ($filterChangwat) {
			mydb::where('LEFT(p.`areacode`,2) IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
		}

		$stmt = 'SELECT
			tg.`catid` `planId`, tg.`name` `planName`
			, p.`planAmt`
			FROM %tag% tg
				LEFT JOIN (SELECT
					p.*
					, tr.`refid` `planId`
					, COUNT(p.`tpid`) `planAmt`
					FROM (
						SELECT p.`tpid`, t.`title`, t.`areacode`, p.`pryear`
						FROM %project% p
							LEFT JOIN %topic% t ON t.`tpid` = p.`tpid` AND t.`orgid` IS NULL AND LENGTH(t.`areacode`) = 4
						WHERE p.`prtype` = "แผนงาน" AND t.`orgid` IS NULL AND LENGTH(t.`areacode`) = 4
						) p
						LEFT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid` = "info" AND tr.`part` = "title"
					%WHERE%
					GROUP BY `planId`
				) p ON p.`planId` = tg.`catid` AND tg.`taggroup` = "project:planning"
			-- LEFT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = p.`planId`
			WHERE tg.`taggroup` = "project:planning"
			ORDER BY tg.`process` DESC, tg.`weight`, CONVERT(tg.`name` USING tis620);
			-- PROJECT API PLANNING AMPUR SUMMARY : COUNT AMPUR PLANNING
			-- {sum:"planamt,orgamt"}
			';

		$planDbs = mydb::select($stmt);
		// debugMsg($planDbs->items, '$planDbs');

		if ($isDebug) $result->process[] = mydb()->_query;

		//debugMsg(mydb()->_query);
		//debugMsg($planDbs, '$planDbs');
		//return print_o($result, '$result');

		//$result->count = $dbs->count();

		// Count Project Proposal On Issue
		// mydb::where('tg.`taggroup` = "project:planning"');
		// if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);
		// if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
		// if ($filterAmpur) {
		// 	mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
		// } else if ($filterChangwat) {
		// 	mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
		// }


		// $stmt = 'SELECT
		// 	tg.`catid`
		// 	, tg.`name` `label`
		// 	, tg.`weight`, tr.`tpid`, tr.`refid`
		// 	, COUNT(*) `proposalAmt`
		// 	, COUNT(DISTINCT t.`orgid`) `orgamt`
		// 	FROM %tag% tg
		// 		LEFT JOIN %project_tr% tr
		// 		ON tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid`=tg.`catid`
		// 		LEFT JOIN %topic% t USING(`tpid`)
		// 		LEFT JOIN %project_dev% p USING(`tpid`)
		// 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
		// 		LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
		// 	%WHERE%
		// 	GROUP BY tg.`catid`
		// 	ORDER BY `process` DESC,`catid` ASC;
		// 	-- PROJECT API PLANNING SUMMARY : COUNT PROPOSAL
		// 	-- {key: "catid", sum:"devamt"}
		// ';

		//mydb::where('tg.`taggroup` = "project:planning"');
		mydb::where('plan.`refid` IS NOT NULL');
		if ($filterYear) mydb::where('d.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);
		if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
		if ($filterAmpur) {
			mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
		} else if ($filterChangwat) {
			mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
		}

		$stmt = 'SELECT
			tg.`catid` `planId`, tg.`name` `planName`
			, p.`proposalAmt`
			FROM %tag% tg
				LEFT JOIN (
					SELECT
					d.`tpid`, t.`title`, t.`areacode`, t.`orgid`, plan.`refid` `planId`
					, COUNT(DISTINCT d.`tpid`) `proposalAmt`
					FROM %project_dev% d
						LEFT JOIN %topic% t ON t.`tpid` = d.`tpid`
				 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			 			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
						LEFT JOIN %project_tr% plan ON plan.`tpid` = d.`tpid` AND plan.`formid` = "develop" AND plan.`part` = "supportplan"
						%WHERE%
					GROUP BY `planId`
				) p ON p.`planId` = tg.`catid` AND tg.`taggroup` = "project:planning"
			WHERE tg.`taggroup` = "project:planning"
			ORDER BY tg.`process` DESC, tg.`weight`, CONVERT(tg.`name` USING tis620);
			-- PROJECT API PLANNING SUMMARY : COUNT PROPOSAL
		 	-- {key: "planId", sum:"proposalAmt"}
			';
		$devDbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		// debugMsg($devDbs->items, '$devDbs');
		// return mydb()->_query;
		if ($isDebug) $result->process[] = mydb()->_query;

		// Count Project Follow On Issue
		// mydb::where('p.`prtype` = "โครงการ"');
		// if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);
		// if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
		// if ($filterAmpur) {
		// 	mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
		// } else if ($filterChangwat) {
		// 	mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
		// }

		// $stmt = 'SELECT
		// 	tg.`catid`
		// 	, tg.`name` `label`
		// 	, tg.`weight`, tr.`tpid`, tr.`refid`
		// 	, COUNT(*) `followAmt`
		// 	, SUM(p.`budget`) `followBudget`
		// 	, COUNT(DISTINCT t.`orgid`) `orgamt`
		// 	FROM
		// 	(SELECT tr.`tpid`, tr.`refid`
		// 		FROM %project_tr% tr
		// 		WHERE tr.`formid` = "info" AND tr.`part` = "supportplan"
		// 	) tr
		// 		LEFT JOIN %project% p USING(`tpid`)
		// 		LEFT JOIN %topic% t USING(`tpid`)
		// 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
		// 		LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
		// 		RIGHT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = tr.`refid`
		// 	%WHERE%
		// 	GROUP BY tg.`catid`
		// 	ORDER BY `process` DESC,`catid` ASC
		// 	-- PROJECT API PLANNING SUMMARY : COUNT FOLLOW;
		// 	-- {key: "catid", sum:"followAmt,followBudget"}
		// ';

		// Count Project Follow On Issue
		mydb::where('p.`prtype` = "โครงการ"');
		if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);
		if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
		if ($filterAmpur) {
			mydb::where('LEFT(t.`areacode`,4) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
		} else if ($filterChangwat) {
			mydb::where('LEFT(t.`areacode`,2) IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
		}

		$stmt = 'SELECT
			tg.`catid` `planId`, tg.`name` `planName`
			, p.`followAmt`
			, p.`followBudget`
			FROM %tag% tg
				LEFT JOIN (
					SELECT
					p.`tpid`, t.`title`, t.`areacode`, t.`orgid`, plan.`refid` `planId`
					, COUNT(DISTINCT p.`tpid`) `followAmt`
					, SUM(p.`budget`) `followBudget`
					FROM %project% p
						LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			 			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
						LEFT JOIN %project_tr% plan ON plan.`tpid` = p.`tpid` AND plan.`formid` = "info" AND plan.`part` = "supportplan"
						%WHERE%
					GROUP BY `planId`
				) p ON p.`planId` = tg.`catid` AND tg.`taggroup` = "project:planning"
			WHERE tg.`taggroup` = "project:planning"
			ORDER BY tg.`process` DESC, tg.`weight`, CONVERT(tg.`name` USING tis620);
			-- PROJECT API PLANNING SUMMARY : COUNT PROPOSAL
		 	-- {key: "planId", sum:"followAmt"}
			';
		$followDbs = mydb::select($stmt);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		// debugMsg($devDbs->items, '$devDbs');
		// return mydb()->_query;

		if ($isDebug) $result->process[] = mydb()->_query;


		foreach ($planDbs->items as $rs) {
			$result->summary[] = [
				'planId' => $rs->planId,
				'planName' => $rs->planName,
				'planAmt' => $rs->planAmt,
				'proposalAmt' => $devDbs->items[$rs->planId]->proposalAmt,
				'followAmt' => $followDbs->items[$rs->planId]->followAmt,
				'followBudget' => floatval($followDbs->items[$rs->planId]->followBudget),
			];
		}

		$result->count = count($result->summary);

		return $result;
	}
}
?>