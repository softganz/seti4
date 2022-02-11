<?php
/**
* Project :: Only nxt Follow Information API
* Created 2021-11-10
* Modify  2021-11-11
*
* @param Int $projectId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage project/info/nxt/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('page:project.info.api.php');

class ProjectInfoNxtApi extends Page {
	var $projectId;
	var $action;
	var $tranId;

	function __construct($projectId = NULL, $action = NULL, $tranId = NULL) {
		$this->projectId = $projectId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('projectId '.$this->projectId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (substr($this->projectId, -1) == '*') list($this->projectId, $isProjectAllType) = array(substr($this->projectId,0,-1),true);

		$projectInfo = ProjectFollowModel::get($this->projectId);
		$this->projectId = $projectId = $projectInfo->projectId;
		$tranId = $this->tranId;

		$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
		$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
		$isOwner = $projectInfo->RIGHT & _IS_OWNER;
		$isMember = $isAdmin || $projectInfo->info->membershipType || $projectInfo->info->orgMemberShipType;
		$isAddAction = $isEdit || $projectInfo->info->membershipType;
		$isOfficer = $isAdmin
			|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
			|| in_array($projectInfo->orgMemberShipType, array('ADMIN','OFFICER'));
		$isEdit = $isMember;

		if (!$projectId) return message(['responseCode' => _HTTP_OK_NO_CONTENT, 'text' => 'ERROR : NO PROJECT']);

		// Public API
		$publicApi = [];

		if (i()->ok && in_array($this->action, $publicApi)) {
			return $this->publicApi();
		} else if (!$isMember) {
			return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'ERROR: Access Denied']);
		}

		// Member API
		$tagname = 'info';

		$ret = '';
		//$ret .= 'Action = '.$this->action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
		// $ret .= print_o($projectInfo, '$projectInfo');

		switch ($this->action) {

			case 'serie.save':
				if ($isEdit) {
					$data = (Object) [
						'serieId' => SG\getFirst(post('serieId'),post('serieid')),
						'orgId' => post('orgId'),
						'projectId' => $this->projectId,
						'serieNo' => post('serieNo'),
						'dateStart' => sg_date(SG\getFirst(post('dateStart'),post('datestart')), 'Y-m-d'),
						'dateEnd' => sg_date(SG\getFirst(post('dateEnd'),post('dateend')), 'Y-m-d'),
						'uid' => i()->uid,
						'created' => date('U'),
					];
					mydb::query('INSERT INTO %lms_serie%
						(`serieId`, `orgId`, `projectId`, `serieNo`, `dateStart`, `dateEnd`, `uid`, `created`)
						VALUES
						(:serieId, :orgId, :projectId, :serieNo, :dateStart, :dateEnd, :uid, :created)
						ON DUPLICATE KEY UPDATE
						`dateStart` = :dateStart, `dateEnd` = :dateEnd',
						$data
					);
					// debugMsg(mydb()->_query);
					// debugMsg($data, '$data');
				}
				// $ret = [
				// 	'msg' => print_o(post(),'post').print_o($data, '$data'),
				// ];
				break;

			case 'student.save':
				if ($isEdit && ($serieNo = $tranId) && ($name = post('name'))) {
					list($name, $lname) = sg::explode_name(' ',post('name'));
					$post = (Object) post();
					$data = (Object) [
						'studentId' => $post->studentId,
						'psnId' => $post->psnId,
						'projectId' => $this->projectId,
						'prename' => $post->prename,
						'name' => $name,
						'lname' => $lname,
						'cid' => $post->cid,
						'phone' => $post->phone,
						'email' => $post->email,
						'serieNo' => $serieNo,
						'studentCode' => $post->studentCode,
						'courseType' => $post->courseType,
						'orgId' => $post->orgId,
						'position' => $post->position,
						'uid' => i()->uid,
						'created' => date('U'),
					];

					// Create person record
					mydb::query(
						'INSERT INTO %db_person%
						(`psnId`, `prename`, `name`, `lname`, `cid`, `phone`, `email`, `uid`, `orgId`, `position`, `created`)
						VALUES
						(:psnId, :prename, :name, :lname, :cid, :phone, :email, :uid, :orgId, :position, :created)
						ON DUPLICATE KEY UPDATE
						  `prename` = :prename
						, `name` = :name
						, `lname` = :lname
						, `cid` = :cid
						, `phone` = :phone
						, `email` = :email
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
						(`studentId`, `psnId`, `projectId`, `uid`, `studentCode`, `serieNo`, `courseType`, `created`)
						VALUES
						(:studentId, :psnId, :projectId, :uid, :studentCode, :serieNo, :courseType, :created)
						ON DUPLICATE KEY UPDATE
						  `studentCode` = :studentCode
						, `courseType` =  :courseType
						',
						$data
					);
					// debugMsg(mydb()->_query);
					// debugMsg($data, '$data');
				}
				break;

			case 'student.remove':
				if ($tranId) {
					mydb::query(
						'DELETE s,p
						FROM %lms_student% s
							LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
						WHERE s.`studentId` = :studentId',
						[':studentId' => $tranId]
					);
					// debugMsg(mydb()->_query);
				}
				break;

			case 'student.status':
				if ($tranId) {
					mydb::query(
						'UPDATE %lms_student% SET `status` = :status WHERE `studentId` = :studentId AND `projectId` = :projectId LIMIT 1',
						[
							':studentId' => $tranId,
							':projectId' => $projectId,
							':status' => post('status'),
						]
					);
				}
				break;

			default:
				$ret .= 'NO ACTION';
				break;
		}

		return $ret;
	}

	function publicApi() {
		$ret = NULL;
		switch ($this->action) {

		}
		return $ret;
	}
}
?>