<?php
/**
* API  :: Person Info Api
* Created :: 2022-09-28
* Modify  :: 2022-11-19
* Version :: 2
*
* @param Int $psnId
* @param String $action
* @return Object
*
* @usage api/person/info/{psnId}/{action}
*/

import('model:person.php');

class PersonInfoApi extends PageApi {
	var $psnId;
	var $action;
	var $right;

	function __construct($psnId = NULL, $action = NULL) {
		parent::__construct([
			'action' => $action,
			'personInfo' => $psnId ? PersonModel::get($psnId) : NULL,
		]);
		$this->psnId = $this->personInfo->psnId;
		$this->right = (Object) [
			'edit' => ($this->personInfo->RIGHT & _IS_EDITABLE) > 0,
			'access' => ($this->personInfo->RIGHT & _IS_ACCESS) > 0,
		];
		// debugMsg($this, '$this');
	}

	function build() {
		if (empty($this->psnId)) {
			return new ErrorMessage([
				'code' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'PROCESS ERROR',
			]);
		} else if (!$this->right->edit) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied'
			]);
		}

		return parent::build();
	}

	function addOrgMember() {
		$data = (Object) [
			'psnId' => $this->psnId,
			'orgId' => post('orgId'),
			'department' => post('department'),
			'position' => post('position'),
			'uid' => i()->uid,
		];
		if (empty($data->psnId) || empty($data->orgId)) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ข้อมูลไม่ครบถ้วน',
			]);
		}

		mydb::query(
			'INSERT INTO %org_morg%
			(`psnId`, `orgId`, `uid`, `department`, `position`)
			VALUES
			(:psnId, :orgId, :uid, :department, :position)
			ON DUPLICATE KEY UPDATE
			`department` = :department
			, `position` = :position',
			$data
		);

		return true;
	}

	function removeOrgMember() {
		if ($orgId = post('orgId')) {
			mydb::query(
				'DELETE FROM %org_morg%
				WHERE `orgId` = :orgId AND `psnId` = :psnId
				LIMIT 1',
				[
					'orgId' => $orgId,
					':psnId' => $this->psnId,
				]
			);
		}
	}
}
?>