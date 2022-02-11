<?php
/**
* LMS :: Get Course Homepage Information
* Created 2020-08-06
* Modify  2020-08-06
*
* @param Int $courseId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_lms_course_homepage_get($courseId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$stmt = 'SELECT
		`bigid` `id`
		, `flddata` `html`
		FROM %bigdata%
		WHERE `keyname` = "lms.course.homepage" AND `keyid` = :keyid
		LIMIT 1';

	$rs = mydb::select($stmt, ':keyid', $courseId);

	if ($rs->count()) {
		$result = mydb::clearprop($rs);
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>