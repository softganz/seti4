<?php
/**
* Model :: LMS
* Created 2021-11-15
* Modify 	2021-11-15
*
* @param Array $args
* @return Object
*
* @usage new LmsModel([])
* @usage LmsModel::function($conditions, $options)
*/

$debug = true;

class LmsModel {
	function __construct($args = []) {
	}

	public static function getSerie($serieId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_array($serieId) || is_object($serieId)) {
			$conditions = (Object) $serieId;
			if ($conditions->serieNo) mydb::where('s.`serieNo` = :serieNo', ':serieNo', $conditions->serieNo);
			if ($conditions->orgId) mydb::where('s.`orgId` = :orgId', ':orgId', $conditions->orgId);
			// if ($conditions->classNo) mydb::where('s.`classNo` = :classNo', ':classNo', $conditions->classNo);
		} else {
			mydb::where('s.`serieId` = :serieId', ':serieId', $serieId);
		}

		$serieInfo = mydb::select(
			'SELECT
			s.*
			FROM %lms_serie% s
			%WHERE%
			LIMIT 1'
		);
		if ($serieInfo->_empty) return NULL;

		$result = (Object) [
			'serieId' => $serieInfo->serieId,
			'serieNo' => $serieInfo->serieNo,
			'info' => mydb::clearprop($serieInfo),
		];

		return $result;
	}

	public static function serieItems($conditions, $options = '{}') {
		$defaults = '{debug: false, order: "l.`serieNo`", sort: "ASC"}';
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

		if ($conditions->projectId) mydb::where('l.`projectId` = :projectId', ':projectId', $conditions->projectId);
		if ($conditions->orgId) mydb::where('l.`orgId` = :orgId', ':orgId', $conditions->orgId);

		mydb::value('$ORDER$', $options->order);
		mydb::value('$SORT$', $options->sort);

		$dbs = mydb::select(
			'SELECT l.*
			FROM %lms_serie% l
			%WHERE%
			ORDER BY $ORDER$ $SORT$'
		);

		$result = (object) [
			'count' => count($dbs->items),
			'items' => $dbs->items,
		];

		return $result;
	}

	public static function createSerie($data) {
		$data = (Object) [
			'serieId' => SG\getFirst($data->serieId),
			'orgId' => SG\getFirst($data->orgId),
			'projectId' => SG\getFirst($data->projectId),
			'serieNo' => SG\getFirst($data->serieNo),
			'dateStart' => $data->dateStart ? sg_date($data->dateStart, 'Y-m-d') : NULL,
			'dateEnd' => $data->dateEnd ? sg_date($data->dateEnd, 'Y-m-d') : NULL,
			'uid' => i()->uid,
			'created' => date('U'),
		];

		$result = (Object) [
			'serieId' => NULL,
			'error' => false,
			'data' => $data,
			'_query' => [],
		];

		mydb::query('INSERT INTO %lms_serie%
			(`serieId`, `orgId`, `projectId`, `serieNo`, `dateStart`, `dateEnd`, `uid`, `created`)
			VALUES
			(:serieId, :orgId, :projectId, :serieNo, :dateStart, :dateEnd, :uid, :created)
			ON DUPLICATE KEY UPDATE
			`dateStart` = :dateStart, `dateEnd` = :dateEnd',
			$data
		);
		$result->_query[] = mydb()->_query;

		if (!mydb()->_error) {
			$result->serieId = !$result->serieId ? mydb()->insert_id : $result->serieId;
			$result->error = false;
		}
		return $result;
	}

	public static function serieClassLevelItems($conditions, $options = '{}') {
		$defaults = '{debug: false, order: "`classLevel`", sort: "ASC"}';
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

		if ($conditions->projectId) mydb::where('l.`projectId` = :projectId', ':projectId', $conditions->projectId);
		if ($conditions->orgId) mydb::where('l.`orgId` = :orgId', ':orgId', $conditions->orgId);

		mydb::value('$ORDER$', $options->order);
		mydb::value('$SORT$', $options->sort);

		$dbs = mydb::select(
			'SELECT
			s.`classLevel`, s.`classNo`
			, level.`className` `classLevelName`
			FROM %lms_student% s
				LEFT JOIN %lms_serie% l ON l.`serieId` = s.`serieId`
				LEFT JOIN %lms_code_classlevel% level ON level.`classLevel` = s.`classLevel`
			%WHERE%
			GROUP BY `classLevel`, s.`classNo`
			ORDER BY $ORDER$ $SORT$'
		);
		// debugMsg(mydb()->_query);

		$result = (object) [
			'count' => count($dbs->items),
			'items' => $dbs->items,
		];

		return $result;
	}

	public static function getStudent($studentId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_array($studentId) || is_object($studentId)) {
			$conditions = (Object) $studentId;
			// if ($conditions->serieNo) mydb::where('s.`serieNo` = :serieNo', ':serieNo', $conditions->serieNo);
			// if ($conditions->orgId) mydb::where('s.`orgId` = :orgId', ':orgId', $conditions->orgId);
		} else {
			mydb::where('s.`studentId` = :studentId', ':studentId', $studentId);
		}

		$studentInfo = mydb::select(
			'SELECT
			s.*
			, p.`psnId`, p.`preName`, p.`name`, p.`lname`
			, p.`birth`
			, serie.`orgId`, org.`name` `orgName`
			FROM %lms_student% s
				LEFT JOIN %lms_serie% serie ON serie.`serieId` = s.`serieId`
				LEFT JOIN %db_org% org ON org.`orgId` = serie.`orgId`
				LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
			%WHERE%
			LIMIT 1'
		);
		if ($studentInfo->_empty) return NULL;

		$result = (Object) [
			'studentId' => $studentInfo->studentId,
			'serieId' => $studentInfo->serieId,
			'psnId' => $studentInfo->psnId,
			'orgId' => $studentInfo->orgId,
			'orgName' => $studentInfo->orgName,
			'fullName' => $studentInfo->preName.$studentInfo->name.' '.$studentInfo->lname,
			'info' => mydb::clearprop($studentInfo),
		];

		return $result;
	}

	public static function getStudentItems($conditions, $options = '{}') {
		$defaults = '{debug: false, order: "CONVERT(p.`name` USING tis620)", sort: "ASC"}';
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

		if ($conditions->projectId) mydb::where('s.`projectId` = :projectId', ':projectId', $conditions->projectId);
		if ($conditions->orgId) mydb::where('serie.`orgId` = :orgId', ':orgId', $conditions->orgId);
		if ($conditions->serieNo) mydb::where('serie.`serieNo` = :serieNo', ':serieNo', $conditions->serieNo);
		if ($conditions->classLevel) mydb::where('s.`classLevel` = :classLevel', ':classLevel', $conditions->classLevel);
		if ($conditions->classNo) mydb::where('s.`classNo` = :classNo', ':classNo', $conditions->classNo);

		mydb::value('$ORDER$', $options->order);
		mydb::value('$SORT$', $options->sort);

		$dbs = mydb::select(
			'SELECT
			s.*
			, p.`preName`, p.`name`, p.`lname`
			, level.`className` `classLevelName`
			FROM %lms_student% s
				LEFT JOIN %lms_serie% serie ON serie.`serieId` = s.`serieId`
				LEFT JOIN %lms_code_classlevel% level ON level.`classLevel` = s.`classLevel`
				LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
			%WHERE%
			ORDER BY $ORDER$ $SORT$'
		);
		// debugMsg(mydb()->_query);

		$result = (object) [
			'count' => count($dbs->items),
			'items' => $dbs->items,
		];

		return $result;
	}
}
?>