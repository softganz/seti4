<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param Array $args
* @return Widget
*
* @usage new NameWidget([])
*/

$debug = true;

class OrgModel {
	/**
	* Create Organization
	*
	* @param Object $data
	* @return Object $options
	*/
	public static function create($data, $options = '{}') {
		$defaults = '{debug: false, createOfficer: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_array($data)) $data = (Object) $data;

		$result = (Object) [
			'orgId' => NULL,
			'_error' => NULL,
			'data' => $data,
			'_query' => [],
		];

		$data->uid = i()->uid;

		if (empty($data->orgId)) {
			$data->shortname = trim($data->shortname);
			if (empty($data->parent)) $data->parent = NULL;
			if (empty($data->name)) $data->name = NULL;
			if (empty($data->shortname)) $data->shortname = NULL;
			if (empty($data->sector)) $data->sector = NULL;
			if (empty($data->phone)) $data->phone = NULL;
			if (empty($data->email)) $data->email = NULL;
			if (empty($data->managername)) $data->managername = NULL;
			if (empty($data->contactname)) $data->contactname = NULL;
			$data->created = date('U');
			$data->areacode = SG\getFirst($data->areacode);

			if ($data->address) {
				$address = SG\explode_address($data->address, $data->areacode);
				$data->house = $address['house'];
				$data->areacode = $address['areaCode'];
				if ($address['zip'] && !$data->zip) $data->zip = $address['zip'];
			} else {
				$data->areacode = NULL;
				$data->house = '';
			}

			if (empty($data->zip)) $data->zip = NULL;

			$stmt = 'INSERT INTO %db_org%
				(
				  `parent`, `uid`, `name`, `shortname`, `sector`
				, `areacode`, `house`, `zipcode`
				, `phone`, `email`
				, `managername`, `contactname`
				, `created`
				)
				VALUES
				(
				  :parent, :uid, :name, :shortname, :sector
				, :areacode, :house, :zip
				, :phone, :email
				, :managername, :contactname
				, :created
			)';

			mydb::query($stmt, $data);

			$result->_query[] = mydb()->_query;

			if (mydb()->_error) {
				$data->orgId = NULL;
				$result->_error = mydb()->_error;
				return $result;
			}
			$data->orgId = mydb()->insert_id;
		}

		$result->orgId = $data->orgId;

		if ($data->orgId && $data->uid && $data->officer) {
			mydb::query(
				'INSERT INTO %org_officer% (`orgId`, `uid`, `membership`) VALUES (:orgId, :uid, :officer)',
				$data
			);
			$result->_query[] = mydb()->_query;
		}


		// debugMsg($data,'$data');
		// debugMsg(mydb()->_query);
		return $result;
	}

	/**
	* Organization Get
	*
	* @param Object $conditions
	* @return Object $options
	*/
	public static function get($conditions = NULL, $options = '{}') {
		$defaults = '{debug: false, initTemplate: false, resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object)$conditions;
		else {
			$conditions = (Object) ['orgId' => $conditions];
		}

		if (empty($conditions->orgId)) return NULL;

		$result = (Object) [
			'orgId' => NULL,
			'orgid' => NULL,
			'name' => NULL,
			'uid' => NULL,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'info' => NULL,
			'is' => (Object) [],
			'officers' => [],
		];

		if ($conditions->orgId && $options->initTemplate) R::Module('org.template', $conditions->orgId);

		mydb::where('o.`orgid` IN (:orgid)', ':orgid', 'SET:'.$conditions->orgId);

		$stmt = 'SELECT
					o.`orgid` `orgId`
					, o.*
					, CAST(SUBSTR(o.`areacode`,7,2) AS UNSIGNED) `village`
					, cosub.`subdistname` `tambonName`
					, codist.`distname` `ampurName`
					, copv.`provname` `changwatName`
					FROM %db_org% o
						LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(o.`areacode`,2)
						LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(o.`areacode`,4)
						LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(o.`areacode`,6)
						LEFT JOIN %co_village% covi ON covi.`villid` = o.`areacode`
					%WHERE%;
					-- {key:"orgid"}';
		$dbs = mydb::select($stmt);

		if ($debug) debugMsg(mydb()->_query);

		if ($dbs->_empty) return NULL;

		$dbs = mydb::clearprop($dbs);

		if ($options->resultType == 'record') {
			$info = reset($dbs->items);
			$info->address = SG\implode_address($info);

			if ($info) {
				$result->orgId = $info->orgId;
				$result->orgid = $info->orgid;
				$result->name = $info->name;
				$result->uid = $info->uid;
				$result->info = $info;

				$right = 0;
				$isAdmin = is_admin('org');
				$isOfficer = false;
				$isOrgAdmin = false;
				$isOwner = false;
				$isEditable = false;

				foreach (mydb::select('SELECT `uid`,UPPER(`membership`) `membership` FROM %org_officer% WHERE `orgid` = :orgid',':orgid',$result->orgId)->items as $item) {
					$result->officers[$item->uid] = $item->membership;
				}

				$membership = i()->ok && array_key_exists(i()->uid, $result->officers) ? $result->officers[i()->uid] : NULL;

				if (i()->ok) {
					$isOfficer = $membership === 'OFFICER';
					$isOrgAdmin = $isAdmin || $membership === 'ADMIN';
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
			}


			if(mydb::table_exists('%org_subject%')) {
				$stmt = 'SELECT s.`subject`, t.`name`
					FROM %org_subject% s
						LEFT JOIN %tag% t ON t.`taggroup` = "subject" AND t.`catid` = s.`subject`
					WHERE s.`orgid` = :orgid';
				foreach(mydb::select($stmt, [':orgid' => $result->orgId])->items as $item) {
					$result->subject[$item->subject] = $item->name;
				}
			}
		} else {
			$result = $dbs->items;
		}

		return $result;
	}

	public static function items($args = [], $options = []) {
		$defaults = '{get: null, order: "name", sort: "ASC", debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($options,'$options');

		$orderList = [
			'name' => 'o.`name`',
			'member' => '`members`',
			'type' => 'j.`type`',
			'issue' => 'j.`issue`',
		];

		$q = preg_replace('/[ ]{2,}/',' ',trim($args['q']));

		if ($args['orgId']) mydb::where('o.`orgid` IN ( :orgId )', ':orgId', 'SET:'.$args['orgId']);
		if ($args['childOf']) mydb::where('o.`parent` = :parent', ':parent', $args['childOf']);
		if ($args['sector']) mydb::where('o.`sector` IN ( :sectorId)', ':sectorId', 'SET:'.$args['sector']);

		if ($args['userId'] === 'member') mydb::where('(o.`uid` = :userId OR of.`uid` = :userId)', ':userId', i()->uid);
		else if ($args['userId'] === 'memberShip') mydb::where('(of.`uid` = :userId)', ':userId', i()->uid);
		else if ($args['userId']) mydb::where('o.`uid` = :userId', ':userId', $args['userId']);

		if (is_numeric($q)) {
			mydb::where('o.`phone` LIKE :phone ', ':phone', '%'.$q.'%');
		} else if ($q) {
			mydb::where('(o.`name` LIKE :name)', ':name', '%'.$q.'%');
		}
		if ($firstchar) mydb::where('p.`name` LIKE :firstchar', ':firstchar', $firstchar.'%');

		$stmt = 'SELECT
			o.`orgid` `orgId`
			, o.*
			, t.`name` `type_name`
			, u.`username`, u.`name` `ownerName`
			FROM %db_org% AS o
				LEFT JOIN %users% u ON u.`uid` = o.`uid`
				LEFT JOIN %tag% AS t ON t.`taggroup`="org:sector" AND o.`sector`=t.`catid`
				LEFT JOIN %org_officer% of ON of.`orgid` = o.`orgid`
			%WHERE%
			GROUP BY `orgId`
			ORDER BY CONVERT ('.$orderList[$options->order].' USING tis620) '.$options->sort;

		$dbs = mydb::select($stmt)->items;

		if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

		return $dbs;
	}

	public static function officerType($orgId, $userId) {
		$officer = NULL;
		if ($orgId && $userId) {
			// Get membership for user of orgid
			$stmt='SELECT `membership`
				FROM %org_officer%
				WHERE `orgid` = :orgid AND `uid` = :uid
				LIMIT 1';

			$orgMemberShip = mydb::select($stmt,':orgid',$orgId, ':uid',$userId)->membership;

			$officer = empty($orgMemberShip) ?false : strtoupper($orgMemberShip);
		}
		return $officer;
	}

	public static function officers($orgId) {
		$officers = (Object) [];

		if (empty($orgId)) return NULL;

		// Get all membership of orgid
		$stmt='SELECT
			of.`orgid` `orgId`
			, of.`uid`
			, UPPER(of.`membership`) `membership`
			, u.`username`
			, u.`name`
			, u.`datein`
			, u.`email`
			, o.`uid` `orgUid`
			FROM %org_officer% of
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_org% o ON o.`orgid` = of.`orgid`
			WHERE of.`orgid` = :orgid;
			';

		$dbs=mydb::select($stmt,':orgid',$orgId);

		if ($dbs->_num_rows) {
			$officers->count = $dbs->_num_rows;
			$officers->items = $dbs->items;
		}
		return $officers;
	}

	// Get all member ship of user
	public static function officerOfUser($userId) {
		$officers = (Object) [];

		$dbs = mydb::select('
			SELECT `orgid`,`membership`
			FROM %org_officer%
			WHERE `uid` = :userId',
			':userId', $userId
		);

		if ($dbs->_num_rows) {
			$officers->count = $dbs->_num_rows;
			foreach ($dbs->items as $rs) {
			 	$officers->items[$rs->orgid] = $rs->membership;
			 }
		}
		return $officers;
	}

	public static function my($uid = NULL) {
		static $myorg = [];

		if (empty($uid)) $uid = i()->uid;
		if (empty($uid)) return;

		if (!isset($myorg[$uid])) {
			$myorg[$uid] = mydb::select('SELECT `orgid` FROM %org_officer% WHERE `uid`=:uid',':uid',$uid)->lists->text;
		}

		// debugMsg($myorg,'$myorg');
		//debugMsg($myorg[$uid]);
		return $myorg[$uid];
	}
}
?>