<?php
/**
* Module :: Description
* Created 2021-12-06
* Modify  2021-12-06
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class OrgInfoProject extends Page {
	var $orgId;
	var $order;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->order = post('prorder');
	}

	function build() {
		if ($this->order == 'year') {
			$order = 'p.`pryear` DESC';
		} else if ($this->order == 'title') {
			$order = 'CONVERT(t.`title` USING tis620) ASC';
		} else {
			$order = 't.`tpid` DESC';
		}

		mydb::value('$ORDER$', 'ORDER BY '.$order);

		$stmt = 'SELECT
				  p.`tpid`, p.`pryear`, t.`title`, p.`budget`
				, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "info" AND `part` = "activity") `activity`
				, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "activity" AND `part` = "owner") `action`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE p.`prtype` = "โครงการ" AND t.`orgid` = :orgid AND p.`project_status` = 1
			$ORDER$;
			-- {sum:"budget,totalPaid,action,activity"}';

		$dbs = mydb::select($stmt,':orgid',$this->orgId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]),
			'body' => new Card([
				'class' => '-org-project',
				'children' => [
					// new ListTile([
					// 	'class' => '-sg-paddingnorm',
					// 	'title' => 'โครงการ/กิจกรรม',
					// 	'leading' => '<i class="icon -material">directions_run</i>',
					// ]),
					new Table([
						'thead' => [
							'amt -year -nowrap'=>'<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.project', ['prorder' => 'year']).'" data-rel=".widget-card.-org-project">ปี</a>',
							'<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.project', ['prorder' => 'title']).'" data-rel=".widget-card.-org-project">ชื่อโครงการ</a>',
							'amt -budget -nowrap'=>'งบประมาณ',
							'amt -act -nowrap'=>'กิจกรรม'],
						'children' => (function($dbs) {
							$rows = [];
							foreach ($dbs->items as $rs) {
								$rows[] = [
									$rs->pryear+543,
									'<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'===ยังไม่ระบุชื่อโครงการ===').'</a>',
									number_format($rs->budget,2),
									$rs->action.'/'.$rs->activity
								];
							}
							return $rows;
						})($dbs), // children
						'tfoot' => [
							[
								'',
								'',
								number_format($dbs->sum->budget,2),
								$dbs->sum->action.'/'.$dbs->sum->activity,
							],
						], // tfoot
					]),
					'<nav class="nav -page -sg-text-right">จำนวน <b>'.$dbs->_num_rows.'</b> โครงการ ',
					'<a class="btn -link" href="'.url('org/'.$this->orgId.'/info.follow').'"><i class="icon -list"></i><span>โครงการทั้งหมด</span></a></nav>',
				],
			]),
		]);
	}
}
?>