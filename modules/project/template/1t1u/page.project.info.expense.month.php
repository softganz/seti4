<?php
/**
* Project :: Follow Month Expense
* Created 2021-03-24
* Modify  2021-03-24
*
* @param Object $self
* @param Object $projectInfo
* @param Int $periodId
* @return String
*
* @usage project/{id}/info.expense.month/{periodId}
*/

$debug = true;

function project_info_expense_month($self, $projectInfo, $periodId = NULL) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$actions = R::Model('project.action.get', $projectId, '{resultGroup: "actionMonth"}');



	// View Model
	$toolbar = new Toolbar($self, 'ค่าใช้จ่ายประจำเดือน');

	$ret = '';

	$tables = new Table();
	$tables->thead = array(
		'date -date' => 'วันที่',
		'กิจกรรม',
		'total -money' => 'จำนวนเงิน',
	);

	$totalExpense = 0;

	foreach ($actions as $group) {
		$totalMonth = 0;
		foreach ($group as $rs) {
			$tables->rows[] = array(
				sg_date($rs->actionDate, 'ว ดด ปปปป'),
				$rs->title,
				number_format($rs->exp_total,2)
			);

			$totalMonth += $rs->exp_total;
			$totalExpense += $rs->exp_total;
		}

		$tables->rows[] = array(
			'',
			'รวมค่าใช้จ่ายเดือน'.sg_date($rs->actionMonth.'-01', 'ดดด ปปปป'),
			number_format($totalMonth,2),
			'config' => '{class: "subheader"}',
		);

	}

	$tables->rows[] = array(
		'',
		'รวมค่าใช้จ่าย',
		number_format($totalExpense,2),
			'config' => '{class: "subheader"}',
	);
	$ret .= $tables->build();

	//$ret .= print_o($actions, '$actions');
	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>