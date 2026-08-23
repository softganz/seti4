<?php
/**
 * Person   :: Person Info Api
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-09-28
 * Modified :: 2026-08-23
 * Version  :: 3
 *
 * @param Int $psnId
 * @param String $action
 * @return Object
 *
 * @uses api/person/info/{psnId}/{action}
 */

use Softganz\DB;

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
			return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
		}

		DB::query([
			'INSERT INTO %org_morg%
			(`psnId`, `orgId`, `uid`, `department`, `position`)
			VALUES
			(:psnId, :orgId, :uid, :department, :position)
			ON DUPLICATE KEY UPDATE
			`department` = :department
			, `position` = :position',
			'var' => $data
		]);

		return apiSuccess('บันทึกเรียบร้อย');
	}

	function removeOrgMember() {
		$orgId = post('orgId');
		if (empty($orgId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุองค์กร');

		DB::query([
			'DELETE FROM %org_morg%
			WHERE `orgId` = :orgId AND `psnId` = :psnId
			LIMIT 1',
			'var' => [
				':orgId' => $orgId,
				':psnId' => $this->psnId,
			]
		]);

		return apiSuccess('ลบชื่อจากองค์กรเรียบร้อย');
	}
}
?>