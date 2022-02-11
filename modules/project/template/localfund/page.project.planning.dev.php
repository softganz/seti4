<?php
/**
* Project :: Proposal of planning
* Created 2018-12-05
* Modify  2021-06-16
*
* @param Int $issueId
* @return Widget
*
* @usage project/planning/dev/{issueId}
*/

$debug = true;

class ProjectPlanningDev extends Page {
	var $issueId;

	function __construct($issueId) {
		$this->issueId = $issueId;
	}

	function build() {
		$issueId = $this->issueId;

		$planSelect = SG\getFirst(post('plan'),NULL);
		$provinceSelect = SG\getFirst(post('changwat'),NULL);
		$ampurSelect = SG\getFirst(post('ampur'), NULL);
		$areaSelect = SG\getFirst(post('area'),NULL);
		$sectorSelect = SG\getFirst(post('sector'),NULL);
		$yearSelect = SG\getFirst(post('year'),NULL);


		$orderKey=SG\getFirst(post('o'),'date');
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
		if (empty($order)) $order=$orderList['date'];

		mydb::where('tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid`=:refid', ':refid', $issueId);
		if ($ampurSelect) {
			mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$ampurSelect);
		} else if ($provinceSelect) mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$provinceSelect);
		if ($areaSelect) mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$areaSelect);
		mydb::value('$order', $order);
		if ($sectorSelect) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$sectorSelect);
		if ($yearSelect) mydb::where('d.`pryear` IN ( :year )', ':year', 'SET:'.$yearSelect);

		$stmt = 'SELECT
			tr.`tpid`, t.`orgid`, t.`title`
			, t.`approve`
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
				LEFT JOIN %co_province% cop ON cop.`provid` = IFNULL(t.`changwat`,o.`changwat`)
			%WHERE%
			ORDER BY $order;
			-- {sum: "budget"}
			';
		$dbs = mydb::select($stmt);
		// debugMsg(nl2br(mydb()->_query));




		$parameter = ['type' => $planTypeSelect, 'area' => $areaSelect, 'changwat' => $provinceSelect, 'ampur' => $ampurSelect, 'year' => $yearSelect];

		$tables = new Table();
		$tables->thead = array(
			'<a class="sg-action" href="'.url('project/planning/dev/'.$issueId, ['o'=>'title'] + $parameter).'" data-rel="box->clear" data-width="full">พัฒนาโครงการ</a>'.($orderKey == 'title' ? ' <i class="icon -sort"></i>' : ''),
			'approve -center -nowrap'.($orderKey == 'approve' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueId,['o'=>'approve'] + $parameter).'" data-rel="box->clear" data-width="full" title="เรียงตามสถานะ"><i class="icon -material">verified</i></a>'.($orderKey == 'approve' ? ' <i class="icon -sort"></i>' : ''),
			'tran -center -nowrap'.($orderKey == 'tran' ? ' -sort' : '') => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueId,['o'=>'tran'] + $parameter).'" data-rel="box->clear" data-width="full" title="เรียงตามจำนวนรายการข้อมูล"><i class="icon -material">playlist_add_check</i></a>'.($orderKey == 'tran' ? ' <i class="icon -sort"></i>' : ''),
			'rate -center -nowrap' => '<i class="icon -material">star</i>',
			'center -chanhwat' => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueId,['o'=>'prov'] + $parameter).'" data-rel="box->clear" data-width="full">จังหวัด</a>'.($orderKey == 'prov' ? ' <i class="icon -sort"></i>' : ''),
			'budget -money' => 'งบประมาณ(บาท)',
			'date' => '<a class="sg-action" href="'.url('project/planning/dev/'.$issueId,['o'=>'date'] + $parameter).'" data-rel="box-clear" data-width="full">วันที่เริ่มพัฒนา</a>'.($orderKey == 'date' ? ' <i class="icon -sort"></i>' : '')
		);

		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a href="' . url('project/develop/' . $rs->tpid) . '" target="_blank">' . SG\getFirst($rs->title, 'ไม่ระบุชื่อ') . '</a>'
				. '<br /><em>'.$rs->orgName.'</em>',
				'<i class="icon -material -'.['MASTER' => 'green', 'USE' => 'yellow', 'LEARN' => 'gray'][$rs->approve].'">'.['MASTER' => 'verified', 'USE' => 'recommend', 'LEARN' => 'flaky'][$rs->approve].'</i>',
				$rs->totalTran ? '<i class="icon -material -sg-level -level-'.(round($rs->totalTran/10) + 1).'" title="จำนวน '.$rs->totalTran.' รายการ">playlist_add_check</i>' : '',
				'<i class="icon -material rating-star '.($rs->rating != '' ? '-rate-'.round($rs->rating) : '').'">star</i>',
				$rs->provname,
				number_format($rs->budget,2),
				$rs->created
			);
		}
		$tables->tfoot[] = array('รวม '.$dbs->count().' โครงการ', '', '', '', number_format($dbs->sum->budget,2), '');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงานตามประเด็น',
			]), // AppBar
			'children' => [
			'<header class="header -box -hidden">'._HEADER_BACK.'<h3>โครงการที่พัฒนา '.$dbs->count().' โครงการ</h3></header>',
				$tables,
			],
		]);
	}
}
?>