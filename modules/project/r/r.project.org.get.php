<?php
/**
* Get Organization
* @param Mixed $conditions
* @param String $options
* @return Object or Array Object
*/
function r_project_org_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "o.`orgid` ASC", limit: 1, start: -1}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;


	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else if (is_numeric($conditions)) {
		$orgid = $conditions;
		$conditions = (Object) ['orgid' => $orgid];
	}

	//debugMsg($conditions,'$conditions');

	$result = (Object) [];

	if ($conditions->orgid) {
		mydb::where('o.`orgid` = :orgid', ':orgid', $orgid);
	}

	if ($options->limit == 1) {
		mydb::value('$LIMIT','LIMIT 1');
		mydb::value('$ORDER','');
	} else if ($options->limit == '*') {
		mydb::value('$LIMIT','');
		mydb::value('$ORDER', 'ORDER BY '.$options->order);
	} else {
		mydb::value('$LIMIT','LIMIT '.$options->limit);
		mydb::value('$ORDER', 'ORDER BY '.$options->order);
	}

	$stmt = 'SELECT
		o.*
		, po.`name` `parentName`
		FROM %db_org% o
			LEFT JOIN %db_org% po ON po.`orgid` = o.`parent`
		%WHERE%
		$ORDER
		$LIMIT
		;';

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($options->limit == 1) {
		$info = end($dbs->items);
		$result->orgId = $info->orgid;
		$result->orgid = $info->orgid;
		$result->name = $info->name;
		$result->RIGHT = $info->RIGHT;
		$result->info = NULL;
		$result->officers = [];

		$info->isAdmin = is_admin('org');
		$info->isOrgAdmin = false;
		$info->isOfficer = false;
		$info->isOwner = false;
		$info->isEdit = false;
		$info->isTrainer = false;

		$stmt = 'SELECT o.* FROM %org_officer% o LEFT JOIN %users% u USING(`uid`) WHERE o.`orgid` = :orgid AND u.`status` = "enable"';
		$dbs = mydb::select($stmt, ':orgid', $info->orgid);
		foreach ($dbs->items as $item) {
			$result->officers[$item->uid] = strtoupper($item->membership);
		}

		if (i()->ok) {
			$info->isOfficer = array_key_exists(i()->uid, $result->officers) && in_array($result->officers[i()->uid],['ADMIN','OFFICER']);
			$info->isOrgAdmin = $info->isOfficer && $result->officers[i()->uid]=='ADMIN';
			$info->isOwner = i()->uid == $info->uid || ($info->isOfficer && in_array($result->officers[i()->uid],array('ADMIN','OFFICER')));
			$info->isEdit = $info->isAdmin || $info->isOwner;
			$info->isTrainer = $result->officers[i()->uid] == 'TRAINER';
		}
		$result->info = $info;
	} else {
		$result = $dbs->items;
	}

	return $result;
}
?>