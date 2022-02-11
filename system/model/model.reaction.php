<?php
/**
* Reaction Model :: Reaction Model
* Created 2021-09-29
* Modify 	2021-09-29
*
* @usage new ReactionModel([])
* @usage ReactionModel::function($conditions, $options)
*/

$debug = true;

class ReactionModel {

	public static function get($conditions = [], $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		if (!$conditions->id) return NULL;

		$result = mydb::select(
			'SELECT
			`tpid` `topicId`, `approve`
			, `rating`, `rateTimes`, `likeTimes`
			, `comment`, `view` `views`, `reply`
			FROM %topic%
			WHERE `tpid` = :topicId
			LIMIT 1',
			[':topicId' => $conditions->id]
		);

		$result = mydb::clearprop($result);

		if ($conditions->bookmark) {
			$result->bookmarks = mydb::select(
				'SELECT COUNT(*) `totals`
				FROM %reaction%
				WHERE `refid` = :topicId AND `action` = :action LIMIT 1',
				[':topicId' => $conditions->id, ':action' => $conditions->bookmark]
			)->totals;
		}

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		$result = (Object) ['count' => 0, 'items' => []];

		if ($conditions->id) mydb::where('r.`refid` = :refId', ':refId', $conditions->id);
		if ($conditions->userId) mydb::where('r.`uid` = :userId', ':userId', $conditions->userId);
		if ($conditions->action) mydb::where('r.`action` LIKE :action', ':action', $conditions->action);

		mydb::value('$LIMIT$', '');

		$result->items = mydb::select(
			'SELECT *, FROM_UNIXTIME(`dateact`, "%Y-%m-%d %H:%i:%s") `dateact`
			FROM %reaction% r
			%WHERE%
			$LIMIT$'
		)->items;

		$result->count = count($result->items);

		return $result;
	}

	public static function add($refid, $action, $options = '{}') {
		$defaults = '{debug: false, updateView: true, addType: "insert", toggle: false, count: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		//debugMsg('refid = '.$refid.' , action = '.$action);
		// Add reaction to table reaction
		if (i()->ok) {
			$data = new stdClass();
			$data->refid = $refid;
			$data->uid = i()->uid;
			$data->action = $action;
			$data->dateact = date('U');

			$stmt = 'INSERT INTO %reaction%
				(`refid`,`uid`,`action`,`dateact`)
				VALUES
				(:refid,:uid,:action,:dateact)';

			if ($options->addType == 'toggle') {
				$hasReaction = mydb::select('SELECT `actid` FROM %reaction% WHERE `refid` = :refid AND `uid` = :uid AND `action` = :action LIMIT 1',$data)->actid;
				//debugMsg($hasReaction. mydb()->_query);
				if ($hasReaction) {
					mydb::query('DELETE FROM %reaction% WHERE `actid` = :actid LIMIT 1', ':actid', $hasReaction);
					$result = false;
					//debugMsg(mydb()->_query);
					unset($stmt);
				} else {
					$result = true;
				}
			}


			if ($stmt) mydb::query($stmt,$data);

			if (mydb()->_error) {
				$createStmt = 'CREATE TABLE %reaction% (
					`actid` int(11) NOT NULL AUTO_INCREMENT,
					`refid` int(11) NULL,
					`uid` int(11) NULL,
					`action` varchar(10) NULL,
					`dateact` bigint(20) NULL,
					PRIMARY KEY (`actid`),
					KEY `refid` (`refid`),
					KEY `uid` (`uid`),
					KEY `action` (`action`),
					KEY `dateact` (`dateact`)
				);';
				mydb::query($createStmt);
				// Requery add
				mydb::query($stmt, $data);
				$result = true;
			}

			if ($options->count == 'topic:liketimes') {
				$stmt = 'UPDATE %topic% SET `liketimes` = IF(`liketimes` >= 0, `liketimes` '.($hasReaction ? '-':'+').' 1, 0) WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid',$refid);
				//debugMsg(mydb()->_query);
			} else if ($options->count == 'msg:liketimes') {
				$stmt = 'UPDATE %msg% SET `liketimes` = IF(`liketimes` >= 0, `liketimes` '.($hasReaction ? '-':'+').' 1, 0) WHERE `msgid` = :msgid LIMIT 1';
				mydb::query($stmt, ':msgid',$refid);
				if ($debug) debugMsg(mydb()->_query);
			}
		}

		// Update topic view count
		if ($action == 'TOPIC.VIEW' && $options->updateView) {
			$stmt = 'UPDATE %topic% SET `view` = `view` + 1, `last_view` = :last_view WHERE `tpid` = :tpid LIMIT 1';
			mydb::query($stmt,':tpid',$refid, ':last_view',date("Y-m-d H:i:s"));
		}
		return $result;
	}

	public static function user($topicId, $userId = NULL) {
		$userId = SG\getFirst($userId, i()->uid);
		if (empty($userId)) return NULL;
		return mydb::select(
			'SELECT DISTINCT
			  `refid`, `action`
			FROM %reaction%
			WHERE `refid` = :topicId AND `uid` = :userId;
			-- {key: "refid", value: "action"}',
			[':topicId' => $topicId, ':userId' => $userId]
		)->items;
	}
}
?>