<?php
/**
* LMS :: Student API
* Created 2021-12-06
* Modify  2021-12-06
*
* @param Int $studentId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage lms/student/api/{studentId}/{action}[/{tranId}]
*/

import('model:lms.php');
import('model:org.php');

class LmsStudentApi extends Page {
	var $studentId;
	var $action;
	var $tranId;
	var $studentInfo;

	function __construct($studentId, $action, $tranId = NULL) {
		$this->studentId = $studentId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('serieId '.$this->studentId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$studentInfo = LmsModel::getStudent($this->studentId);
		$orgInfo = $studentInfo->orgId ? OrgModel::get($studentInfo->orgId) : NULL;
		$tranId = $this->tranId;

		// debugMsg($studentInfo,'$studentInfo');
		// debugMsg($orgInfo,'$orgInfo');

		if (empty($this->studentId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $serieInfo->RIGHT & _IS_ACCESS;
		$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		if (!$isEdit) return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'API Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'weight.save' :
				$post = (Object) post();
				list($term,$period) = explode(':', $post->period);
				$data = (Object) [
					'recordId' => $post->recordId,
					'studentId' => $this->studentId,
					'recordDate' => sg_date($post->date, 'Y-m-d'),
					'classLevel' => $post->classLevel,
					'year' => $post->year,
					'term' => $term,
					'period' => $period,
					'weight' => sg_strip_money($post->weight),
					'height' => sg_strip_money($post->height),
					'uid' => i()->uid,
					'created' => date('U'),
				];

				mydb::query(
					'INSERT INTO %lms_weight%
					(`recordId`, `studentId`, `recordDate`, `classLevel`, `year`, `term`, `period`, `weight`, `height`, `uid`, `created`)
					VALUES
					(:recordId, :studentId, :recordDate, :classLevel, :year, :term, :period, :weight, :height, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`weight` = :weight
					, `height` = :height',
					$data
				);
				// debugMsg(mydb()->_query);
				// debugMsg($data, '$data');
				// debugMsg($post, '$post');
				break;

			default:
				return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>