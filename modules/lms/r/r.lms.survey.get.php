<?php
/**
* LMS :: Get Survey Information
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_lms_survey_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['surveyId' => $conditions];
	}

	$surveyId = $conditions->surveyId;

	mydb::where('`qtref` = :surveyId', ':surveyId', $surveyId);

	$stmt = 'SELECT
		q.*
		, FROM_UNIXTIME(`created`) `created`
		, s.`courseid`, s.`modid`
		, m.`name` `moduleName`
		, c.`name` `courseName`
		FROM %qtmast% q
			LEFT JOIN %lms_survey% s ON s.`surid` = q.`lmssurid`
			LEFT JOIN %lms_mod% m ON m.`modid` = s.`modid`
			LEFT JOIN %lms_course% c ON c.`courseid` = s.`courseid`
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	$result->surveyId = $rs->qtref;
	$result->courseId = $rs->courseid;
	$result->moduleId = $rs->modid;
	$result->uid = $rs->uid;
	$result->courseName = $rs->courseName;
	$result->moduleName = $rs->moduleName;

	$result->info = mydb::clearprop($rs);

	mydb::where('tr.`qtref` = :surveyId', ':surveyId', $surveyId);
	$stmt = 'SELECT tr.*
		FROM %qttran% tr
			-- LEFT JOIN %lms_mod% m USING(`modid`)
		%WHERE%;
		-- {key: "part"}
		';

	$result->trans = mydb::select($stmt)->items;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>