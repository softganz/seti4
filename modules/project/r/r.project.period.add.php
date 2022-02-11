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
* @usage R::Model("project.period.add", $condition, $options)
*/

$debug = true;

function r_project_period_add($projectInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$projectId = $projectInfo->projectId;

	$result = NULL;

	if ($options->period) {
		foreach ($options->period as $key => $value) {
			$data = new stdClass();
			$data->projectId = $projectId;
			$data->uid = SG\getFirst($options->uid, i()->uid);
			$data->formid = 'info';
			$data->part = 'period';
			$data->period = $key;
			$data->dateStart = SG\getFirst($value['start']);
			$data->dateEnd = SG\getFirst($value['end']);
			$data->budget = SG\getFirst($value['budget']);
			$data->created = date('U');

			$stmt = 'INSERT INTO %project_tr%
				(`tpid`, `uid`, `formid`, `part`, `period`, `date1`, `date2`, `num1`, `created`)
				VALUES
				(:projectId, :uid, :formid, :part, :period, :dateStart, :dateEnd, :budget, :created)';
			mydb::query($stmt, $data);
			if ($debug) debugMsg(mydb()->_query);
		}
	}

	return $result;
}
?>