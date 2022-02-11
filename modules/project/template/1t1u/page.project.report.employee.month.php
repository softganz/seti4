<?php
/**
* Project :: Report Employee Action Month
* Created 2021-02-18
* Modify  2021-02-18
*
* @param Object $self
* @return String
*
* @usage project/report/employee/month
*/

$debug = true;

function project_report_employee_month($self) {
	$getMonth = SG\getFirst(post('mm'), date('Y-m'));
	$getParentId = post('set');
	$getProjectId = post('id');

	$isAccess = is_admin('project') || user_access('administer projects');
	if (!$isAccess) {
		return message('error', 'Access Denied');
	}


	// View Model
	$ret = '';
	$toolbar = new Toolbar($self, 'รายงานการทำงานประจำเดือนของผู้รับจ้าง (ร่าง)');

	$ui = new Ui(NULL, 'ui-nav');
	//$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.kpi').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>บันทึกตัวชี้วัด</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('project/app/month/send/'.$projectId).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ส่งรายงาน</span></a>');

	$toolbar->addNav('main', $ui);


	// Get days and actions in each period
	mydb::where('p.`project_status` = "กำลังดำเนินโครงการ" AND p.`ownertype` IN ("'._PROJECT_OWNERTYPE_GRADUATE.'", "'._PROJECT_OWNERTYPE_STUDENT.'", "'._PROJECT_OWNERTYPE_PEOPLE.'")');
	if ($getProjectId) mydb::where('p.`tpid` = :projectId', ':projectId', $getProjectId);
	if ($getParentId) mydb::where('t.`parent` = :parentId', ':parentId', $getParentId);
	if ($getMonth) mydb::where(NULL, ':dateStart', date('Y-m-25',strtotime($getMonth.'-01 -1 day')), ':dateEnd', $getMonth.'-24');

	$stmt = 'SELECT
		p.`tpid`, t.`title`, m.`title` `parentTitle`
		, DATE_FORMAT(IF(DAYOFMONTH(a.`date1`) >= 25, DATE_ADD(LAST_DAY(a.`date1`), INTERVAL 1 DAY), a.`date1`) , "%Y-%m") `month`
		, COUNT(a.`trid`) `actions`, COUNT(DISTINCT a.`date1`) `days`
		FROM %project% p
			LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			LEFT JOIN %topic% m ON m.`tpid` = t.`parent`
			LEFT JOIN %project_tr% a ON a.`tpid` = p.`tpid` AND a.`formid` = "activity" AND a.`part` = "owner" AND a.`date1` BETWEEN :dateStart AND :dateEnd
		%WHERE%
		GROUP BY `tpid`
		ORDER BY CONVERT(`parentTitle` USING tis620) ASC, CONVERT(t.`title` USING tis620) ASC
		;
		-- {key: ""}';
	$actionMonth = mydb::select($stmt)->items;

	//$ret .= mydb()->_query;

	$ret .= '<header class="header"><h3>ประจำเดือน '.sg_date($getMonth.'-01', 'ดดด ปปปป').'</h3></header>';

	$tables = new Table();
	$tables->thead = array(
		'ผู้รับจ้าง',
		'days -amt' => 'วัน',
		'actions -amt' => 'กิจกรรม',
		'icons -check -nowrap -center' => 'ตรวจ',
		'icons -approve -nowrap -center' => 'รับรอง',
	);
	$tables->addConfig('showHeader', false);

	$currentParentTitle = '';
	foreach ($actionMonth as $rs) {
		$assignIcon = ($currentAssign = $assignMonth[sg_date($date,'Y-m-01')]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$kpiIcon = ($currentKpi = $kpiMonth[$date]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$sendIcon = ($currentSend = $sendMonth[$date]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$checkedIcon = ($currentChecked = $sendMonth[$date]->checked) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$approvedIcon = ($currentApproved = $sendMonth[$date]->approved) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		if ($rs->parentTitle != $currentParentTitle) {
			$tables->rows[] = array('<th colspan="5">'.$rs->parentTitle.'</th>');
			$currentParentTitle = $rs->parentTitle;
			$tables->rows[] = '<header>';
		}
		$tables->rows[] = array(
			'<a class="sg-action" href="'.url('project/app/activity',array('id' => $rs->tpid)).'" data-rel="box" data-width="640">'.$rs->title.'</a>',
			$rs->days ? $rs->days : '-',
			$rs->actions ? $rs->actions : '-',
			$checkedIcon,
			$approvedIcon,
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($actionMonth, '$actionMonth');
	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>