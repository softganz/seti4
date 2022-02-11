<?php
function r_school_get($conditions = NULL, $options = '{}') {
	$defaults = '{debug: false, resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['orgId' => $conditions];
	}

	$orgId = $conditions->orgId;

	if (empty($conditions->orgId)) return NULL;

	$result = NULL;
	$result = R::Model('org.get',$orgId);

	$stmt='SELECT
					s.*
					FROM %school% s
					WHERE s.`orgId` = :orgId
					LIMIT 1';
	$rs = mydb::select($stmt, [':orgId' => $orgId]);

	if (!$debug) mydb::clearprop($rs);

	//debugMsg($rs,'$rs');

	foreach ($rs as $key => $value) $result->info->{$key} = $value;

	return $result;





	if ($rs->_empty) return NULL;


	$result->orgid=$rs->orgid;
	$result->name=$rs->name;
	$result->orgid=$rs->orgid;

	$result->RIGHT=NULL;
	$result->RIGHTBIN=NULL;
	$result->is=NULL;


	$right=0;

	//$result->info->areaName=SG\implode_address($result->info);

	$result->is->owner=i()->ok && $result->info->uid==i()->uid;
	$result->is->access=user_access('administer schools','edit own school content',$result->info->uid) || $result->info->isOwner;
	$result->is->admin=user_access('administer schools');

	$result->is->editable=false;
	$result->is->editable=$result->is->admin || $result->is->owner;

	if ($result->is->admin) $right=$right | _IS_ADMIN;
	if ($result->is->owner) $right=$right | _IS_OWNER;
	if ($result->is->access) $right=$right | _IS_ACCESS;
	if ($result->is->editable) $right=$right | _IS_EDITABLE;

	$result->RIGHT=$right;
	$result->RIGHTBIN=str_pad(decbin($right),32,'0',STR_PAD_LEFT);

	return $result;
}
?>