<?php
function r_org_docs_get($conditions=NULL, $options='{}') {
	$defaults='{debug: false, resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions=(object)$conditions;
	else {
		$conditions = (Object) ['docId' => $conditions];
	}

	$result = NULL;

	mydb::where('d.`docid` IN (:docid)', ':docid', 'SET:'.$conditions->docId);

	$stmt='SELECT * FROM %org_doc% d %WHERE%; -- {key:"orgid"}';
	$dbs=mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($dbs->_empty) return null;

	$dbs=mydb::clearprop($dbs);

	if ($options->resultType == 'record') {
		$result = reset($dbs->items);
		$result->isAdmin=is_admin('org');
		$result->isOfficer=false;
		$result->isOrgAdmin=false;
		$result->isOwner=false;
		$result->isEdit=false;
		$result->officers=array();

		foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid`=:orgid',':orgid',$result->orgid)->items as $item) {
			$result->officers[$item->uid]=$item->membership;
		}

		if (i()->ok) {
			$result->isOfficer=array_key_exists(i()->uid, $result->officers);
			$result->isOrgAdmin=$result->isOfficer && $result->officers[i()->uid]=='admin';
			$result->isOwner=i()->uid==$result->uid || ($result->isOfficer && in_array($result->officers[i()->uid],array('admin','officer')));
			$result->isEdit=$result->isAdmin || $result->isOwner;
		}
	} else {
		$result = $dbs->items;
	}

	return $result;
}
?>