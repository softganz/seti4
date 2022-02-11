<?php
/**
* Project :: Fund Proposal Main Page
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/proposal[/$action]
*/

$debug = true;

function project_fund_proposal($self, $fundInfo = NULL, $action = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$ret='';

	R::view('project.toolbar',$self,'พัฒนาโครงการ - '.$fundInfo->name,'fund',$fundInfo);


	$fundid=$fundInfo->fundid;
	$isCreateProposal = $fundInfo->right->createProposal;

	if (empty($action)) {
		$waitProposal = __project_fund_proposal_waitreply($orgId);
	}

	if ($action == 'waitreply') {
		$self->theme->title = 'รอตอบรับ';
		mydb::where('t.`orgid` IS NULL AND d.`toorg` = :orgId', ':orgId', $orgId);
	} else {
		mydb::where('t.`orgid` = :orgId',':orgId',$orgId);
		if ($action == 'pass') mydb::where('p.`tpid` IS NOT NULL');
		else if ($action == 'notpass') mydb::where('p.`tpid` IS NULL');
	}

	$stmt = 'SELECT
		t.`tpid`, t.`title`, d.`budget`, d.`pryear`, t.`created`
		, d.`status`
		, SUM(b.`num1`) `totalBudget`
		, p.`tpid` `isProject`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_tr% b ON b.`tpid` = d.`tpid` AND b.`formid` = "develop" AND b.`part` = "activity"
		%WHERE%
		GROUP BY `tpid`
		ORDER BY `tpid` DESC';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;


	$ret .= '<div class="sg-view -co-2">';

	$ret .= '<div class="-sg-view">';

	$ret .= $waitProposal;

	$tables = new Table();
	$tables->addClass('-projectlist');
	$tables->id = 'project-fund-list';
	$tables->thead = array('year -date'=>'ปีงบประมาณ','date'=>'วันที่เริ่มพัฒนา','title -fill'=>'ชื่อพัฒนาโครงการ','amt'=>'งบประมาณ', 'status -nowrap'=>'สถานะ');

	if ($isCreateProposal) {
		// Prepare input form
		$ret .= '<form id="project-add" method="post" action="'.url('project/fund/'.$orgId.'/info/proposal.add').'">';

		$yearList = array();
		$yearList[date('Y')-1] = date('Y')+543-1;
		$yearList[date('Y')] = date('Y')+543;
		$yearList[date('Y')+1] = date('Y')+543+1;

		$yearDefault = date('m')>=9 ? date('Y')+1 : date('Y');

		$yearOption = '';
		foreach ($yearList as $key => $value) {
			$yearOption .= '<option value="'.$key.'" '.($key==$yearDefault ? 'selected="selected"' : '').'>'.$value.'</option>';
		}
		/*
		if (date('m')>=8) {
			$yearOption = '<option value="'.date('Y').'">'.(date('Y')+543).'</option>';
		} else {
			$yearOption = '<option value="'.date('Y').'">'.(date('Y')+543).'</option>';
		}
		$yearOption .= '<option value="'.(date('Y')+1).'">'.(date('Y')+543+1).'</option>';
		*/

		$tables->rows[] = array(
			'<select class="form-select" name="year">'.$yearOption.'</select>',
			'<input id="project-date" class="form-text sg-datepicker" type="text" name="created" placeholder="31/12/'.date('Y').'" size="10" value="'.date('d/m/Y').'" />',
			'<input id="project-title" class="form-text -fill" type="text" name="title" placeholder="ชื่อพัฒนาโครงการ" />',
			'<td colspan="2">'
			.'<input id="project-budget" class="form-text -money -fill" type="text" name="budget" placeholder="0.00" value="0" style="display:none;" />'
			.'<button class="btn -primary -nowrap" type="submit" value="add"><i class="icon -addbig -white"></i> เพิ่มพัฒนาโครงการ</button>'
			.'</td>'
		);
	}

	$tables = __project_fund_proposal_show($tables, $dbs);

	$ret .= $tables->build();

	if ($isCreateProposal) $ret .= '</form>';

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';

	$ret .= '<nav class="nav">';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/proposal/waitreply').'"><i class="icon -material -red">assistant</i><span>รอตอบรับ</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/proposal/notpass').'"><i class="icon -material">watch_later</i><span>รอพิจารณา</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/proposal/pass').'"><i class="icon -material">verified</i><span>ผ่านการพิจารณา</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/proposal/all').'"><i class="icon -material">view_list</i><span>พัฒนาโครงการทั้งหมด</span></a>');
	$ret .= $ui->build();
	$ret .= '</nav>';

	$ret .= '</div>';

	//$ret.=print_o($fundInfo,'$fundInfo');

	$ret.='<style type="text/css">
	.item.-projectlist .form-text {font-size:1.1em;}
	.item.-projectlist .form-select {height: 44px; font-size:1.1em;}
	.item.-projectlist .btn.-primary {padding: 9px 8px;}
	</style>';
	$ret.='<script type="text/javascript">
	$("#project-add").submit(function() {
		if ($("#project-date").val()=="") {
			$("#project-date").focus();
			return false;
		}
		if ($("#project-title").val()=="") {
			$("#project-title").focus();
			return false;
		}
		if ($("#project-budget").val()=="") {
			$("#project-budget").focus();
			return false;
		}
		return true;
	});
	</script>';
	return $ret;
}

function __project_fund_proposal_show($tables, $dbs) {
	$prevrs=NULL;
	$totalBudget=0;
	$statusList = project_base::$statusList;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->pryear + 543,
			($rs->created ? sg_date($rs->created,'ว ดดด ปปปป') : '??????'),
			'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst(trim($rs->title),'***** ยังไม่ระบุชื่อพัฒนาโครงการ *****').'</a>'
			.$rs->followTpid,
			number_format($rs->totalBudget,2),
			$statusList[$rs->status],
			'config'=>array('class'=>'project-develop-list -status-'.$rs->status.($rs->isProject ? ' -status-pass' : '')),
		);

		$totalBudget+=$rs->totalBudget;
		$prevrs=$rs;
	}

	$tables->tfoot[]=array('','','รวม',number_format($totalBudget,2),'');
	return $tables;
}

function __project_fund_proposal_waitreply($orgId) {

	mydb::where('t.`orgid` IS NULL AND d.`toorg` = :orgId', ':orgId', $orgId);

	$stmt = 'SELECT
			t.`tpid`, t.`title`, d.`budget`, d.`pryear`, t.`created`
			, SUM(b.`num1`) `totalBudget`
			, p.`tpid` `isProject`
			FROM %project_dev% d
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_tr% b ON b.`tpid` = d.`tpid` AND b.`formid` = "develop" AND b.`part` = "activity"
		%WHERE%
		GROUP BY `tpid`
		ORDER BY `tpid` DESC';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->addClass('-project-waitreply');
	$tables->caption = 'พัฒนาโครงการรอตอบรับ';
	$tables->thead = array('year -date'=>'ปีงบประมาณ','date'=>'วันที่เริ่มพัฒนา','title -fill'=>'ชื่อพัฒนาโครงการ','budget -amt -nowrap'=>'งบประมาณ');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->pryear + 543,
			($rs->created ? sg_date($rs->created,'ว ดดด ปปปป') : '??????'),
			'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst(trim($rs->title),'***** ยังไม่ระบุชื่อพัฒนาโครงการ *****').'</a>'
			.$rs->followTpid,
			number_format($rs->totalBudget,2),
			'config'=>array('class'=>'project-develop-list -status-'.$rs->status.($rs->isProject ? ' -status-pass' : '')),
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>