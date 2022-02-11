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

function r_lms_student_get($conditions, $options = '{}') {
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

	$stmt = 'SELECT
		s.*
		, u.`username`
		, IF(s.`email` IS NULL OR s.`email`= "", u.`email`, s.`email`) `email`
		, IF(s.`phone` IS NULL OR s.`phone`= "", u.`phone`, s.`phone`) `phone`
		, u.`email` `userEmail`, u.`phone` `userPhone`
		, SUBSTR(s.`areacode`, 7,2) `village`
		, cos.`subdistname` `tambonName`, cod.`distname` `ampurName`, cop.`provname` `changwatName`
		, f.`fid` `photoId`
		, f.`file` `photo`
		FROM %lms_student% s
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %topic_files% f ON f.`tagname` = "lms,profile" AND f.`refid` = s.`sid`
			LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(s.`areacode`,6)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(s.`areacode`,4)
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(s.`areacode`,2)
   	%WHERE%
		$LIMIT$';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	if ($studentId == '*') {
		$result->count = $rs->count();
		$result->trans = $rs->items;
	} else {
		$result->studentId = $rs->sid;
		$result->courseId = $rs->courseid;
		$result->uid = $rs->uid;
		$result->name = $rs->prename.''.$rs->name.' '.$rs->lname;
		$result->status = $rs->status;

		$result->info = mydb::clearprop($rs);
		$result->info->address = SG\implode_address($rs, 'short');
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>