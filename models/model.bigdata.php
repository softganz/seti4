<?php
/**
* Model :: Big Data Model
* Created 2021-12-24
* Modify 	2021-12-24
*
* @param Array $args
* @return Object
*
* @usage new BigDataModel([])
* @usage BigDataModel::function($conditions, $options)
*/

class BigDataModel {
	public static function update($keyName, $value) {
		$bigId = NULL;

		if (is_numeric($keyName)) $bigId = $keyName;
		else if (preg_match('/\//', $keyName)) {
			list($keyName, $fldName, $keyId, $fldRef) = explode('/', $keyName);

			$bigId = mydb::select(
				'SELECT `bigId` FROM %bigdata% WHERE `keyName` = :keyName AND `fldName` = :fldName AND `keyId` = :keyId LIMIT 1',
				[':keyName' => $keyName, ':fldName' => $fldName, ':keyId' => $keyId]
			)->bigId;
		}

		mydb::query(
			'INSERT INTO %bigdata%
			(`bigId`, `keyName`, `keyId`, `fldName`, `fldType`, `fldRef`, `fldData`, `created`, `ucreated`)
			VALUES
			(:bigId, :keyName, :keyId, :fldName, :fldType, :fldRef, :fldData, :created, :ucreated)
			ON DUPLICATE KEY UPDATE
			`fldData` = :fldData
			, `modified` = :modified
			, `umodified` = :umodified
			',
			[
				':bigId' => $bigId,
				':keyName' => $keyName,
				':keyId' => $keyId,
				':fldName' => $fldName,
				':fldType' => NULL,
				':fldRef' => $fldRef,
				':fldData' => $value,
				':created' => date('U'),
				':ucreated' => i()->uid,
				':modified' => date('U'),
				':umodified' => i()->uid,
			]
		);
	}

	public static function updateJson($keyName, $data) {
		$bigId = NULL;

		if (is_numeric($keyName)) $bigId = $keyName;
		else if (preg_match('/\//', $keyName)) {
			list($keyName, $fldName, $keyId, $fldRef) = explode('/', $keyName);

			$bigId = mydb::select(
				'SELECT `bigId` FROM %bigdata% WHERE `keyName` = :keyName AND `fldName` = :fldName AND `keyId` = :keyId LIMIT 1',
				[':keyName' => $keyName, ':fldName' => $fldName, ':keyId' => $keyId]
			)->bigId;
		}

		$oldData = $bigId ? mydb::select('SELECT `fldData` FROM %bigdata% WHERE `bigId` = :bigId LIMIT 1', [':bigId' => $bigId])->fldData : (Object) [];

		$oldData = SG\json_decode($oldData);

		$updateData = SG\json_decode($data, $oldData);

		mydb::query(
			'INSERT INTO %bigdata%
			(`bigId`, `keyName`, `keyId`, `fldName`, `fldType`, `fldRef`, `fldData`, `created`, `ucreated`)
			VALUES
			(:bigId, :keyName, :keyId, :fldName, :fldType, :fldRef, :fldData, :created, :ucreated)
			ON DUPLICATE KEY UPDATE
			`fldData` = :fldData
			, `modified` = :modified
			, `umodified` = :umodified
			',
			[
				':bigId' => $bigId,
				':keyName' => $keyName,
				':keyId' => $keyId,
				':fldName' => $fldName,
				':fldType' => 'JSON',
				':fldRef' => $fldRef,
				':fldData' => SG\json_encode($updateData),
				':created' => date('U'),
				':ucreated' => i()->uid,
				':modified' => date('U'),
				':umodified' => i()->uid,
			]
		);
	}

	public static function getJson($keyName) {
		if (is_numeric($keyName)) {
			$jsonData = mydb::select('SELECT `fldData` FROM %bigdata% WHERE `bigId` = :bigId LIMIT 1; -- {reset: false}', [':bigId' => $keyName])->fldData;
		} else if (preg_match('/\//', $keyName)) {
			list($keyName, $fldName, $keyId, $fldRef) = explode('/', $keyName);

			$jsonData = mydb::select(
				'SELECT `fldData`
				FROM %bigdata%
				WHERE `keyName` = :keyName AND `fldName` = :fldName AND '
				. (is_null($keyId) ? '`keyId` IS NULL' : '`keyId` = :keyId')
				. ' AND '.
					(is_null($fldRef) ? '`fldRef` IS NULL' : '`fldRef` = :fldRef')
				. ' LIMIT 1',
				[':keyName' => $keyName, ':fldName' => $fldName, ':keyId' => $keyId, ':fldRef' => $fldRef]
			)->fldData;
			return SG\json_decode($jsonData);
		}
	}

	// public static function get($id, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	$result = NULL;

	// 	return $result;
	// }

	/*
	*
	* @para $conditions
	* @para $options
	*
	* $conditions
	*		Numeric bigId
	*		String keyName/fldName/keyId/fldRef
	*		Object keyName,fldName,keyId,fldRef
	*/
	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, key: null, value: null}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($options->debug) debugMsg($options, '$options');

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else if (is_numeric($conditions)) {
			$bigId = $conditions;
		} else {
			$conditions = (Object) ['key' => $conditions];
		}

		if ($bigId) {
			return mydb::clearprop(mydb::select(
				'SELECT `bigId` `id`, `keyName` `key`, `fldName` `name`, `fldType` `type`, `fldRef` `refId`, `fldData` `value`, `created` `createdDate`, `uCreated` `createdBy`, `modified` `modifiedDate`, `uModified` `modifiedBy`
				FROM %bigdata% WHERE `bigId` = :bigId LIMIT 1',
				[':bigId' => $bigId]
			));
		}

		$result = (Object) [
			'count' => 0,
			'items' => [],
		];

		if (preg_match('/\//', $conditions->key)) {
			list($conditions->key, $conditions->name, $keyId, $fldRef) = explode('/', $conditions->key);
		}

		if (!$conditions->key) return $result;

		mydb::where('`keyName` = :keyName', ':keyName', $conditions->key);

		if (is_string($conditions->name)) {
			$conditions->name = preg_replace('/\*$/', '%', $conditions->name);
			mydb::where('`fldName` LIKE :fldName', ':fldName', $conditions->name);
		} else if (is_array($conditions->name)) {
			mydb::where('`fldName` IN ( :fldName )', ':fldName', 'SET-STRING:'.implode(',', $conditions->name));
		}

		if ($debug) debugMsg($conditions, '$conditions');

		$result->items = mydb::select(
			'SELECT `bigId` `id`, `keyName` `key`, `fldName` `name`, `fldType` `type`, `fldRef` `refId`, `fldData` `value`, `created` `createdDate`, `uCreated` `createdBy`, `modified` `modifiedDate`, `uModified` `modifiedBy`
			FROM %bigdata%
			%WHERE%;
			-- '.json_encode([
				'key' => $options->key,
				'value' => $options->value,
			])
		)->items;

		$result->count = count($result->items);

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}
}
?>