<?php
/**
* Export Need To Rehab Server
* Created 2019-08-22
* Modify  2019-08-22
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_rehab_export2rehab_need($self) {
	$ret = '';

	$stmt = 'SELECT
`needid`,`needtype`,`urgency`,`status`,`detail`
, p.`cid`, p.`name`, p.`lname`
, FROM_UNIXTIME(n.`created`) `created`
FROM `sgz_imed_need` n
LEFT JOIN `sgz_db_person` p USING(`psnid`)
WHERE `changwat` = "90" AND `cid` IS NOT NULL AND LEFT(`cid`,1)!="?" AND `cid`!=""
';

	$dbs = mydb::select($stmt);

	$ret .= mydb::printtable($dbs);

	return $ret;
}
?>

