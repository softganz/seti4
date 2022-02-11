<?php
/**
* Model :: Description
* Created 2021-11-14
* Modify 	2021-11-14
*
* @param Array $args
* @return Object
*
* @usage new StudentModel([])
* @usage StudentModel::function($conditions, $options)
*/

$debug = true;

class StudentModel {
	function __construct($args = []) {
	}

	public static function get($studentId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$studentInfo = mydb::select(
			'SELECT
			s.*
			, p.`prename`, p.`name`, p.`lname`
			, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `fullname`
			, p.`cid`
			, p.`phone`
			, p.`email`
			, p.`position`, p.`orgId`, o.`name` `orgName`
			FROM %lms_student% s
				LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
				LEFT JOIN %db_org% o ON o.`orgId` = p.`orgId`
			WHERE s.`studentId` = :studentId
			LIMIT 1',
			[':studentId' => $studentId]
		);

		if ($studentInfo->_empty) return NULL;

		$result = (Object) [
			'studentId' => $studentInfo->studentId,
			'fullname' => $studentInfo->fullname,
			'info' => mydb::clearprop($studentInfo),
		];

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

		return $result;
	}
}
?>