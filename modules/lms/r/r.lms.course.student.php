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

function r_lms_course_student($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['studentId' => $conditions];
	}

	$studentId = $conditions->studentId;

	if ($debug) debugMsg($conditions,'$conditions');


	if ($studentId == '*') {
		mydb::value('$LIMIT$', '');
	} else if ($studentId) {
		mydb::where('s.`sid` = :sid', ':sid', $studentId);
		mydb::value('$LIMIT$', 'LIMIT 1');
	} else if ($conditions->uid) {
		mydb::where('s.`uid` = :uid', ':uid', $conditions->uid);
		mydb::value('$LIMIT$', 'LIMIT 1');
	} else {
		return false;
	}

	$stmt = 'SELECT s.*, u.`username`, u.`email` `userEmail`, u.`phone` `userPhone`
		FROM %lms_student% s
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		$LIMIT$';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	if ($studentId == '*') {
		$result->count = $rs->count();
		$result->trans = $rs->items;
	} else {
		$result->uid = $rs->uid;
		$result->name = $rs->prename.''.$rs->name.' '.$rs->lname;

		$result->info = mydb::clearprop($rs);
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>