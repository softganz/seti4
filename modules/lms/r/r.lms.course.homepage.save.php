<?php
/**
* LMS :: Get Course Homepage Save
* Created 2020-08-06
* Modify  2020-08-06
*
* @param Int $courseId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_lms_course_homepage_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$homepageInfo = R::Model('lms.course.homepage.get', $data->courseId);

	$data->bigid = SG\getFirst($homepageInfo->id);
	$data->keyname = 'lms.course.homepage';
	$data->fldtype = 'string';
	$data->created = $data->modified = date('U');
	$data->ucreated = $data->umodified = i()->uid;

	$stmt = 'INSERT INTO %bigdata% 
		(`bigid`, `keyname`, `keyid`, `fldtype`, `flddata`, `created`, `ucreated`)
		VALUES
		(:bigid, :keyname, :courseId, :fldtype, :html, :created, :ucreated)
		ON DUPLICATE KEY UPDATE
		`flddata` = :html
		, `modified` = :modified
		, `umodified` = :umodified
		';

	$rs = mydb::query($stmt, $data);

	$result->process[] = mydb()->_query;
	
	if ($debug) debugMsg(mydb()->_query);

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>