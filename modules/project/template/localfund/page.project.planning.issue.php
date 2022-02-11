<?php
/**
* List Planning by Issue
* Created 2018-12-05
* Modify  2020-05-22
*
* @param Int $issueId
* @return String
*/

$debug = true;

class ProjectPlanningIssue extends Page {
	var $issueId;
	var $export;

	function __construct($issueId) {
		$this->issueId = $issueId;
		$this->export = post('export');
		parent::__construct();
	}

	function build() {
		$issueId = $this->issueId;
		$planTypeSelect = SG\getFirst(post('type'),NULL);
		$planSelect = SG\getFirst(post('plan'),NULL);
		$provinceSelect = SG\getFirst(post('changwat'),NULL);
		$ampurSelect = SG\getFirst(post('ampur'), NULL);
		$areaSelect = SG\getFirst(post('area'),NULL);
		$sectorSelect = SG\getFirst(post('sector'),NULL);
		$yearSelect = SG\getFirst(post('year'),NULL);

		$orderKey = SG\getFirst(post('o'),'mod');
		$orderList = [
			'tpid'=>'`tpid`',
			'title' => 'CONVERT(`title` USING tis620)',
			'prov' => 'CONVERT(`provname` USING tis620)',
			'date' => '`created` DESC',
			'mod' => '`lastModified` DESC',
			'tran' => '`totalTran` DESC',
			'rate' => '`rating` DESC',
			'approve' => 't.`approve`+0 DESC',
		];
		$order = $orderList[$orderKey];
		if (empty($order)) $order=$orderList['mod'];


		mydb::where('p.`prtype` = "แผนงาน" AND tr.`formid`="info" AND tr.`part`="title" AND tr.`refid`=:refid', ':refid', $issueId);
		if ($yearSelect) mydb::where('p.`pryear` IN ( :year )', ':year', 'SET:'.$yearSelect);

		if ($planTypeSelect == 'ampur') {
			mydb::where('t.`orgid` IS NULL AND LENGTH(t.`areacode` = 4)');
		} else {
			mydb::where('t.`orgid` IS NOT NULL');
		}

		if ($ampurSelect) {
			mydb::where('LEFT(t.`areacode`, 4) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$ampurSelect);
		} else if ($provinceSelect) {
			mydb::where('LEFT(t.`areacode`,2) IN ( :changwat )', ':changwat', 'SET:'.$provinceSelect);
		} else if ($areaSelect) {
			if ($planTypeSelect == 'ampur') {
				mydb::where('LEFT(t.`areacode`,2) IN (SELECT `changwat` FROM %project_fund% WHERE `areaid` = :areaSelect)', ':areaSelect', $areaSelect);
			} else if ($areaSelect) {
				mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$areaSelect);
			}
		}
		if ($sectorSelect) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$sectorSelect);

		mydb::value('$ORDER$', $order);

		$stmt = 'SELECT
			  tr.`tpid`
			, t.`orgid`
			, t.`title`
			, t.`rating`
			, t.`approve`
			, o.`name` `orgName`
			, t.`changwat`
			, cop.`provname` `changwatName`
			, t.`created`
			, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "info" AND `part` IN ("problem", "basic", "guideline", "project")) ) `totalTran`
			, (SELECT GREATEST(IFNULL(MAX(lm.`modified`),0),MAX(lm.`created`)) FROM %project_tr% lm WHERE lm.`tpid` = tr.`tpid` AND lm.`formid` = "info") `lastModified`
			FROM %project_tr% tr
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
			%WHERE%
			GROUP BY `tpid`
			ORDER BY $ORDER$
			';

		$dbs = mydb::select($stmt);
		// debugMsg(nl2br(mydb()->_query));


		$parameter = ['type' => $planTypeSelect, 'area' => $areaSelect, 'changwat' => $provinceSelect, 'ampur' => $ampurSelect, 'year' => $yearSelect];

		$tables = new Table();
		$tables->thead = [
			'<a class="sg-action" href="'.url('project/planning/issue/'.$issueId, ['o'=>'title'] + $parameter).'" data-rel="box->clear" data-width="full">แผนงาน</a>'.($orderKey == 'title' ? ' <i class="icon -sort"></i>' : ''),
			'approve -center -nowrap'.($orderKey == 'approve' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'approve'] + $parameter).'" data-rel="box->clear" data-width="full" title="เรียงตามสถานะ"><i class="icon -material">verified</i></a>'.($orderKey == 'approve' ? ' <i class="icon -sort"></i>' : ''),
			'tran -center -nowrap'.($orderKey == 'tran' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'tran'] + $parameter).'" data-rel="box->clear" data-width="full" title="เรียงตามจำนวนรายการข้อมูล"><i class="icon -material">playlist_add_check</i></a>'.($orderKey == 'tran' ? ' <i class="icon -sort"></i>' : ''),
			'rate -center -nowrap' => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'rate'] + $parameter).'" data-rel="box->clear" data-width="full"><i class="icon -material">star</i></a>'.($orderKey == 'rate' ? ' <i class="icon -sort"></i>' : ''),
			'center -changwat -nowrap' => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'prov'] + $parameter).'" data-rel="box->clear" data-width="full">จังหวัด</a>'.($orderKey == 'prov' ? ' <i class="icon -sort"></i>' : ''),
			'created -date -nowrap' => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'date'] + $parameter).'" data-rel="box->clear" data-width="full">วันที่สร้างแผนงาน</a>'.($orderKey == 'date' ? ' <i class="icon -sort"></i>' : ''),
			'modified -date -nowrap' => '<a class="sg-action" href="'.url('project/planning/issue/'.$issueId,['o'=>'mod'] + $parameter).'" data-rel="box->clear" data-width="full">วันที่แก้ไข</a>'.($orderKey == 'mod' ? ' <i class="icon -sort"></i>' : ''),
		];

		foreach ($dbs->items as $rs) {
			$tables->rows[] = [
				'<a href="' . url('project/planning/' . $rs->tpid) . '" target="_blank">' . $rs->title . '</a>'
				. '<br /><em>'.$rs->orgName.'</em>',
				'<i class="icon -material -'.['MASTER' => 'green', 'USE' => 'yellow', 'LEARN' => 'gray'][$rs->approve].'">'.['MASTER' => 'verified', 'USE' => 'recommend', 'LEARN' => 'flaky'][$rs->approve].'</i>',
				$rs->totalTran ? '<i class="icon -material -sg-level -level-'.(round($rs->totalTran/10) + 1).'" title="จำนวน '.$rs->totalTran.' รายการ">playlist_add_check</i>' : '',
				'<i class="icon -material rating-star '.($rs->rating != '' ? '-rate-'.round($rs->rating) : '').'">star</i>',
				$rs->changwatName,
				$rs->created,
				$rs->lastModified ? sg_date($rs->lastModified, 'Y-m-d H:i:s') : '',
			];
		}

		if ($this->export) {
			die(R::Model('excel.export',$tables,'แผนงาน ปี '.($yearSelect + 543).' @'.date('Y-m-d H:i:s').'.xls','{debug:false}'));
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงานของประเด็น '.$dbs->count().' แผนงาน',
				'leading' => _HEADER_BACK,
				'trailing' => new Row([
					'children' => [
						'<a class="btn" class="" href="'.url('project/planning/issue/'.$this->issueId, $parameter+['o' => $orderKey, 'export' => 'excel']).'"><i class="icon -material">download</i><span>EXPORT</span></a>',
					],
				]),
				'boxHeader' => true,
			]), // AppBar
			'children' => [
				// '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนงานของประเด็น '.$dbs->count().' แผนงาน</h3></header>',
				$tables,
			],
		]);
	}
}
?>