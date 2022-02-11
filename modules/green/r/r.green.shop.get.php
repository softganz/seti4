<?php
/**
* Green :: Get Shop/Group Information
*
* @param Object $conditaion
* @param Object $options
* @return Object
*/

$debug = true;

function r_green_shop_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "CONVERT(o.`name` USING tis620) ASC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$isAdmin = is_admin('green');

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else if (is_string($conditions) && preg_match('/^\{/', $conditions)) $conditions = SG\json_decode($conditions);
	else {
		$conditions = (Object) ['shopid' => $conditions];
	}

	//mydb::where('of.`membership` = :membership', ':membership', 'SHOPOWNER');
	if ($conditions->shopId == 'my') {
		//debugMsg('$_SESSION[shopid]=',$_SESSION['shopid']);
		$conditions->shopId = $_SESSION['shopid'];
		mydb::where('o.`orgid` = :shopid', ':shopid', $conditions->shopId);
		if (!$isAdmin) mydb::where('of.`uid` = :uid', ':uid', i()->uid);
		$options->limit = 1;
	} else if ($conditions->shopId) {
		mydb::where('o.`orgid` = :shopid', ':shopid', $conditions->shopId);
	} else if ($conditions->my == '*') {
		mydb::where('of.`uid` = :uid', ':uid', i()->uid);
	} else if ($conditions->user) {
		mydb::where('of.`uid` = :uid', ':uid', $conditions->user);
	} else if ($conditions->my) {

	}

	if ($conditions->search && $conditions->search != '*') mydb::where('o.`name` LIKE :search', ':search', '%'.$conditions->search.'%');

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

	mydb::value('$STANDARD$', mydb::table_exists('ibuy_farmland') ? ', (SELECT COUNT(*) FROM %ibuy_farmland% WHERE `orgid` = s.`shopid` AND `approved` IN ("Approve", "ApproveWithCondition")) `standard`' : '', false);

	$stmt = 'SELECT
		s.*
		, o.`name`, o.`phone`, o.`fax`, o.`areacode`
		, "https://communeinfo.com/themes/default/logo-green.png" `logo`
		, o.`house`
		, CAST(SUBSTR(o.`areacode`,7,2) AS UNSIGNED) `village`
		, cosub.`subdistname` `tambonName`
		, codist.`distname` `ampurName`
		, copv.`provname` `changwatName`
		$STANDARD$
		FROM %ibuy_shop% s
			LEFT JOIN %db_org% o ON o.`orgid` = s.`shopid`
			LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(o.`areacode`,2)
			LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(o.`areacode`,4)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(o.`areacode`,6)
			LEFT JOIN %co_village% covi ON covi.`villid` = o.`areacode`
			LEFT JOIN %org_officer% of USING (`orgid`)
		%WHERE%
		GROUP BY `orgid`
		$ORDER$
		$LIMIT$;'
		.$queryOption;

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

	if ($options->limit == 1 && $dbs->count() == 1) {
		$result->orgId = $dbs->shopid;
		$result->shopId = $dbs->shopid;
		$result->name = $dbs->name;
		$result->uid = $dbs->uid;
		$result->RIGHT = NULL;
		$result->RIGHTBIN = NULL;
		$result->info = NULL;
		$result->officers = NULL;

		if ($options->setShop) {
			$_SESSION['shopid'] = $result->shopId;
		}

		$info = R::Model('org.get', $result->shopId)->info;
		$info->logo = 'https://communeinfo.com/themes/default/logo-green.png';

		$isAdmin = is_admin('ibuy');
		$result->officers = array();

		$result->info = $info;

		$stmt = 'SELECT o.* FROM %org_officer% o LEFT JOIN %users% u USING(`uid`) WHERE o.`orgid` = :shopid AND u.`status` = "enable"';
		$dbs = mydb::select($stmt, ':shopid', $result->shopId);
		foreach ($dbs->items as $item) {
			$result->officers[$item->uid] = strtoupper($item->membership);
		}

		$membership = i()->ok && array_key_exists(i()->uid, $result->officers) ? $result->officers[i()->uid] : NULL;

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

		$result->is->membership = $membership;
		$result->is->admin = $isAdmin;
		$result->is->orgadmin = $isOrgAdmin;
		$result->is->owner = $isOwner;
		$result->is->officer = $isOfficer;
		$result->is->editable = $isEditable;


		mydb::clearprop($result->info);


		$result->photo = NULL;

		$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `type` = "photo" AND `cid` = 0';
		$result->photo = mydb::select($stmt, ':tpid', $result->tpid)->items;
		foreach ($result->photo as $key => $rs) {
			$photo = model::get_photo_property($rs->file);
			$result->photo[$key]->prop = $photo;
		}

	} else {
		$result = $dbs->items;
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>