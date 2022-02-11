<?php
/**
* Model :: Description
* Created 2021-09-10
* Modify 	2021-09-10
*
* @param Array $args
* @return Object
*
* @usage new FundModel([])
* @usage FundModel::function($conditions, $options)
*/

$debug = true;

class FundModel {
	function __construct($args = []) {
	}

	public static function Population($args = ['changwat' => NULL, 'year' => NULL]) {
		$result = (Object) ['count' => 0];

		if ($args['year']) mydb::where(NULL,':year',$args['year']);
		if ($args['area']) mydb::where('`areaid`=:areaid',':areaid',$args['area']);
		if ($args['changwat']) mydb::where('`changwat`=:prov',':prov',$args['changwat']);
		if ($args['fundid']) mydb::where('`shortname`=:fundid',':fundid',$args['fundid']);

		$stmt = 'SELECT
			  b.*
				, lgl.`glcode` `localRcvGlCode`
				, ABS(lgl.`amount`) `localRcv`
				, ABS(SUM(lgl.`amount`)) `localRcv`
				, (SELECT COUNT(*) FROM %topic% t RIGHT JOIN %project% tp USING(`tpid`) WHERE t.`orgid`=b.`orgid` AND tp.`pryear`=:year AND tp.`prtype`="โครงการ") `totalProject`
			FROM
			(
				SELECT
				  a.*
				, gl.`glcode` `nhsoGlCode`
				, ABS(SUM(gl.`amount`)) `nhsoRcv`
				FROM
					(
						SELECT f.*
							, o.`name`
							, o.`shortname`
							, po.`num2` `yearPopulation`
							, po.`num4` `budgetlocal`
						FROM %project_fund% f
							LEFT JOIN %db_org% o ON o.`orgid`=f.`orgid`
							LEFT JOIN %project_tr% po ON po.`formid`="population" AND po.`part`=f.`fundid` AND po.`refid` = :year
						GROUP BY `orgid`
					) a
					LEFT JOIN %project_gl% gl
						ON gl.`orgid`=a.`orgid`
						AND gl.`glcode`="40100"
						AND YEAR(gl.`refdate`)+IF(MONTH(gl.`refdate`)>=10,1,0)=:year
					GROUP BY `fundid`
				) b
				LEFT JOIN %project_gl% lgl
					ON lgl.`orgid`=b.`orgid`
					AND lgl.`glcode`="40200"
					AND YEAR(lgl.`refdate`)+IF(MONTH(lgl.`refdate`)>=10,1,0)=:year
			%WHERE%
			GROUP BY `fundid`
			ORDER BY `changwat` ASC, `ampur` ASC, CONVERT(`fundname` USING tis620) ASC;
			-- {sum:"population,openbalance,totalProject,nhsoRcv,localRcv"}';

		$result->items = mydb::select($stmt)->items;
		// debugMsg('<pre>'.mydb()->_query.'</pre>');

		return $result;
	}

	public static function getMoneyPlan($orgId, $budgetYear) {
		$result = (Object) [
			'orgId' => NULL,
			'budgetYear' => NULL,
			'info' => NULL,
		];

		$result->info = mydb::select(
			'SELECT
				m.*
			,	m.`openBalance` + m.`incomeNhso` + m.`incomeLocal` + m.`incomeOther` `incomeTotal`
			, m.`budget10_1` + m.`budget10_2` + m.`budget10_3` + m.`budget10_4` + m.`budget10_5` `budgetTotal`
			FROM %project_fundmoneyplan% m
			WHERE m.`orgId` = :orgId AND m.`budgetYear` = :budgetYear LIMIT 1',
			[
				':orgId' => $orgId,
				':budgetYear' => $budgetYear,
			]
		);

		if (!$result->info->orgId) return NULL;

		$result->info = mydb::clearprop($result->info);
		$result->orgId = $result->info->orgId;
		$result->budgetYear = $result->info->budgetYear;

		$result->supportType = mydb::select(
			'SELECT
				do.`trid` `projectDoId`
				, p.`tpid` `planningId`
				, `refid` `proposalId`
				, IFNULL(do.`refcode`,"n/a") `supportType`
				, do.`detail1` `projectTitle`
				, do.`detail2` `orgNameDo`
				, do.`num1` `budget`
				, support.`name` `supportTypeName`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %project_tr% do ON do.`tpid` = p.`tpid` AND do.`formid` = "info" AND do.`part` = "project"
				LEFT JOIN %tag% support ON support.`taggroup` = "project:supporttype" AND support.`catid` = do.`refcode`
			WHERE `prtype` = "แผนงาน" AND `pryear` = :budgetYear AND t.`orgid` = :orgId
			HAVING `projectDoId` IS NOT NULL
			ORDER BY `supportType` ASC, CONVERT(`projectTitle` USING tis620) ASC;
			-- {group: "supportType", key: "projectDoId"}',
			[
				':budgetYear' => $result->info->budgetYear,
				':orgId' => $orgId,
			]
		)->items;
		return $result;
	}

	public static function moneyPlans($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, items: 50, order: "m.`budgetYear`", sort: "ASC"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['count' => 0, 'items' => []];

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['orgId' => $conditions];
		}

		if ($debug) {debugMsg($conditions, '$conditions'); debugMsg($options, '$options');}

		if ($conditions->budgetYear) mydb::where('m.`budgetYear` = :budgetYear', ':budgetYear', $conditions->budgetYear);

		if ($conditions->orgId) mydb::where('m.`orgId` = :orgId', ':orgId', $conditions->orgId);
		else if ($conditions->changwat) mydb::where('LEFT(o.`areacode`, 2) = :changwat', ':changwat', $conditions->changwat);
		else if ($conditions->zone) mydb::where('f.`areaId` = :zone', ':zone', $conditions->zone);


		$result->items = mydb::select(
			'SELECT
				m.*
				, o.`shortName` `fundId`
				FROM %project_fundmoneyplan% m
					LEFT JOIN %db_org% o ON o.`orgId` = m.`orgId`
					LEFT JOIN %project_fund% f ON f.`orgId` = m.`orgId`
				%WHERE%
				ORDER BY `budgetYear` DESC'
		)->items;

		if ($debug) debugMsg(mydb()->_query);

		$result->count = count($result->items);
		return $result;
	}
}
?>