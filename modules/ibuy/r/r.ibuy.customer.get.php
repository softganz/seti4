<?php
/**
* iBuy Get Customer
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_ibuy_customer_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "`custname` ASC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['customerId' => $conditions];
	}

	//mydb::where('of.`membership` = :membership', ':membership', 'SHOPOWNER');
	if ($conditions->customerId) {
		mydb::where('c.`custid` = :customerId', ':customerId', $conditions->customerId);
	}

	$queryOption = '';
	if ($options->limit == 1) {
		mydb::value('$LIMIT$','LIMIT 1');
		mydb::value('$ORDER$','');
	} else if ($options->limit == '*') {
		mydb::value('$LIMIT$','');
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "shopid"}';
	} else {
		mydb::value('$LIMIT$','LIMIT '.$options->limit);
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		$queryOption = _NL.'	-- {key: "shopid"}';
	}

	$stmt = 'SELECT
		c.*
		, CONCAT(X(c.`location`),",",Y(c.`location`)) `location`
		, X(c.`location`) `lat`
		, Y(c.`location`) `lnt`
		, CAST(SUBSTR(c.`areacode`,7,2) AS UNSIGNED) `village`
		, cosub.`subdistname` `tambonName`
		, codist.`distname` `ampurName`
		, copv.`provname` `changwatName`
		FROM %ibuy_customer% c
			LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(c.`areacode`,2)
			LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(c.`areacode`,4)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(c.`areacode`,6)
			LEFT JOIN %co_village% covi ON covi.`villid` = c.`areacode`
		%WHERE%
		$ORDER$
		$LIMIT$;'
		.$queryOption;

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($options->limit == 1 && $dbs->count() == 1) {
		$result->custid = $dbs->custid;
		$result->name = $dbs->custname;
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