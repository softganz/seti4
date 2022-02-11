<?php
/**
* Project :: Fund Follow Main Page
* Created 2020-06-08
* Modify  2020-06-08
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/follow[/$action]
*/

$debug = true;

function project_fund_follow($self, $fundInfo, $action = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$ret = '';

	$isCreateProject = $fundInfo->right->createFollow;

	R::view('project.toolbar',$self,'ติดตามโครงการ - '.$fundInfo->name,'fund',$fundInfo);

	$ret .= __project_fund_follow_summary($orgId);

	$ret .= '<div class="sg-view -co-2">';

	$ret .= '<div class="-sg-view">';

	$ret .= '<div class="project-list">';

	mydb::where('p.`prtype` = "โครงการ" AND t.`orgid` = :orgid',':orgid',$orgId);
	if ($action == 'close') mydb::where('p.`project_status` != :status',':status',1);
	else if ($action == 'all') ;
	else mydb::where('p.`project_status` = :status',':status',1);

	$stmt = 'SELECT
		t.`tpid`, t.`title`, p.`pryear`, p.`budget`
		, p.`project_status`, p.`project_status`+0 `projectStatusCode`
		, p.`date_approve`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY `tpid` DESC;
		-- {sum: "budget"}';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	// New table
	// <ทุกปี:select>,วันที่อนุมัติ,โครงการ/กิจกรรม,งบประมาณ,เบิกจ่าย,รับคืน,คงเหลือ,สถานะ

	$tables = new Table();
	$tables->addClass('-projectlist');
	$tables->id = 'project-fund-list';
	$tables->thead=array('date'=>'วันที่อนุมัติ','โครงการ/กิจกรรม','amt'=>'<th colspan="2">งบประมาณ</th>');

	if ($isCreateProject) {
		// Prepare input form
		$ret.='<form id="project-add" class="sg-form" method="post" action="'.url('project/fund/'.$orgId.'/info/follow.add').'" data-checkvalid="1">';

		$stmt='SELECT * FROM %tag% WHERE `taggroup`="project:planning"';
		$issueDbs=mydb::select($stmt);
		$optionsIssue='';
		foreach ($issueDbs->items as $rs) {
			$optionsIssue.='<abbr class="checkbox -block"><label><input type="checkbox" class="" name="supportplan['.$rs->catid.']" value="'.$rs->catid.'" /> '.$rs->name.'</label></abbr>';
		}

		$supportTypeNameList=model::get_category('project:supporttype','catid');
		$supportTypeNameOptions='<option value="">== เลือกประเภท ==</option>';
		foreach ($supportTypeNameList as $key => $value) {
			$supportTypeNameOptions.='<option value="'.$key.'">'.$value.'</option>';
		}

		$supportOrgNameList=model::get_category('project:supportorg','catid');
		$supportOrgNameOptions='<option value="">== เลือกหน่วยงาน ==</option>';
		foreach ($supportOrgNameList as $key => $value) {
			$supportOrgNameOptions.='<option value="'.$key.'">'.$value.'</option>';
		}

		$month=date('m');
		if ($month<10) $optionsBudgetYear.='<option value="'.(date('Y')).'">'.(date('Y')+543).'</option>';
		if ($month>=4) $optionsBudgetYear.='<option value="'.(date('Y')+1).'">'.(date('Y')+543+1).'</option>';

		$tables->rows[]=array(
			'<label class="-hidden" for="project-date" style="display:none;">วันที่อนุมัติ</label><input id="project-date" class="form-text sg-datepicker -require" type="text" name="date_approve" placeholder="31/12/'.date('Y').'" size="10" readonly="readonly" />',
			'<label class="-hidden" for="project-title" style="display:none;">ชื่อโครงการ	</label><input id="project-title" class="form-text -fill -require" type="text" name="title" placeholder="ชื่อโครงการ" />'
			.'<div id="project-input-other" class="-hidden" style="margin:8px 0;">'
			. ($fundInfo->fundid ? '<div class="form-item"><label>ประเภทการสนับสนุน:</label><select class="form-select -fill -require" name="typename">'.$supportTypeNameOptions.'</select></div>'
			.'<div class="form-item"><label>หน่วยงาน/องค์กร/กลุ่มคน ที่รับผิดชอบโครงการ:</label><select class="form-select -fill -require" name="orgname">'.$supportOrgNameOptions.'</select></div>' : '')
			.'<div class="form-item"><label>ปีงบประมาณ:</label><select class="form-select -require" name="pryear">'.$optionsBudgetYear.'</select></div>'
			.'<div class="form-item"><label>ความสอดคล้องกับแผนงาน:</label>'.$optionsIssue.'</div>'
			.'</div>',
			'<td colspan="2">'
			.'<input id="project-budget" class="form-text -money -fill -require" type="text" name="budget" placeholder="0.00" />'
			.'<p align="right"><button class="btn -primary" type="submit" value="เพิ่มโครงการ"><i class="icon -addbig -white"></i> เพิ่มโครงการ</button></p>'
			.'</td>'
		);
	}

	foreach ($dbs->items as $rs) {
		if ($rs->projectStatusCode == 1) {
			// Proceed
			$statusIcon = '<i class="icon -material -green" title="'.$rs->project_status.'">directions_run</i>';
		} else if ($rs->projectStatusCode == 2) {
			// Normal Close
			$statusIcon = '<i class="icon -material -gray" title="'.$rs->project_status.'">verified</i>';
		} else if ($rs->projectStatusCode == 3) {
			// Stop
			$statusIcon = '<i class="icon -material -gray" title="'.$rs->project_status.'">pan_tool</i>';
		} else if ($rs->projectStatusCode == 3) {
			// Block
			$statusIcon = '<i class="icon -material -gray" title="'.$rs->project_status.'">block</i>';
		} else {
			// Unknown
			$statusIcon = '<i class="icon -material -gray" title="'.$rs->project_status.'"></i>';
		}
		$tables->rows[] = array(
			($rs->date_approve ? sg_date($rs->date_approve,'ว ดดด ปปปป') : '??????')
			.'<br />('.($rs->pryear+543).')',
			'<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'***** ยังไม่ระบุชื่อโครงการ *****').'</a>'. ($rs->projectStatusCode == 1 ? '' : '<em> ('.$rs->project_status.')</em>'),
			number_format($rs->budget,2),
			$statusIcon,
			'config' => array('class'=>'-status-'.$rs->projectStatusCode)
		);

	}

	$tables->tfoot[] = array('','รวม',number_format($dbs->sum->budget,2),'');

	$ret .= $tables->build();

	if ($isCreateProject) $ret .= '</form>';

	$ret .= '</div>';

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';

	$ret .= '<nav class="nav">';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/proposal').'"><i class="icon -material">nature_people</i><span>พัฒนาโครงการ</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/follow').'"><i class="icon -material">directions_run</i><span>กำลังดำเนินการ</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/follow/close').'"><i class="icon -material">verified</i><span>ดำเนินการเรียบร้อย</span></a>');
	$ui->add('<a class="" href="'.url('project/fund/'.$orgId.'/follow/all').'"><i class="icon -material">view_list</i><span>โครงการทั้งหมด</span></a>');
	$ret .= $ui->build();
	$ret .= '</nav>';

	$ret .= '</div>';

	//$ret.=print_o($dbs,'$dbs');
	$ret .= '<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary p {margin:0; padding:0 0 0 16px;}
	.project-summary>div {width:33%; display:inline-block;vertical-align: top;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.2em; line-height:1.2em;}
	.item.-projectlist .form-text {font-size:1.1em;}
	.item.-projectlist .button {width:100%; padding:8px 0;}
	.item.-projectlist tr.-status-2 * {color: gray;}
	.item.-projectlist tr.-status-3 * {color: #dc7979; text-decoration: line-through;}
	.item.-projectlist tr.-status-4 * {color: #dccc79; text-decoration: line-through;}
	</style>
	<script type="text/javascript">
	$("#project-title").focus(function(){
		$("#project-input-other").show();
	});
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

function __project_fund_follow_summary($orgId) {
	// Show summary report
	$stmt = 'SELECT
		  COUNT(IF(p.`project_status`=1,1,NULL)) `activeProjects`
		, COUNT(*) `totalProjects`
		, SUM(IF(p.`project_status`=1,p.`budget`,NULL)) `activeBudget`
		, SUM(p.`budget`) `totalBudgets`
		, (SELECT SUM(`num1`) `totalPaid`
				FROM %project_tr% pd
					LEFT JOIN %topic% pt ON pt.`tpid`=pd.`tpid`
				WHERE pd.`formid`="paiddoc" AND pd.`part`="title" AND YEAR(pd.`date1`)=YEAR(CURDATE()) AND pt.`orgid`=:orgid
				LIMIT 1) `totalPaid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE p.`prtype`="โครงการ" AND `orgid`=:orgid AND `pryear`=YEAR(CURDATE())
		LIMIT 1;';

	$thisYearSum = mydb::select($stmt,':orgid',$orgId);

	$stmt = 'SELECT
		  COUNT(IF(p.`project_status`=1,1,NULL)) `activeProjects`
		, COUNT(*) `totalProjects`
		, SUM(IF(p.`project_status`=1,p.`budget`,NULL)) `activeBudget`
		, SUM(p.`budget`) `totalBudgets`
		, (SELECT SUM(`num1`) `totalPaid`
				FROM %project_tr% pd
					LEFT JOIN %topic% pt ON pt.`tpid`=pd.`tpid`
				WHERE pd.`formid`="paiddoc" AND pd.`part`="title" AND YEAR(pd.`date1`)=YEAR(CURDATE())-1 AND pt.`orgid`=:orgid
				LIMIT 1) `totalPaid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE p.`prtype`="โครงการ" AND `orgid`=:orgid AND `pryear`=YEAR(CURDATE())-1
		LIMIT 1;';

	$prevYearSum = mydb::select($stmt,':orgid',$orgId);

	$stmt = 'SELECT
		  COUNT(IF(p.`project_status`=1,1,NULL)) `activeProjects`
		, COUNT(*) `totalProjects`
		, SUM(IF(p.`project_status`=1,p.`budget`,NULL)) `activeBudget`
		, SUM(p.`budget`) `totalBudgets`
		, (SELECT SUM(`num1`) `totalPaid`
				FROM %project_tr% pd
					LEFT JOIN %topic% pt ON pt.`tpid`=pd.`tpid`
				WHERE pd.`formid`="paiddoc" AND pd.`part`="title" AND pt.`orgid`=:orgid
				LIMIT 1) `totalPaid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE p.`prtype`="โครงการ" AND `orgid`=:orgid
		LIMIT 1;';

	$allYearSum = mydb::select($stmt,':orgid',$orgId);

	$ret .= '<div class="project-summary">';
	$ret .= '<div class="thisyearprojects"><span>ปีนี้</span><p>โครงการ <span class="itemvalue">'.number_format($thisYearSum->activeProjects).'/'.number_format($thisYearSum->totalProjects).'</span><span> โครงการ</span></p><p>งบประมาณ <span class="itemvalue">'.number_format($thisYearSum->activeBudget,2).'/'.number_format($thisYearSum->totalBudgets,2).'</span><span> บาท</span></p></div>';
	$ret .= '<div class="lastyearprojects"><span>ปีที่แล้ว</span><p>โครงการ <span class="itemvalue">'.number_format($prevYearSum->activeProjects).'/'.number_format($prevYearSum->totalProjects).'</span><span> โครงการ</span></p><p>งบประมาณ <span class="itemvalue">'.number_format($prevYearSum->activeBudget,2).'/'.number_format($prevYearSum->totalBudgets,2).'</span><span> บาท</span></p></div>';
	$ret .= '<div class="totalprojects"><span>ทั้งหมด</span><p>โครงการ <span class="itemvalue">'.number_format($allYearSum->activeProjects).'/'.number_format($allYearSum->totalProjects).'</span><span> โครงการ</span></p><p>งบประมาณ <span class="itemvalue">'.number_format($allYearSum->activeBudget,2).'/'.number_format($allYearSum->totalBudgets,2).'</span><span> บาท</span></p></div>';
	$ret .= '</div>';

	return $ret;
}
?>