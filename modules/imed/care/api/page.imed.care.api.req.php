<?php
/**
* iMed :: Care Request API
* Created 2021-07-30
* Modify  2021-08-26
*
* @param String $keyId // Leading with 0
* @param String $action
* @param Int $tranId
* @return Mixed
*
* @usage imed/care/api/req
*/

$debug = true;

import('model:imed.patient');
import('model:imed.plan');
import('package:imed/care/models/model.request.php');

class ImedCareApiReq extends Page {
	var $keyId;
	var $action;
	var $tranId;

	function __construct($keyId = NULL, $action = NULL, $tranId = NULL) {
		$this->keyId = $keyId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$requestInfo = RequestModel::get($this->keyId);
		$reqId = $requestInfo->reqId;
		$psnId = $requestInfo->psnId;
		$tranId = $this->tranId;

		if (!$reqId) return message('error', 'ไม่มีรายการที่ระบุ');
		else if (!$requestInfo->is->access) return message('error', 'Access Denied');

		// debugMsg($requestInfo, '$requestInfo');

		switch ($this->action) {
			case 'patient.add':
				//TODO: เปลี่ยนเป็นรับข้อมูลผู้ป่วยแทน psnId
				if ($tranId) {
					$result = (Object) ['psnId' => $tranId];
				} else {
					$isCreate = user_access('administer imed,create imed at home');
					if (!$isCreate) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access denied']);

					$post = (Object) post('patient');
					$post->module = 'IMED/CARE';

					$result = PatientModel::create($post);

					if ($result->error) return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => $result->error]);
					$ret = $result;
				}
				// Update psnId to request
				if ($result->psnId) {
					mydb::query(
						'UPDATE %imed_request% SET `psnId` = :psnId WHERE `reqId` = :reqId LIMIT 1',
						':reqId', $reqId,
						':psnId', $result->psnId
					);
				}
				break;

			case 'giver.add':
				$ret .= 'บันทึกผู้ให้บริการเรียบร้อย';
				if (post('giver')) {
					mydb::query('UPDATE %imed_request% SET `giverId` = :giverId WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId, ':giverId', post('giver'));
					// $ret .= mydb()->_query;
				}
				break;

			case 'visit.save':
				$ret .= 'บันทึกการให้บริการเรียบร้อย';
				$data = (Object) [
					'seqId' => post('seqId'),
					'uid' => i()->uid,
					'psnId' => $requestInfo->psnId,
					'reqId' => $reqId,
					'service' => _IMED_CARE_SERVICE,
					'rx' => post('msg'),
					'timeData' => sg_date(sg_date(post('servDate'),'Y-m-d').' '.post('servTime'),'U'),
					'created' => date('U'),
					'tranId' => post('tranId'),
				];

				mydb::query('INSERT INTO %imed_service%
					(`seq`, `uid`, `pid`, `reqId`, `service`, `rx`, `timeData`, `created`)
					VALUES
					(:seqId, :uid, :psnId, :reqId, :service, :rx, :timeData, :created)
					ON DUPLICATE KEY UPDATE
					`rx` = :rx
					, `timeData` = :timeData
					',
					$data
				);
				// debugMsg(mydb()->_query);

				if (!mydb()->_error) {
					if (empty($data->seqId)){
						$data->seqId = mydb()->insert_id;
						mydb::query('UPDATE %imed_careplantr% SET `seq` = :seqId WHERE `cptrid` = :tranId LIMIT 1', $data);
						// debugMsg(mydb()->_query);
					}
					do {
						$keyId = SG\uniqid(10);
						mydb::query('UPDATE %imed_service% SET `keyId` = :keyId WHERE `seq` = :seqId LIMIT 1', ':seqId', $data->seqId, ':keyId', $keyId);
						if (mydb()->_affected_rows == 1) break;
					} while (true);
				}

				// $ret .= print_o(post(),'post()');
				break;

			case 'done':
				$ret = 'เรียบร้อย';
				mydb::query('UPDATE %imed_request% SET `done` = 1 WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId);
				break;

			case 'done.cancel':
				$ret = 'เรียบร้อย';
				mydb::query('UPDATE %imed_request% SET `done` = NULL WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId);
				break;

			case 'closed':
				$ret = 'เรียบร้อย';
				mydb::query('UPDATE %imed_request% SET `closed` = 1 WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId);
				break;

			case 'closed.cancel':
				$ret = 'เรียบร้อย';
				mydb::query('UPDATE %imed_request% SET `closed` = NULL WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId);
				break;

			case 'plan.tran.save':
				$post = (Object) post('data');
				// Get Request Care Plan
				if (!$requestInfo->carePlanId) {
					$planId = ImedPlanModel::create(['reqId' => $reqId, 'psnId' => $psnId])->planId;
				} else {
					$planId = $requestInfo->carePlanId;
				}

				if ($planId) {
					$post->planId = $planId;
					$result = ImedPlanModel::saveTran($post);
					// debugMsg($result, '$result');
				} else $ret = message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ไม่มีข้อมูลแผน']);
				// debugMsg($post,'$post');
				break;

			case 'patient.pain':
				$value = post('value');
				$ret = [
					'value' => nl2br($value),
				];
				mydb::query(
					'UPDATE %imed_request% SET `detail` = :detail WHERE `reqId` = :reqId LIMIT 1',
					[':reqId' => $reqId, 'detail' => $value]
				);
				// $ret['debug'] = print_o(post(),'post()');
				// mydb::query('UPDATE ');
				break;

			default:
				$ret = 'ขออภัย!!! ไม่เจอหน้าที่ต้องการอยู่ระบบ';
				break;
		}

		return $ret;
	}
}
?>