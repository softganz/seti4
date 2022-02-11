<?php
/**
* Organization Get
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

import('model:org.php');

class ImedGroupModel {
	public static function get($conditions = NULL, $options = '{}') {
		$defaults = '{debug: false, data: "*", resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object)$conditions;
		else {
			$conditions = (Object) ['orgId' => $conditions];
		}

		$orgId = $conditions->orgId;

		if (empty($conditions->orgId)) return NULL;

		$result = NULL;

		$stmt = 'SELECT * FROM %imed_socialgroup% WHERE `orgId` = :orgId AND `status` > 0 LIMIT 1';
		$isServ = mydb::select($stmt, ':orgId',$orgId)->orgid;

		if ($debug) debugMsg(mydb()->_query);

		if (!$isServ) return $result;

		$result = OrgModel::get($conditions, $options);

		$result->is->socialtype = false;

		$stmt = 'SELECT * FROM %imed_socialmember% WHERE `orgId` = :orgId; -- {key: "uid"}';
		$members = mydb::select($stmt, ':orgId', $orgId)->items;


		$result->is->socialtype = array_key_exists(i()->uid, $members) ? $members[i()->uid]->membership : false;

		$result->is->admin = is_admin('imed') || $result->is->admin || $result->is->socialtype === 'ADMIN';

		if ($result->is->admin) $result->RIGHT = $result->RIGHT | _IS_ADMIN;
		$result->RIGHTBIN = decbin($result->RIGHT);

		if ($options->data == 'info') return $result;

		$result->members = $members;

		return $result;
	}
}
?>