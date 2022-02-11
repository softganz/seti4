<?php
/**
* Project :: Planning of Co-Organization
* Created 2021-10-12
* Modify  2021-10-12
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org.co.proposal
*/

$debug = true;

class ProjectInfoOrgCoProposal extends Page {
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

		mydb::where('t.`orgid` IN (SELECT `orgid` FROM %project_orgco% WHERE `tpid` = :projectId)', ':projectId' , $this->projectId);
		if ($this->issue) mydb::where('po.`refid` = :supportType', ':supportType', $this->issue);

		$dbs = mydb::select(
			'SELECT
			t.`tpid`, t.`title`, d.`pryear`
			, d.`budget`
			, t.`approve`
			, t.`created`
			FROM %project_dev% d
				LEFT JOIN %topic% t ON t.`tpid` = d.`tpid`
				LEFT JOIN %project_tr% `po` ON po.`tpid` = d.`tpid` AND po.`formid` = "develop" AND po.`part` = "supportplan"
			%WHERE%
			ORDER BY d.`pryear` DESC, d.`tpid` DESC'
		);

		// debugMsg($dbs, '$dbs');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'พัฒนาโครงการองค์กรร่วม',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => [
							'year -date' => 'ปีงบประมาณ',
							'พัฒนาโครงการ',
							'<i class="icon -material">verified</i>',
							'budget -money' => 'งบประมาณ(บาท)',
							'create -date' => 'วันที่สร้าง',
						],
						'children' => array_map(function($item) {
							return [
								$item->pryear+543,
								'<a href="'.url('project/develop/'.$item->tpid).'" target="_blank">'.$item->title.'</a>',
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