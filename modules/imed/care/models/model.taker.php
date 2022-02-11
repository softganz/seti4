<?php
/**
* iMed Care :: Taker Model
* Created 2021-08-26
* Modify  2021-08-26
*
*
* @usage import('package:imed/care/models/model.taker')
* @usage new TakerModel([])
*/

$debug = true;

class TakerModel {
	// public static function get($keyId) {
	// 	mydb::where('r.`keyId` = :keyId', ':keyId', $keyId);

	// 	$result = mydb::select('SELECT
	// 		r.*
	// 		, cs.`name` `serviceName`
	// 		, tu.`username` `takerUsername`
	// 		, tu.`name` `takerName`
	// 		, gu.`username` `giverUsername`
	// 		, gu.`name` `giverName`
	// 		, CONCAT(p.`name`," ",p.`lname`) `patientName`
	// 		FROM %imed_request% r
	// 			LEFT JOIN %imed_code_serv% cs ON cs.`servId` = r.`servId`
	// 			LEFT JOIN %users% tu ON tu.`uid` = r.`takerId`
	// 			LEFT JOIN %users% gu ON gu.`uid` = r.`giverId`
	// 			LEFT JOIN %db_person% p ON p.`psnid` = r.`psnid`
	// 		%WHERE%
	// 		LIMIT 1'
	// 	);

	// 	// debugMsg(mydb()->_query);

	// 	$result = $result->reqId ? mydb::clearprop($result) : NULL;

	// 	if ($result) {
	// 		$result->is->admin = is_admin('imed care');
	// 		$result->is->taker = i()->ok && i()->uid == $result->takerId;
	// 		$result->is->giver = i()->ok && i()->uid == $result->giverId;
	// 		$result->is->access = $result->is->admin || $result->is->taker || $result->is->giver;
	// 	}

	// 	return $result;
	// }

	public static function giverList($args = [], $options = []) {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($args['userId']) mydb::where('r.`takerId` = :userId AND r.`giverId` IS NOT NULL', ':userId', $args['userId']);

		$result = mydb::select('SELECT
			r.`giverId`, u.`username`, u.`name`
			, COUNT(*) `times`
			, FROM_UNIXTIME(MIN(`created`), "%Y-%m-%d %H:%i:%s") `firstRequestDate`
			, FROM_UNIXTIME(MAX(`created`), "%Y-%m-%d %H:%i:%s") `lastRequestDate`
			FROM %imed_request% r
				LEFT JOIN %users% u ON u.`uid` = r.`giverId`
			%WHERE%
			GROUP BY r.`giverId`
			ORDER BY u.`name` DESC'
		)->items;

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}
}
?>