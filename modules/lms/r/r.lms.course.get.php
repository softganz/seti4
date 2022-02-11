<?php
/**
* LMS :: Get Course Information
* Created 2020-07-02
* Modify  2020-07-02
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_lms_course_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['courseId' => $conditions];
	}

	$courseId = $conditions->courseId;

	mydb::where('`courseid` = :courseid', ':courseid', $courseId);

	$stmt = 'SELECT * FROM %lms_course% %WHERE% LIMIT 1';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	$result->courseId = $rs->courseid;
	$result->name = $rs->name;

	$result->info = mydb::clearprop($rs);

	mydb::where('c.`courseid` = :courseId', ':courseId', $courseId);
	$stmt = 'SELECT c.`courseid`, m.*, c.`frmid`
		FROM %lms_cmod% c
			LEFT JOIN %lms_mod% m USING(`modid`)
		%WHERE%;
		-- {key: "modid"}';

	$result->module = mydb::select($stmt)->items;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>