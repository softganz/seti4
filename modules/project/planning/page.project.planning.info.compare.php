<?php
/**
* Project Planning View Detail
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $tranId
* @return String
*/

class ProjectPlanningInfoCompare extends Page {
	var $planInfo;

	function __construct($planInfo) {
		$this->planInfo = $planInfo;
	}

	function build() {
		if (!($tpid = $this->planInfo->tpid)) return message('error', 'PROCESS ERROR');

		$tagname = 'info';

		$ret = '';

		R::View('project.toolbar',$self,$this->planInfo->title, 'planning', $this->planInfo,'{showPrint:true}');

		//$ret .= '$action ='.$action.' $tranId = '.$tranId;



		$ret .= '<header class="header">'._HEADER_BACK.'<h3>สถานการณ์ปัญหาของแผนทุกปี</h3></header>';

		$stmt = 'SELECT a.*
			, o.`trid`
			, o.`refid`
			, o.`detail1` `problem`
			, o.`num1` `problemsize`
			, o.`num2` `targetsize`
			, tg.`taggroup`
			, tg.`weight`
			, tg.`catid`, tg.`description`
			FROM
			(
			SELECT p.`tpid`, p.`prtype`, p.`pryear`, t.`title`
			, pt.`refid` `planGroup`
			FROM %project% p 
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
			WHERE p.`prtype` = "แผนงาน" AND t.`orgid` = :orgid
				AND pt.`refid` = :plangroup
			ORDER BY `pryear` ASC
			) a
				LEFT JOIN %project_tr% o ON o.`tpid` = a.`tpid` AND o.`formid` = "info" AND o.`part` = "problem"
				LEFT JOIN %tag% tg ON tg.`taggroup` = :taggroup AND tg.`catid` = o.`refid`
			WHERE `refid` IS NOT NULL
			ORDER BY `weight`, `refid`
			';

		$dbs = mydb::select($stmt,
			':orgid', $this->planInfo->info->orgid,
			':plangroup', $this->planInfo->info->planGroup,
			':taggroup', 'project:problem:'.$this->planInfo->info->planGroup
		);

		foreach ($dbs->items as $rs) $yearList[$rs->pryear] = $rs->pryear;
		asort($yearList);

		$emptyRow = array();
		$emptyRow['no'] = '';

		$tables = new Table();
		$tables->addClass('-compare');
		$thead = '<tr><th rowspan="2"></th><th rowspan="2">สถานการณ์ปัญหา</th>';
		$head2 = '';
		foreach ($yearList as $year) {
			$thead .= '<th colspan="2">ปี '.($year+543).'</th>';
			$head2 .= '<th>ขนาด</th><th>เป้าหมาย</th>';
			$emptyRow['problem'] = '';
			$emptyRow['year-'.$year.'-problem'] = '';
			$emptyRow['year-'.$year.'-target'] = '';
		}
		$thead .= '</tr>';
		$thead .= '<tr>'.$head2.'</tr>';
		$tables->thead = $thead;

		foreach ($dbs->items as $rs) {
			if (!isset($tables->rows[$rs->catid])) {
				$tables->rows[$rs->catid] = $emptyRow;
				$tables->rows[$rs->catid]['no'] = ++$no.'.';
			}

			$detail = json_decode($rs->description);
			//debugMsg($detail,'$detail');
			$detail->problem = str_replace('<br />',"\n",$detail->problem);
			//$result->problem[$key]->indicator=$detail->indicator;
			$tables->rows[$rs->catid]['problem'] = $detail->problem;
			$tables->rows[$rs->catid]['year-'.$rs->pryear.'-problem'] = is_null($rs->problemsize) ? '' : number_format($rs->problemsize,2);
			$tables->rows[$rs->catid]['year-'.$rs->pryear.'-target'] = is_null($rs->targetsize) ? '' : number_format($rs->targetsize,2);
		}

		$ret .= $tables->build();

		//$ret .= print_o($dbs, '$dbs');

		/*
		$stmt = 'SELECT * FROM
			(
			SELECT
				  o.`tpid`, o.`trid`
				, o.`refid`
				, o.`detail1` `problem`
				, o.`text1` `detailproblem`
				, o.`detail2` `objective`
				, o.`text2` `detailobjective`
				, o.`text3` `indicator`
				, o.`num1` `problemsize`
				, o.`num2` `targetsize`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
				, tg.`catid`, tg.`description`
				, tg.`weight`
				, 1 `process`
			FROM %project_tr% o
				LEFT JOIN %tag% tg ON tg.`taggroup`=:taggroup AND tg.`catid`=o.`refid`
			WHERE o.`tpid`=:tpid AND o.`formid`=:tagname AND o.`part`="problem"
			) a

			UNION ALL

			SELECT * FROM
				(
				SELECT
				  NULL `tpid`, NULL `trid`
				, `catid` `refid`
				, `name` `problem`
				, NULL `detailproblem`
				, NULL `objective`
				, NULL `detailobjective`
				, NULL `indicator`
				, NULL `problemsize`
				, NULL `targetsize`
				, NULL `uid`, NULL `created`, NULL `modified`, NULL `modifyby`
				, `catid`, `description`
				, tg.`weight`
				, tg.`process`
				FROM %tag% tg
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`formid`=:tagname
						AND tr.`part`="problem" AND tr.`refid`=tg.`catid`
				WHERE `taggroup`=:taggroup AND tr.`trid` IS NULL
				) b
			ORDER BY IF(`refid` IS NULL,`trid`,`weight`) ASC, `refid` ASC
		';

		$dbs = mydb::select($stmt,':tpid', $tpid, ':tagname',$tagname, ':taggroup','project:problem:'.$this->planInfo->info->planGroup);
		*/

		$ret .= '<style type="text/css">
		.item.-compare th {text-align: center; white-space: nowrap}
		.item.-compare td:nth-child(n+3) {text-align: center;}
		</style>';

		//$ret .= print_o($dbs,'$dbs');

		//$ret .= print_o($this->planInfo, '$this->planInfo');

		return $ret;
	}
}
?>