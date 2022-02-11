<?php
/**
* Project :: Follow of Co-Organization
* Created 2021-10-12
* Modify  2021-10-12
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org.co.follow
*/

$debug = true;

class ProjectInfoOrgCoFollow extends Page {
	var $projectId;
	var $right;
	var $issue;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = $projectInfo->right;
		$this->issue = post('issue');
	}

	function build() {
		if (!$this->projectId) return message('error', 'PROCESS ERROR');

		mydb::where('p.`prtype` = "โครงการ"');
		mydb::where('t.`orgid` IN (SELECT `orgid` FROM %project_orgco% WHERE `tpid` = :projectId)', ':projectId' , $this->projectId);
		if ($this->issue) mydb::where('issue.`refid` = :supportType', ':supportType', $this->issue);

		$dbs = mydb::select(
			'SELECT
			t.`tpid`, t.`title`, p.`pryear`
			, p.`budget`
			, t.`approve`
			, t.`created`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %project_tr% issue ON issue.`tpid` = p.`tpid` AND issue.`formid` = "info" AND issue.`part` = "supportplan"
			%WHERE%
			ORDER BY p.`pryear` DESC, p.`tpid` DESC'
		);

		// debugMsg($dbs, '$dbs');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'โครงการองค์กรร่วม',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => [
							'year -date' => 'ปีงบประมาณ',
							'โครงการ',
							'<i class="icon -material">verified</i>',
							'budget -money' => 'งบประมาณ(บาท)',
							'create -date' => 'วันที่สร้าง',
						],
						'children' => array_map(function($item) {
							return [
								$item->pryear+543,
								'<a href="'.url('project/'.$item->tpid).'" target="_blank">'.$item->title.'</a>',
								'<i class="icon -material -'.['MASTER' => 'green', 'USE' => 'yellow', 'LEARN' => 'gray'][$item->approve].'">'.['MASTER' => 'verified', 'USE' => 'recommend', 'LEARN' => 'flaky'][$item->approve].'</i>',
								number_format($item->budget, 2),
								sg_date($item->created,'ว ดด ปปปป'),
							];
						}, $dbs->items),
					]),
				], // children
			]), // Widget
		]);
	}
}
?>