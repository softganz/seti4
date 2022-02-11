<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_options_get($projectInfo, $options = '{}') {
	$defaults = '{debug: false, result: object"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$stmt = 'SELECT p.`value` `projectOption`, pp.`value` `parentOption`
		FROM %topic% t
			LEFT JOIN %property% p ON p.`propid` = t.`tpid` AND p.`module` = "project" AND p.`name` = "options"
			LEFT JOIN %property% pp ON pp.`propid` = t.`parent` AND pp.`module` = "project" AND pp.`name` = "options"
		WHERE t.`tpid` = :tpid
		LIMIT 1';

	$topicOption = mydb::select($stmt, ':tpid', $projectInfo->tpid, ':parentId', $projectInfo->info->parent);
	debugMsg(mydb()->_query);

	if ($options->result == 'source') {
		$result = $topicOption;
	} else {
		$result = sg_json_decode(
			$topicOption->projectOption,
			$topicOption->parentOption,
			cfg('project.options')
		);
	}

	debugMsg($topicOption, '$topicOption');
	//debugMsg($result,'$result');
	//debugMsg($projectInfo,'$projectInfo');

	return $result;
}
?>