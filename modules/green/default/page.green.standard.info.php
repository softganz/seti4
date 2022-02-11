<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function green_standard_info($self, $landId) {
	$isAdmin = user_access('administer ibuys');

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>มาตรฐาน</h3></header>';

	$stmt = 'SELECT
		*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(`location`),",",Y(`location`)) `latlng`
		FROM %ibuy_farmland%
			LEFT JOIN %users% u USING(`uid`)
		WHERE `landid` = :landid
		LIMIT 1';

	$landInfo = mydb::select($stmt, ':landid', $landId);

	$ret = $landInfo->landname.' มาตรฐาน '.$landInfo->standard.' สถานะ '.$landInfo->approved;

	//$ret .= print_o($landInfo, '$landInfo');

	return $ret;
}
?>