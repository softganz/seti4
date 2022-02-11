<?php
/**
* Project :: Get Next Reference Number
* Created 2018-10-09
* Modify  2020-08-03
*
* @param String $precode
* @param Boolean $update
* @param Int $digit
* @return String
*/

$debug = true;

function r_project_gl_getnextref($precode, $update = false, $digit = 7) {
	/*
	$precodeLen = strlen($precode);

	$stmt = 'SELECT MAX(`refcode`) `maxRefCode`
		FROM %project_gl%
		WHERE LEFT(`refcode`,'.$precodeLen.') = :precode LIMIT 1';

	$rs = mydb::select($stmt,':precode',$precode);

	$maxRefCode = $rs->maxRefCode;
	$no = substr($maxRefCode,$precodeLen);
	$ret = $precode.sprintf('%0'.$digit.'d',$no+1);
	*/


	$stmt = 'SELECT * FROM %variable% WHERE `name` = :name LIMIT 1';

	$rs = mydb::select($stmt, ':name', 'lastno.'.$precode);

	$maxRefCode = $rs->value;
	$nextNo = sprintf('%0'.$digit.'d', intval($maxRefCode) + 1);
	$ret = $precode.sprintf('%0'.$digit.'d',$nextNo);

	if ($update) {
		$stmt = 'INSERT INTO %variable% (`name`, `value`) VALUES (:name, :value) ON DUPLICATE KEY UPDATE `value` = :value';
		mydb::query($stmt, ':name', 'lastno.'.$precode, ':value', $nextNo);
	}

	return $ret;
}
?>