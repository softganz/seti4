<?php
/**
* iBuy Get Customer Serial No.
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_ibuy_serial_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "`issnid` ASC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (Object) $conditions;
	else {
		$conditions = (Object) ['serialId' => $conditions];
	}

	if ($conditions->serialId) mydb::where('s.`issnid` = :serialId', ':serialId', $conditions->serialId);

	if ($conditions->customerId) mydb::where('s.`custid` = :customerId', ':customerId', $conditions->customerId);

	$queryOption = '';
	if ($options->limit == 1) {
		mydb::value('$LIMIT$','LIMIT 1');
		mydb::value('$ORDER$','');
	} else if ($options->limit == '*') {
		mydb::value('$LIMIT$','');
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "issnid"}';
	} else {
		mydb::value('$LIMIT$','LIMIT '.$options->limit);
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "issnid"}';
	}


	$stmt = 'SELECT
		s.`issnid` `serialId`
		, IFNULL(t.`title`, s.`stkdesc`) `productName`
		, s.*
		FROM %ibuy_serial% s
			LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		$ORDER$
		$LIMIT$;'
		.$queryOption;

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($options->limit == 1 && $dbs->count() == 1) {
		$result->serialId = $dbs->serialId;
		$result->productName = $dbs->productName;
		$result->uid = $dbs->uid;
		$result->RIGHT = NULL;
		$result->RIGHTBIN = NULL;
		$result->info = mydb::clearprop($dbs);
		$result->officers = NULL;

		$isAdmin = is_admin('ibuy');


		if (i()->ok) {
			$isOfficer = $membership === 'OFFICER';
			$isOrgAdmin = $isAdmin || in_array($membership,array('ADMIN', 'SHOPOWNER', 'MANAGER'));
			$isOwner = i()->uid == $result->uid
				|| ($isOfficer && in_array($result->officers[i()->uid],array('ADMIN','OFFICER')));
			$isEditable = $isAdmin || $isOrgAdmin || $isOwner;
		}

		if ($isOrgAdmin) $right = $right | _IS_ADMIN;
		if ($isOwner) $right = $right | _IS_OWNER;
		if ($isOfficer) $right = $right | _IS_OFFICER;
		if ($isEditable) $right = $right | _IS_EDITABLE;

		$result->RIGHT = $right;
		$result->RIGHTBIN = decbin($right);

		$result->is->admin = $isAdmin;
		$result->is->orgadmin = $isOrgAdmin;
		$result->is->owner = $isOwner;
		$result->is->editable = $isEditable;

	} else {
		$result = $dbs->items;
	}


	return $result;
}
?>