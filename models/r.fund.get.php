<?php
/**
* Get Local Fund Information
* Created 2018-12-05
* Modify  2020-04-10
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_fund_get($fundid, $orgid = NULL, $options = '{}') {
	$stmt = 'SELECT
		  o.`orgid`, o.`name`, o.`uid`
		, f.*
		, t.`name` `orgSizeName`
		, o.`location`
		FROM %project_fund% f
			LEFT JOIN %db_org% o ON o.`shortname` = f.`fundid`
			LEFT JOIN %tag% t ON t.`taggroup` = "project:orgsize" AND t.`catid` = f.`orgsize`
		WHERE '.($fundid?'f.`fundid` = :fundid' : 'o.`orgid` = :orgid').'
		LIMIT 1';

	$rs = mydb::select($stmt,':fundid',$fundid,':orgid',$orgid);

	if ($rs->_empty) return null;

	$rs = mydb::clearprop($rs);

	$rs->hasInitAccount = $rs->openbaldate && $rs->accbank && $rs->accname && $rs->accno;

	$stmt = 'SELECT CONCAT(`prename`,`name`) `name`
		FROM %org_board%
		WHERE `orgid` = :orgid AND `boardposition` = 2 AND `status` = 1
		LIMIT 1';

	$board = mydb::select($stmt,':orgid',$orgid);

	$rs->chaimanName = $board->name;
	
	$rs->isAdmin = is_admin('project');
	$rs->isFundAdmin = false;
	$rs->isOfficer = false;
	$rs->isOwner = false;
	$rs->isEdit = false;
	$rs->isTrainer = false;
	$rs->officers = array();

	foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgid',':orgid', $rs->orgid)->items as $item) {
		$rs->officers[$item->uid] = strtoupper($item->membership);
	}

	if (i()->ok) {
		$rs->isOfficer = array_key_exists(i()->uid, $rs->officers) && in_array($rs->officers[i()->uid],array('ADMIN','OFFICER'));
		$rs->isFundAdmin = $rs->isOfficer && $rs->officers[i()->uid]=='ADMIN';
		$rs->isOwner = i()->uid == $rs->uid || ($rs->isOfficer && in_array($rs->officers[i()->uid],array('ADMIN','OFFICER')));
		$rs->isEdit = $rs->isAdmin || $rs->isOwner;
		$rs->isTrainer = $rs->officers[i()->uid] == 'TRAINER';
	}
	return $rs;
}
?>