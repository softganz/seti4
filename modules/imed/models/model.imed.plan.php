<?php
/**
* iMed :: Plan Model
* Created 2021-08-27
* Modify  2021-08-27
*
* @param Array $args
* @return Object
*
* @usage import('model:model.plan')
* @usage new ImedPlanModel([])
* @usage ImedPlanModel::method()
*/

$debug = true;

class ImedPlanModel {
	public static function get($planId, $options = '{}') {
		$defaults = '{debug:false}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		if (is_array($planId)) {
			$planId = ImedPlanModel::items($planId, '{debug: false, items: 1}')->planId;
		}

		if (!$planId) return NULL;

		$result = (Object) [
			'planId' => NULL,
			'psnId' => NULL,
			'reqId' => NULL,
			'orgId' => NULL,
			'userId' => NULL,
			'patientName' => NULL,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'info' => (Object) [],
			'items' => [],
		];

		mydb::where('pn.`cpid` = :planId',':planId',$planId);
		// if ($seqId) mydb::where('s.`seq` = :seq',':seq',$seqId);

		$stmt = 'SELECT
			  pn.`cpid` `planId`, pn.`psnid` `psnId`, pn.`orgid` `orgId`
			, pn.*
			, u.`username`, u.`name` `ownerName`
			, p.`prename`
			, CONCAT(p.`name`," ",p.`lname`) `patientName`
			FROM %imed_careplan% pn
				LEFT JOIN %users% u ON u.`uid` = pn.`uid`
				LEFT JOIN %db_person% p ON p.`psnid` = pn.`psnid`
			%WHERE%
			LIMIT 1';

		$result->info = mydb::select($stmt);

		if (!$result->info->planId) return NULL;

		if ($debug) debugMsg($result, '$result');

		mydb::clearprop($result->info);

		if ($result->info->planId) {
			$result->planId = $result->info->planId;
			$result->psnId = $result->info->psnId;
			$result->reqId = $result->info->reqId;
			$result->orgId = $result->info->orgId;
			$result->userId = $result->info->uid;
			$result->patientName = $result->info->patientName;

			$right = 0;

			$isOwner = i()->ok && $result->userId == i()->uid;
			$isAdmin = is_admin('imed') || is_admin('imed care');
			$isAccess = false;
			$isEdit = false;
			if ($isAdmin || $isOwner) {
				$isAccess = true;
				$isEdit = true;
			// } else  if ($zones = imed_model::get_user_zone(i()->uid,'imed')) {
			// 	$psnRight = imed_model::in_my_zone($zones,$result->info->changwat,$result->info->ampur,$result->info->tambon);
			// 	if (!$psnRight) {
			// 		$isAccess = false;
			// 		$isEdit = false;
			// 	} else if (in_array($psnRight->right, ['edit','admin'])) {
			// 		$isAccess = true;
			// 		$isEdit = false;
			// 	} else if (in_array($psnRight->right, ['view'])) {
			// 		$isAccess = true;
			// 		$isEdit = false;
			// 	}
			} else {
				$isAccess = false;
				$isEdit = false;
			}


			if ($isAdmin) $right = $right | _IS_ADMIN;
			if ($isOwner) $right = $right | _IS_OWNER;
			if ($isAccess) $right = $right | _IS_ACCESS;
			if ($isEdit) $right = $right | _IS_EDITABLE;

			$result->RIGHT = $right;
			$result->RIGHTBIN = decbin($right);


			$result->items = mydb::select(
				'SELECT
				tr.`cptrid` `tranId`
				, tr.`cpid` `planId`
				, tr.`uid` `userId`
				, tr.`seq` `seqId`
				, tr.`planDate`
				, LEFT(tr.`planTime`,5) `planTime`
				, tr.`careCode`
				, cs.`name` `servName`
				, cs.`icon` `servIcon`
				, cs.`detail` `servDetail`
				, cs.`description` `servDescription`
				, tr.`detail`
				, tr.`created`
				FROM %imed_careplantr% tr
					LEFT JOIN %imed_code_serv% cs ON cs.`servId` = tr.`careCode`
				WHERE `cpid` = :planId
				ORDER BY `plandate` ASC, `plantime` ASC;
				-- {key: "tranId"}',
				':planId', $result->planId
			)->items;
		}

		return $result;
	}

	public static function create($data) {
		if (is_array($data)) $data = (Object) $data;

		$data->psnId = SG\getFirst($data->psnId);
		$data->reqId = SG\getFirst($data->reqId);
		$data->orgId = SG\getFirst($data->orgId);
		$data->userId = i()->uid;
		$data->dateMake = SG\getFirst($data->dateMake, date('Y-m-d'));
		$data->created = date('U');

		mydb::query('
			INSERT INTO %imed_careplan%
			(`psnid`, `reqId`, `orgid`, `uid`, `datemake`, `created`)
			VALUES
			(:psnId, :reqId, :orgId, :userId, :dateMake, :created)
			',
			$data
		);

		$result = (Object) [
			'planId' => NULL,
			'error' => false,
		];

		if (mydb()->_error) {
			$result->error = mydb()->_error;
			$result->query = mydb()->_query;
			return $result;
		}

		$result->planId = mydb()->insert_id;

		return $result;
	}

	public static function saveTran($data) {
		if (is_array($data)) $data = (Object) $data;

		$data->tranId = SG\getFirst($data->tranId);
		$data->planId = SG\getFirst($data->planId);
		$data->userId = i()->uid;
		$data->planDate = sg_date(SG\getFirst($data->planDate, date('Y-m-d')), 'Y-m-d');
		$data->planTime = SG\getFirst($data->planTime);
		$data->careCode = SG\getFirst($data->careCode);
		$data->detail = SG\getFirst($data->detail);
		$data->created = date('U');

		mydb::query('
			INSERT INTO %imed_careplantr%
			(`cptrid`, `cpid`, `uid`, `plandate`, `plantime`, `carecode`, `detail`, `created`)
			VALUES
			(:tranId, :planId, :userId, :planDate, :planTime, :careCode, :detail, :created)
			ON DUPLICATE KEY UPDATE
			`plandate` = :planDate
			, `plantime` = :planTime
			, `carecode` = :careCode
			, `detail` = :detail
			',
			$data
		);

		$result = (Object) [
			'tranId' => NULL,
			'error' => false,
		];

		$result->query = mydb()->_query;

		if (mydb()->_error) {
			$result->error = mydb()->_error;
			return $result;
		}

		$result->tranId = mydb()->insert_id;

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, order: "pn.`cpid`", sort: "DESC", start: 0, items: 10, debugResult: false}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($conditions, '$conditions');
		if ($debug) debugMsg($options, '$options');

		if ($conditions['orgId']) {
			import('model:org');
			$orgInfo = OrgModel::get($conditions['orgId']);

			$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
			$isViewHomeVisit = $isAdmin || in_array($orgInfo->is->socialtype, array('ADMIN','MODERATOR','CM','PHYSIOTHERAPIST'));

			$configGroupViewMemberVisit = false;
		}

		$result = (Object) [
			'count' => 0,
			'conditions' => SG\json_encode($conditions),
			'options' => SG\json_encode($options),
			'query_time' => 0,
			'items' => [],
		];

		if ($conditions['psnId']) mydb::where('pn.`psnid` = :psnId', ':psnId', $conditions['psnId']);
		if ($conditions['userId']) mydb::where('pn.`uid` = :userId', ':userId', $conditions['userId']);
		if ($conditions['reqId']) mydb::where('pn.`reqId` = :reqId', ':reqId', $conditions['reqId']);
		if ($conditions['orgId']) mydb::where('pn.`orgId` = :orgId', ':orgId', $conditions['orgId']);

		mydb::value('$ORDER$', $options->order);
		mydb::value('$SORT$', $options->sort);
		mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->start.','.$options->items);

		$stmt = 'SELECT
			  pn.`cpid` `planId`, pn.`psnid` `psnId`
			, pn.*
			FROM %imed_careplan% pn
				LEFT JOIN %users% u ON u.`uid` = pn.`uid`
				LEFT JOIN %db_person% p ON p.`psnid` = pn.`psnid`
			%WHERE%
			ORDER BY $ORDER$ $SORT$
			$LIMIT$
			';

		if ($options->items == 1) {
			$result =  mydb::clearprop(mydb::select($stmt));
		} else {
			$result->items =  mydb::select($stmt)->items;
		}
		$result->count = count($result->items);
		$result->query_time = mydb()->_last_query_time;

		if ($debug) debugMsg(nl2br(mydb()->_query));

		if ($options->debugResult) debugMsg($result, '$result');

		return $result;
	}
}
?>