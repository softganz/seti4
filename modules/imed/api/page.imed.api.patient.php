<?php
/**
* iMed API :: Patient API
* Created 2020-12-21
* Modify  2021-08-29
*
* @param Int $psnId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage imed/api/patient/{psnId}/{action}[/{tranId}]
*/

$debug = true;

import('model:imed.patient');

class ImedApiPatient extends Page {
	var $psnId;
	var $action;
	var $tranId;
	var $psnInfo;

	function __construct($psnId, $action = NULL, $tranId = NULL) {
		$this->psnInfo = PatientModel::get($psnId);
		$this->psnId = $this->psnInfo->psnId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$psnId = $this->psnInfo->psnId;
		$tranId = $this->tranId;
		$psnInfo = $this->psnInfo;

		// debugMsg('Id '.$this->psnId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->psnId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// Olny user has right to access
		$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
		$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

		// If remove isEdit check, Must check right in each action
		if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'delete':
				if (SG\confirm()) {
					$result = PatientModel::delete($psnId);
					$ret .= $result->error ? $result->error : 'Delete patient complete';
				}
				break;

			case 'dead':
				// Set imed_disabled => discharge = 26, dischargedate = date
				// Set db_person => dischar = 1, ddisch = date
				// co_discharge => 1 = ตาย
				// co_category => 26 = ตาย
				if (post('date')) {
					$ret .= 'บันทึกการเสียชีวิตเรียบร้อย';
					mydb::query(
						'UPDATE %db_person% SET `dischar` = 1, `ddisch` = :ddisch WHERE `psnid` = :psnid LIMIT 1',
						':psnid', $psnId,
						':ddisch', sg_date(post('date'),'Y-m-d')
					);
					// debugMsg(mydb()->_query);
					$psnInfo->person->dead->course = post('dead');
					R::Model('imed.person.save', $psnId, $psnInfo->person);
					// debugMsg(mydb()->_query);
				}
				break;

			case 'dead.cancel':
				if (SG\confirm()) {
					mydb::query(
						'UPDATE %db_person% SET `dischar` = NULL, `ddisch` = NULL WHERE `psnid` = :psnid LIMIT 1',
						':psnid', $psnId
					);
					$ret .= 'ยกเลิกบันทึกการเสียชีวิตเรียบร้อย';
				}
				break;

			case 'disabled.add':
				$stmt = 'INSERT INTO %imed_disabled% (`pid`, `uid`, `created`) VALUES (:psnid, :uid, :created) ON DUPLICATE KEY UPDATE `pid` = :psnid';
				mydb::query($stmt, ':psnid', $psnId, ':uid', i()->uid, ':created', date('U'));
				//$ret .= mydb()->_query;
				if (post('defect')) {
					$stmt = 'INSERT INTO %imed_disabled_defect% (`pid`, `defect`) VALUES (:psnid, :defect) ON DUPLICATE KEY UPDATE `defect` = :defect';
					mydb::query($stmt, ':psnid', $psnId, ':defect', post('defect'));
					//$ret .= mydb()->_query;
				}

				$stmt = 'INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created) ON DUPLICATE KEY UPDATE `careid` = :careid';
				mydb::query($stmt, ':pid',$psnId, ':careid',_IMED_CARE_DISABLED, ':uid',i()->uid, ':created', date('U'));
				//$ret .= mydb()->_query;
				$ret .= 'เพิ่มประเภทความพิการเรียบร้อย';
				break;

			case 'disabled.remove':
				mydb::query(
					'DELETE FROM %imed_disabled% WHERE `pid` = :psnid LIMIT 1',
					':psnid', $psnId
				);

				mydb::query(
					'DELETE FROM %imed_disabled_defect% WHERE `pid` = :psnid',
					':psnid', $psnId
				);

				mydb::query(
					'DELETE FROM %imed_care% WHERE `pid` = :psnid AND `careid`=:careid LIMIT 1',
				   ':psnid', $psnId,
				   ':careid', _IMED_CARE_DISABLED
				);
				break;

			case 'disabled.defect.remove':
				if ($tranId && SG\confirm()) {
					$defect_id = intval($tranId);
					mydb::query(
						'DELETE FROM %imed_disabled_defect% WHERE `pid`=:pid AND `defect`=:defect LIMIT 1',
						':pid', $psnId,
						':defect', $defect_id
					);
				}
				break;

			case 'rehab.add':
				mydb::query(
					'INSERT INTO %imed_care%
					(`pid`,`careid`, `status`, `uid`, `created`)
					VALUES
					(:pid, :careid, 1, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`careid` = :careid',
					':pid', $psnId,
					':careid', _IMED_CARE_REHAB,
					':uid', i()->uid,
					':created', date('U')
				);
				break;

			case 'rehab.remove':
				mydb::query(
					'DELETE FROM %imed_care% WHERE `pid` = :psnid AND `careid`=:careid LIMIT 1',
					':psnid', $psnId,
					':careid', _IMED_CARE_REHAB
				);
				break;

			case 'elder.add':
				mydb::query(
					'INSERT INTO %imed_care%
					(`pid`,`careid`, `status`, `uid`, `created`)
					VALUES
					(:pid, :careid, 1, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`careid` = :careid',
					':pid',$psnId,
					':careid',_IMED_CARE_ELDER,
					':uid',i()->uid,
					':created', date('U')
				);
				break;

			case 'elder.remove':
				mydb::query(
					'DELETE FROM %imed_care% WHERE `pid`=:pid AND `careid`=:careid LIMIT 1',
					':pid', $psnId,
					':careid', _IMED_CARE_ELDER
				);
				break;

			case 'photo.upload':
				$photo = (object)$_FILES['photo'];
				if (is_uploaded_file($photo->tmp_name)) {
					$photo->name = 'profile-'.$psnId.'.jpg';
					$photo->overwrite = true;
					$result = R::Model('photo.save', $photo, 'upload/imed/');
					if ($result->complete && $result->save->_file) {
						$ret .= '<img src="'.imed_model::patient_photo($psnId).'" width="100%" height="100%" />';
					}
				}
				break;

			case 'gis.save':
				if ($getLoc = post('location')) {
					$data = new stdClass;
					$data->gis = SG\getFirst($psnInfo->info->gis);
					$data->psnId = $psnId;
					$data->uid = i()->uid;
					$data->table = 'sgz_db_person';
					$data->latlng = 'func.POINT('.$getLoc.')';
					$data->created = date('U');

					$stmt = 'INSERT INTO %gis%
						(`gis`, `table`, `latlng`) VALUES (:gis, :table, :latlng)
						ON DUPLICATE KEY UPDATE `latlng` = :latlng
						';
					mydb::query($stmt, $data);

					if (empty($data->gis)) {
						$data->gis = mydb()->insert_id;
						mydb::query(
							'UPDATE %db_person% SET `gis` = :gis WHERE `psnid` = :psnId LIMIT 1',
							$data
						);
					}

					// Save location history
					mydb::query(
						'INSERT INTO %imed_patient_gis% SET `pid` = :psnId, `uid` = :uid, `latlng` = :latlng,`created` = :created',
						$data
					);
				} else {
					mydb::query(
						'UPDATE %db_person% SET `gis` = NULL WHERE `psnid` = :psnId LIMIT 1',
						':psnId', $psnId
					);
				}
				break;

			case 'gis.remove':
				if (SG\confirm()) {
					mydb::query(
						'UPDATE %db_person% SET `gis` = NULL WHERE `psnid` = :psnid LIMIT 1',
						':psnid', $psnId
					);
				}
				break;

			case 'tran.save':
				$code = post('code');
				mydb::query(
					'INSERT INTO %imed_tr%
					(`pid`, `uid`, `tr_code`, `cat_id`, `status`, `detail1`, `detail2`, `created`)
					VALUES
					(:pid, :uid, :tr_code, :cat_id, :status, :detail1, :detail2, :created)',
					':pid', $psnId,
					':uid', i()->uid,
					':tr_code', $code,
					':cat_id', post('catid') ,
					':status', post('status'),
					':detail1', post('detail1'),
					':detail2', post('detail2'),
					':created', date('U')
				);
				if (!mydb()->_error) {
					$insertId = mydb()->insert_id;

					if (post('admit')) {
						mydb::query(
							'UPDATE %db_person% SET `admit` = 1 WHERE `psnid` = :psnId LIMIT 1',
							':psnId', $psnId
						);
					}
					$updatePatient = R::Model('imed.patient.get', $psnId);
					$ret = $updatePatient->{$code}[$insertId];
				}
				break;

			case 'tran.remove':
				if ($tranId) {
					mydb::query(
						'DELETE FROM %imed_tr% WHERE `pid` = :psnId AND `tr_id` = :tranId LIMIT 1',
						':psnId', $psnId,
						':tranId', $tranId
					);
				} else {
					$ret = message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'Invalid request']);
				}
				break;

			case 'carer.add':
				mydb::query(
					'INSERT INTO %imed_tr%
					(`pid`, `uid`, `tr_code`, `cat_id`, `status`, `created`)
					VALUES
					(:pid, :uid, :tr_code, :cat_id, :status, :created)',
					':pid', $psnId,
					':uid', i()->uid,
					':tr_code', 'carer',
					':cat_id', 'func.NULL' ,
					':status',SG\getFirst(imed_model::get_category('carerstate',NULL,NULL,'default'),'func.NULL'),
					':created', date('U')
				);
				break;

			case 'carer.remove':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %imed_tr% WHERE `tr_id` = :tr_id LIMIT 1',
						':tr_id', $tranId
					);
				}
				break;

			case 'admit.backhome':
				if ($tranId = post('tranId')) {
					mydb::query(
						'UPDATE %db_person% SET `admit` = NULL WHERE `psnid` = :psnId LIMIT 1',
						':psnId', $psnId
					);
					mydb::query(
						'UPDATE %imed_tr% SET `detail4` = :backDate WHERE `tr_id` = :tranId AND `pid` = :psnId LIMIT 1',
						[
							'tranId' => $tranId,
							'psnId' => $psnId,
							'backDate' => sg_date(post('backDate'), 'Y-m-d'),
						]
					);
				}
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>