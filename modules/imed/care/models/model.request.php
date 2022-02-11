<?php
/**
* iMed Care :: Request Model
* Created 2021-08-02
* Modify  2021-08-02
*
*
* @usage import('package:imed/care/models/model.request')
* @usage new RequestModel([])
*/

$debug = true;

class RequestModel {
	public static function get($keyId) {
		mydb::where('r.`keyId` = :keyId', ':keyId', $keyId);

		$result = (Object) [
			'reqId' => NULL,
			'keyId' => NULL,
			'psnId' => NULL,
			'carePlanId' => NULL,
			'takerId' => NULL,
			'giverId' => NULL,
			'serviceName' => NULL,
			'done' => false,
			'closed' => false,
			'info' => NULL,
			'is' => (Object) [],
		];

		$result->info = mydb::select('SELECT
			r.*
			, cp.`cpid` `carePlanId`
			, cs.`name` `serviceName`
			, tu.`username` `takerUsername`
			, tu.`name` `takerName`
			, gu.`username` `giverUsername`
			, gu.`name` `giverName`
			, CONCAT(gu.`real_name`, " ", gu.`last_name`) `giverRealName`
			, CONCAT(p.`name`," ",p.`lname`) `patientName`
			FROM %imed_request% r
				LEFT JOIN %imed_code_serv% cs ON cs.`servId` = r.`servId`
				LEFT JOIN %imed_careplan% cp ON cp.`reqId` = r.`reqId`
				LEFT JOIN %users% tu ON tu.`uid` = r.`takerId`
				LEFT JOIN %users% gu ON gu.`uid` = r.`giverId`
				LEFT JOIN %db_person% p ON p.`psnid` = r.`psnid`
			%WHERE%
			LIMIT 1'
		);

		// debugMsg(mydb()->_query);

		if (!$result->info->reqId) return NULL;

		$result->info = mydb::clearprop($result->info);

		$result->reqId = $result->info->reqId;
		$result->keyId = $result->info->keyId;
		$result->psnId = $result->info->psnId;
		$result->carePlanId = $result->info->carePlanId;
		$result->giverId = $result->info->giverId;
		$result->takerId = $result->info->takerId;
		$result->done = $result->info->done == 'YES';
		$result->closed = $result->info->closed == 'YES';
		$result->serviceName = $result->info->serviceName;

		$result->is->admin = is_admin('imed care');
		$result->is->taker = i()->ok && i()->uid == $result->takerId;
		$result->is->giver = i()->ok && i()->uid == $result->giverId;
		$result->is->access = $result->is->admin || $result->is->taker || $result->is->giver;

		return $result;
	}

	public static function items($args = [], $options = []) {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		if ($args['takerId']) mydb::where('r.`takerId` = :takerId', ':takerId', $args['takerId']);
		if ($args['giverId']) mydb::where('r.`giverId` = :giverId', ':giverId', $args['giverId']);
		if ($args['closed']) mydb::where('(r.`done` = "YES" OR r.`closed` = "YES")');
		else if ($args['waiting']) mydb::where('r.`done` IS NULL');

		$result = mydb::select('SELECT
			r.*
			, cs.`name` `serviceName`
			, tu.`username` `takerUsername`
			, tu.`name` `takerName`
			, gu.`username` `giverUsername`
			, gu.`name` `giverName`
			, CONCAT(gu.`real_name`, " ", gu.`last_name`) `giverRealName`
			, CONCAT(p.`name`," ",p.`lname`) `patientName`
			, COUNT(DISTINCT cptr.`cptrid`) `plan`
			FROM %imed_request% r
				LEFT JOIN %imed_code_serv% cs ON cs.`servId` = r.`servId`
				LEFT JOIN %users% tu ON tu.`uid` = r.`takerId`
				LEFT JOIN %users% gu ON gu.`uid` = r.`giverId`
				LEFT JOIN %db_person% p ON p.`psnid` = r.`psnid`
				LEFT JOIN %imed_careplan% cp ON cp.`reqId` = r.`reqId`
				LEFT JOIN %imed_careplantr% cptr ON cptr.`cpid` = cp.`cpid`
			%WHERE%
			GROUP BY r.`reqId`
			ORDER BY `reqId` DESC'
		)->items;

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	public static function last() {
		return mydb::select(
			'SELECT `keyId` FROM %imed_request% WHERE `takerId` = :takerId ORDER BY `reqId` DESC LIMIT 1',
			':takerId', i()->uid
		)->keyId;
	}
}
?>