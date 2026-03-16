<?php
/**
 * Reaction:: Reaction Model
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2021-09-29
 * Modify  :: 2026-03-16
 * Version :: 2
 *
 * @usage new ReactionModel([])
 * @usage ReactionModel::function($conditions, $options)
 */

use Softganz\DB;

class ReactionModel {

	public static function get($conditions = [], $options = '{}') {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		if (!$conditions->id) return NULL;

		$result = DB::select([
			'SELECT
			`tpid` `topicId`, `approve`
			, `rating`, `rateTimes`, `likeTimes`
			, `comment`, `view` `views`, `reply`
			FROM %topic%
			WHERE `tpid` = :topicId
			LIMIT 1',
			'var' => [':topicId' => $conditions->id]
		]);

		$result = mydb::clearProp($result);

		if ($conditions->bookmark) {
			$result->bookmarks = DB::select([
				'SELECT COUNT(*) `totals`
				FROM %reaction%
				WHERE `refid` = :topicId AND `action` = :action LIMIT 1',
				'var' => [':topicId' => $conditions->id, ':action' => $conditions->bookmark]
			])->totals;
		}

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		$result = (Object) ['count' => 0, 'items' => []];

		$result->items = DB::select([
			'SELECT *, FROM_UNIXTIME(`dateact`, "%Y-%m-%d %H:%i:%s") `dateact`
			FROM %reaction% r
			%WHERE%',
			'%WHERE%' => [
				$conditions->id ? ['r.`refid` = :refId', ':refId' => $conditions->id] : NULL,
				$conditions->userId ? ['r.`uid` = :userId', ':userId' => $conditions->userId] : NULL,
				$conditions->action ? ['r.`action` LIKE :action', ':action' => $conditions->action] : NULL,
			],
		])->items;

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
			$data = (Object) [
				'refid' => $refid,
				'uid' => i()->uid,
				'action' => $action,
				'dateact' => date('U')
			];

			$stmt = 'INSERT INTO %reaction%
				(`refid`,`uid`,`action`,`dateact`)
				VALUES
				(:refid,:uid,:action,:dateact)';

			if ($options->addType == 'toggle') {
				$hasReaction = DB::select([
					'SELECT `actid` FROM %reaction% WHERE `refid` = :refid AND `uid` = :uid AND `action` = :action LIMIT 1',
					'var' => $data
				])->actid;
				//debugMsg($hasReaction. mydb()->_query);
				if ($hasReaction) {
					DB::query([
						'DELETE FROM %reaction% WHERE `actid` = :actid LIMIT 1',
						'var' => [':actid' => $hasReaction]
					]);
					$result = false;
					//debugMsg(mydb()->_query);
					unset($stmt);
				} else {
					$result = true;
				}
			}

			try {
				if ($stmt) DB::query([
					$stmt,
					'var' => $data
				]);
			} catch (Exception $exception) {
				DB::query([
					'CREATE TABLE %reaction% (
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
					);'
				]);
				// Requery add
				DB::query([
					$stmt,
					'var' => $data
				]);
				$result = true;
			}

			if ($options->count == 'topic:liketimes') {
				DB::query([
					'UPDATE %topic% SET `liketimes` = IF(`liketimes` >= 0, `liketimes` '.($hasReaction ? '-':'+').' 1, 0) WHERE `tpid` = :tpid LIMIT 1',
					'var' => [':tpid' => $refid]
				]);
				//debugMsg(mydb()->_query);
			} else if ($options->count == 'msg:liketimes') {
				DB::query([
					$stmt = 'UPDATE %msg% SET `liketimes` = IF(`liketimes` >= 0, `liketimes` '.($hasReaction ? '-':'+').' 1, 0) WHERE `msgid` = :msgid LIMIT 1',
					'var' => [':msgid' => $refid]
				]);
				if ($debug) debugMsg(R('query'));
			}
		}

		// Update topic view count
		if ($action == 'TOPIC.VIEW' && $options->updateView) {
			DB::query([
				'UPDATE %topic% SET `view` = `view` + 1, `last_view` = :last_view WHERE `tpid` = :tpid LIMIT 1',
				'var' => [
					':tpid' => $refid,
					':last_view' => date("Y-m-d H:i:s")
				]
			]);
		}
		return $result;
	}

	public static function user($topicId, $userId = NULL) {
		$userId = \SG\getFirst($userId, i()->uid);
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