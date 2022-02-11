<?php
/**
* LMS :: Student Serie API
* Created 2021-12-05
* Modify  2021-12-05
*
* @param Int $serieId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage lms/serie/api/{serieId}/{action}[/{tranId}]
*/

import('model:lms.php');

class LmsSerieApi extends Page {
	var $serieId;
	var $action;
	var $tranId;
	var $serieInfo;

	function __construct($serieId, $action, $tranId = NULL) {
		$this->serieId = $serieId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('serieId '.$this->serieId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$serieInfo = LmsModel::getSerie($this->serieId);
		$tranId = $this->tranId;

		if (empty($this->serieId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $serieInfo->RIGHT & _IS_ACCESS;
		$isEdit = true;//$serieInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'student.save' :
				if ($isEdit && ($name = post('name'))) {
					$post = (Object) post();
					list($name, $lname) = sg::explode_name(' ', $post->name);
					$hasBirthDay = $post->birth['year'] && $post->birth['month'] && $post->birth['date'];

					$data = (Object) [
						'studentId' => $post->studentId,
						'serieId' => $this->serieId,
						'psnId' => $post->psnId,
						'projectId' => $this->projectId,
						'prename' => $post->prename,
						'name' => $name,
						'lname' => $lname,
						'cid' => $post->cid,
						'phone' => $post->phone,
						'email' => $post->email,
						'serieNo' => $serieInfo->serieNo,
						'studentCode' => $post->studentCode,
						'courseType' => $post->courseType,
						'classLevel' => $post->classLevel,
						'classNo' => $post->classNo,
						'orgId' => $post->orgId,
						'position' => $post->position,
						'birth' => $hasBirthDay ? $post->birth['year'].'-'.$post->birth['month'].'-'.$post->birth['date'] : NULL,
						'uid' => i()->uid,
						'created' => date('U'),
					];

					// Create person record
					mydb::query(
						'INSERT INTO %db_person%
						(`psnId`, `prename`, `name`, `lname`, `cid`, `phone`, `email`, `birth`, `uid`, `orgId`, `position`, `created`)
						VALUES
						(:psnId, :prename, :name, :lname, :cid, :phone, :email, :birth, :uid, :orgId, :position, :created)
						ON DUPLICATE KEY UPDATE
						  `prename` = :prename
						, `name` = :name
						, `lname` = :lname
						, `cid` = :cid
						, `phone` = :phone
						, `email` = :email
						, `birth` = :birth
						, `orgId` = :orgId
						, `position` = :position
						',
						$data
					);
					// debugMsg(mydb()->_query);

					if (empty($data->psnId)) {
						$data->psnId = mydb()->insert_id;
					}
					mydb::query(
						'INSERT INTO %lms_student%
						(`studentId`, `serieId`, `psnId`, `projectId`, `uid`, `studentCode`, `courseType`, `classLevel`, `classNo`, `created`)
						VALUES
						(:studentId, :serieId, :psnId, :projectId, :uid, :studentCode, :courseType, :classLevel, :classNo, :created)
						ON DUPLICATE KEY UPDATE
						  `studentCode` = :studentCode
						, `courseType` =  :courseType
						, `classLevel` = :classLevel
						, `classNo` = :classNo
						',
						$data
					);
					// debugMsg(mydb()->_query);
					// debugMsg($data, '$data');
					// debugMsg($post, '$post');
				}
				break;

			default:
				return new ErrorMessage(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>