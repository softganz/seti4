<?php
/**
* Project :: Model Get My Fund
* Created 2020-06-10
* Modify  2020-06-10
*
* @param Int $uid
* @return Object Data Set
*/

$debug = true;

function r_project_fund_get_my($uid, $memberShip = 'ADMIN,OFFICER') {
	mydb::where('of.`uid` = :uid', ':uid', $uid);
	if ($memberShip) mydb::where('of.`membership` IN ( :membership )', ':membership', 'SET-STRING:'.$memberShip);

	$stmt = 'SELECT of.`orgid`, o.`shortname` `fundid`
		, of.`membership`, o.`name`
		, f.`nameampur`, f.`namechangwat`, f.`areaid`, f.`namearea`
		FROM %org_officer% of
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f ON f.`fundid`=o.`shortname`
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);

	//debugMsg($rs,'$rs');

	if ($rs->_empty) return null;

	$rs = mydb::clearprop($rs);

	return $rs;
}
?>