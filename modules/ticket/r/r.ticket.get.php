<?php
/**
* iBuy Get Customer Ticket
*
* @param Object $data
* @return Object $options
*/

$debug = true;

function r_ticket_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "`tickid` DESC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	if (is_string($conditions) && substr($conditions, 0, 1) == '{') {
		$conditions = sg_json_decode($conditions);
	}

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['ticketId' => $conditions];
	}

	if (!$options->includeThread) mydb::where('tk.`thread` IS NULL');

	if ($conditions->ticketId) mydb::where('tk.`tickid` = :ticketId', ':ticketId', $conditions->ticketId);

	if ($conditions->status) mydb::where('tk.`status` IN ( :status )', ':status', 'SET-STRING:'.$conditions->status);

	$queryOption = '';
	if ($options->limit == 1) {
		mydb::value('$LIMIT$','LIMIT 1');
		mydb::value('$ORDER$','');
	} else if ($options->limit == '*') {
		mydb::value('$LIMIT$','');
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "tickid"}';
	} else {
		mydb::value('$LIMIT$','LIMIT '.$options->limit);
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "tickid"}';
	}

	$stmt = 'SELECT
		tk.*
		, u.`name` `posterName`
		, (SELECT MAX(`created`) FROM %ticket% WHERE `thread` = tk.`tickid`) `lastAction`
		, (SELECT GROUP_CONCAT(`fid`,"|",`file`) FROM %topic_files% WHERE `tagname` = "ticket" AND `refid` = tk.`tickid`) `photos`
		FROM %ticket% tk
			LEFT JOIN %users% u ON u.`uid` = tk.`uid`
		%WHERE%
		$ORDER$
		$LIMIT$;'
		.$queryOption;

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($options->limit == 1 && $dbs->count() == 1) {
		$result->ticketId = $dbs->tickid;
		$result->customerId = $dbs->custid;
		$result->problem = $dbs->problem;
		$result->uid = $dbs->uid;
		$result->RIGHT = NULL;
		$result->RIGHTBIN = NULL;
		$result->info = mydb::clearprop($dbs);
		$result->officers = NULL;

		$stmt = 'SELECT
			*
			, (SELECT GROUP_CONCAT(`fid`,"|",`file`) FROM %topic_files% WHERE `tagname` = "ticket" AND `refid` = tk.`tickid`) `photos`
			FROM %ticket% tk
			WHERE `thread` = :thread
			ORDER BY `tickid` DESC';
		$result->thread = mydb::select($stmt, ':thread', $result->ticketId)->items;



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