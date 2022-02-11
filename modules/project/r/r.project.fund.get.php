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

function r_project_fund_get($orgid) {
	$result = NULL;

	$stmt = 'SELECT
		  o.`orgid`, o.`name`, o.`uid`
		, f.*
		, o.`areacode`
		, o.`house`
		, o.`village`
		, o.`tambon`
		, o.`changwat`
		, o.`ampur`
		, cop.`provname` `changwatName`
		, cod.`distname` `ampurName`
		, cos.`subdistname` `tambonName`
		, t.`name` `orgSizeName`
		, o.`location`
		FROM %project_fund% f
			LEFT JOIN %db_org% o ON o.`shortname` = f.`fundid`
			LEFT JOIN %tag% t ON t.`taggroup` = "project:orgsize" AND t.`catid` = f.`orgsize`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(o.`areacode`,4)
			LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(o.`areacode`,6)
		WHERE o.`orgid` = :orgid
		LIMIT 1';

	$rs = mydb::select($stmt, ':orgid', $orgid);

	if ($rs->_empty) return null;

	mydb::clearprop($rs);

	$result = new stdClass();

	$result->orgId = $rs->orgid;
	$result->fundId = $rs->fundid;
	$result->areaId = $rs->areaid;
	$result->orgid = $rs->orgid;
	$result->fundid = $rs->fundid;
	$result->uid = $rs->uid;
	$result->name = $rs->name;
	$result->RIGHT = NULL;
	$result->RIGHTBIN = NULL;
	$result->hasInitAccount = false;
	$result->finclosemonth = $rs->finclosemonth;

	$result->info = $rs;
	$result->info->address = SG\implode_address($result->info);

	/*
	$result = mydb::clearprop($rs);
	$result->RIGHT = NULL;
	$result->RIGHTBIN = NULL;
	$result->info = sg_clone($rs);
	*/

	$result->is = (Object) [];
	$result->right = (Object) [];
	$result->officers = [];


	$result->hasInitAccount = $rs->openbaldate && $rs->accbank && $rs->accname && $rs->accno;


	$stmt = 'SELECT CONCAT(`prename`,`name`) `name`
		FROM %org_board%
		WHERE `orgid` = :orgid AND `boardposition` = 2 AND `status` = 1
		LIMIT 1';

	$board = mydb::select($stmt,':orgid',$orgid);

	$result->info->chaimanName = $board->name;

	$result->is->admin = $result->isAdmin = is_admin();
	$result->is->projectAdmin = $result->isProjectAdmin = is_admin('project');
	$result->is->fundAdmin = $result->isFundAdmin = false;
	$result->is->officer = $result->isOfficer = false;
	$result->is->owner = $result->isOwner = false;
	$result->is->edit = $result->isEdit = false;
	$result->is->trainer = $result->isTrainer = false;
	$result->officers = array();

	foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgid',':orgid', $result->orgid)->items as $item) {
		$result->officers[$item->uid] = strtoupper($item->membership);
	}

	if (i()->ok) {
		$result->is->membership = array_key_exists(i()->uid, $result->officers) ? $result->officers[i()->uid] : NULL;

		$result->is->officer = $result->isOfficer = $result->is->membership
			&& in_array($result->officers[i()->uid],array('ADMIN','OFFICER'));

		$result->is->fundAdmin = $result->isFundAdmin = $result->is->membership
			&& $result->officers[i()->uid] == 'ADMIN';

		$result->is->owner = $result->isOwner = i()->uid == $result->uid || $result->is->officer;

		$result->is->trainer = $result->isTrainer = $result->is->membership && $result->officers[i()->uid] == 'TRAINER';

		$result->is->edit = $result->isEdit = $result->isAdmin || $result->is->owner || is_admin('project');
	}

	$result->right = R::Model('project.right.fund', $result);

	return $result;
}
?>