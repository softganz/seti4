<?php
/**
* Module :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/app/month/{id}
*/

$debug = true;

function project_app_month($self, $projectId = NULL) {
	// Data Model
	if (empty($projectId)) {
		$selectProject = R::View('project.select', '{class: "sg-action", rel: null, retUrl: "'.url('project/app/month/$id').'"}');
		if ($selectProject->error) {
			return message('error', 'ขออภัย '.$selectProject->error);
		} else if ($selectProject->projectId) {
			$projectId = $selectProject->projectId;
		} else {
			return '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'
				. $selectProject->build();
		}
	}

	//debugMsg($selectProject, '$selectProject');
	$projectInfo = R::Model('project.get', $projectId);
	$isAccess = is_admin() || $projectInfo->info->membershipType;
	if (!$isAccess) {
		return message('error', 'ขออภัย - โครงการนี้ท่านไม่สามารถเขียนบันทึกกิจกรรมได้');
	}

	if (empty($projectInfo->info->date_end) || empty($projectInfo->info->date_from) || $projectInfo->info->date_end <= $projectInfo->info->date_from) {
		return message('error', 'ยังไม่ได้กำหนดระยะเวลาดำเนินการ หรือ ระยะเวลาดำเนินการผิดพลาด');
	}

	$projectId = $projectInfo->projectId;


	// View Model
	$ret = '';

	$toolbar = new Toolbar($self, 'รายงานประจำเดือน');

	$ui = new Ui(NULL, 'ui-nav');
	$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.kpi').'" data-rel="box" data-width="480"><i class="icon -material">addchart</i><span>บันทึกตัวชี้วัด</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('project/app/month/send/'.$projectId).'" data-rel="box" data-width="480"><i class="icon -material">add_task</i><span>ส่งรายงาน</span></a>');

	$toolbar->addNav('main', $ui);

	// Get period information and days and actions in each period
	$periodDbs = mydb::select(
		'SELECT
		pd.`tpid`, pd.`period`, pd.`flag`
		, pd.`date1` `dateStart`, pd.`date2` `dateEnd`
		, COUNT(a.`trid`) `actions`, COUNT(DISTINCT a.`date1`) `days`
		FROM %project_tr% pd
			LEFT JOIN %project_tr% a ON a.`tpid` = pd.`tpid` AND a.`formid` = "activity" AND a.`part` = "owner" AND a.`date1` BETWEEN pd.`date1` AND pd.`date2`
		WHERE pd.`tpid` = :projectId AND pd.`formid` = "info" AND pd.`part` = "period"
		GROUP BY pd.`period`
		ORDER BY `period` ASC',
		':projectId', $projectId
	);
	//debugMsg($periodDbs, '$periodDbs');



	$stmt = 'SELECT `trid`, `tpid`, `date1` `assignMonth` FROM %project_tr% WHERE `formid` = "info" AND `part` = "assign" AND `tpid` = :tpid; -- {key: "assignMonth"}';
	$assignMonth = mydb::select($stmt, ':tpid', $projectId)->items;
	//$ret .= print_o($assignMonth,'$assignMonth');

	$stmt = 'SELECT `qtref`, `qtdate` FROM %qtmast% WHERE `tpid` = :tpid AND `qtform` = "psi"; -- {key: "qtdate"}';
	$kpiMonth = mydb::select($stmt, ':tpid', $projectId)->items;
	//$ret .= print_o($kpiMonth, '$kpiMonth');

	$stmt = 'SELECT `trid`, `tpid`, `date2` `sendMonth`, `detail2` `checked`, `detail3` `approved` FROM %project_tr% WHERE `formid` = "info" AND `part` = "send" AND `tpid` = :tpid; -- {key: "sendMonth"}';
	$sendMonth = mydb::select($stmt, ':tpid', $projectId)->items;
	//$ret .= print_o($sendMonth,'$sendMonth');

	$tables = new Table();
	$tables->thead = array(
		'period -date' => 'งวดเดือน',
		'icons -i1 -center' => '',
		'actions -amt -nowrap' => 'วัน/กิจกรรม',
		'icons -to -nowrap -center' => 'มอบงาน',
		'icons -kpi -nowrap -center' => 'ตัวชี้วัด',
		// 'icons -send  -nowrap -center' => 'ส่ง',
		'icons -check -nowrap -center' => 'ตรวจ',
		'icons -approve -nowrap -center' => 'รับรอง',
	);

	foreach ($periodDbs->items as $periodInfo) {
		$date = sg_date($periodInfo->dateEnd, 'Y-m-t');
		$assignIcon = ($currentAssign = $assignMonth[sg_date($date,'Y-m-01')]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$kpiIcon = ($currentKpi = $kpiMonth[$date]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$sendIcon = ($currentSend = $sendMonth[$date]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$checkedIcon = ($periodInfo->flag >= _PROJECT_PERIOD_FLAG_MANAGER) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$approvedIcon = ($periodInfo->flag >= _PROJECT_PERIOD_FLAG_GRANT) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">cancel</i>';

		$tables->rows[] = array(
			$periodInfo->actions ? '<a href="'.url('project/'.$projectId.'/operate.action.period/'.$periodInfo->period).'" title="รายละเอียดการทำกิจกรรม">'.sg_date($periodInfo->dateEnd,'ดด ปปปป').'</a>' : sg_date($periodInfo->dateEnd,'ดด ปปปป'),
			$periodInfo->actions ? '<a href="'.url('project/'.$projectId.'/operate.action.period/'.$periodInfo->period).'" title="รายละเอียดการทำกิจกรรม"><i class="icon -material">print</i></a>' : '',
			($periodInfo->actions ? $periodInfo->days.'/'.$periodInfo->actions : ''),
			$currentAssign ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.assign/'.$currentAssign->trid).'" data-rel="box" data-width="480" data-webview="มอบงาน">'.$assignIcon.'</a>' : $assignIcon,
			$currentKpi ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.kpi/'.$currentKpi->qtref).'" data-rel="box" data-width="480">'.$kpiIcon.'</a>' : $kpiIcon,
			//$currentSend ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.send.form/'.$currentSend->trid).'" data-rel="box" data-width="480">'.$sendIcon.'</a>' : $sendIcon,
			$checkedIcon,
			$approvedIcon,
		);

		$date = date('Y-m-t', strtotime($date.' +1 day'));
		if ($i++ >= 12*3) break;
	}

	$ret .= $tables->build();

	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>