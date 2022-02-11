<?php
/**
* Model :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("project.employee.period.create", $projectInfo, $options)
*/

$debug = true;

function r_project_employee_period_create($projectInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$followCfg = cfg('project')->follow;

	$result = NULL;

	$options = new stdClass();

	$date = sg_date($projectInfo->info->date_from, 'Y-m-t');
	$period = 1;

	do {
		$options->period->{$period} = array(
			'start' => date('Y-m-'.($followCfg->dayEndReport+1), strtotime(sg_date($date, 'Y-m-01').' -1 day')),
			'end' => sg_date($date, 'Y-m-'.$followCfg->dayEndReport),
			'budget' => $followCfg->ownerType->{$projectInfo->info->ownertype}->budget,
		);
		$date = date('Y-m-t', strtotime($date.' +1 day'));
		$period++;
	} while ($date <= $projectInfo->info->date_end && $period <= 12);

	$lastKey = end(array_keys((array) $options->period));

	if ($options->period->{1}) $options->period->{1}['start'] = $projectInfo->info->date_from;
	if ($lastKey) $options->period->{$lastKey}['end'] = $projectInfo->info->date_end;

	R::Model('project.period.add', $projectInfo, $options);
	//debugMsg($options, '$options');

	$result = R::Model('project.period.get', $projectInfo->projectId);

	return $result;
}
?>