<?php
/**
* LMS :: Get Course Member
* Created 2020-07-03
* Modify  2020-07-03
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_lms_class_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['classId' => $conditions];
	}

	$classId = $conditions->classId;

	if ($debug) debugMsg($conditions,'$conditions');


	if ($classId == '*') {
		mydb::value('$LIMIT$', '');
	} else if ($classId) {
		mydb::where('t.`classid` = :classId', ':classId', $classId);
		mydb::value('$LIMIT$', 'LIMIT 1');
	} else {
		return false;
	}

	$stmt = 'SELECT
		t.*
		, c.`name` `courseName`
		, m.`name` `moduleName`, m.`enname` `enModuleName`
		FROM %lms_timetable% t
			LEFT JOIN %lms_course% c ON c.`courseid` = t.`courseid`
			LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
		%WHERE%
		$LIMIT$';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	if ($classId == '*') {
		$result->count = $rs->count();
		$result->trans = $rs->items;
	} else {
		$result->classId = $rs->classid;
		$result->name = $rs->title;
		$result->courseId = $rs->courseid;
		$result->moduleId = $rs->modid;

		$result->info = mydb::clearprop($rs);
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>