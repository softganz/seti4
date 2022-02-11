<?php
/**
* imed :: Patient Information Model Controller
* Created 2020-12-21
* Modify  2020-12-21
*
* @param Object $self
* @param Int $psnInfo
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

// @deprecared => use imed/api/patient/{psnId}/{action}/{tranId}

function imed_patient_info($self, $psnInfo = NULL, $action = NULL, $tranId = NULL) {
	return R::Page('imed.api.patient', $psnInfo->psnId, $action, $tranId);



	// if (!($psnId = $psnInfo->psnId)) return message('error', 'PROCESS ERROR');

	// // Olny user has right to access
	// $isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	// $isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	// if (!$isEdit) return message('error', 'Access Denied');

	// $ret = '';

	// switch ($action) {
	// 	case 'dead':
	// 		// Set imed_disabled => discharge = 26, dischargedate = date
	// 		// Set db_person => dischar = 1, ddisch = date
	// 		// co_discharge => 1 = ตาย
	// 		// co_category => 26 = ตาย
	// 		if (post('date')) {
	// 			$stmt = 'UPDATE %db_person% SET `dischar` = 1, `ddisch` = :ddisch WHERE `psnid` = :psnid LIMIT 1';
	// 				$ret .= 'บันทึกการเสียชีวิตเรียบร้อย';
	// 			mydb::query($stmt, ':psnid', $psnId, ':ddisch', sg_date(post('date'),'Y-m-d'));
	// 			//$ret .= mydb()->_query;
	// 			$psnInfo->person->dead->course = post('dead');
	// 			//$ret .= print_o($psnInfo->person, '$psnInfo->person');
	// 			R::Model('imed.person.save', $psnId, $psnInfo->person);
	// 			/*
	// 			if ($psnInfo->info->dischar == 1) {
	// 				$stmt = 'UPDATE %db_person% SET `dischar` = NULL, `ddisch` = NULL WHERE `psnid` = :psnid LIMIT 1';
	// 				$ret .= 'ยกเลิกบันทึกการเสียชีวิตเรียบร้อย';
	// 			} else {
	// 				$stmt = 'UPDATE %db_person% SET `dischar` = 1, `ddisch` = :ddisch WHERE `psnid` = :psnid LIMIT 1';
	// 				$ret .= 'บันทึกการเสียชีวิตเรียบร้อย';
	// 			}
	// 			mydb::query($stmt, ':psnid', $psnId, ':ddisch', date('Y-m-d'));
	// 			*/
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'delete':
	// 		if ($isEdit && SG\confirm()) {
	// 			$result = PatientModel::delete($psnId);
	// 			//$ret .= print_o($result, '$result');
	// 			$ret .= $result->error ? $result->error : 'Delete patient complete';
	// 		}
	// 		break;

	// 	case 'dead.cancel':
	// 		if (SG\confirm()) {
	// 			$stmt = 'UPDATE %db_person% SET `dischar` = NULL, `ddisch` = NULL WHERE `psnid` = :psnid LIMIT 1';
	// 			mydb::query($stmt, ':psnid', $psnId);
	// 			$ret .= 'ยกเลิกบันทึกการเสียชีวิตเรียบร้อย';
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'disabled.add':
	// 		if ($isEdit) {
	// 			$stmt = 'INSERT INTO %imed_disabled% (`pid`, `uid`, `created`) VALUES (:psnid, :uid, :created) ON DUPLICATE KEY UPDATE `pid` = :psnid';
	// 			mydb::query($stmt, ':psnid', $psnId, ':uid', i()->uid, ':created', date('U'));
	// 			//$ret .= mydb()->_query;
	// 			if (post('defect')) {
	// 				$stmt = 'INSERT INTO %imed_disabled_defect% (`pid`, `defect`) VALUES (:psnid, :defect) ON DUPLICATE KEY UPDATE `defect` = :defect';
	// 				mydb::query($stmt, ':psnid', $psnId, ':defect', post('defect'));
	// 				//$ret .= mydb()->_query;
	// 			}

	// 			$stmt = 'INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created) ON DUPLICATE KEY UPDATE `careid` = :careid';
	// 			mydb::query($stmt, ':pid',$psnId, ':careid',_IMED_CARE_DISABLED, ':uid',i()->uid, ':created', date('U'));
	// 			//$ret .= mydb()->_query;
	// 			$ret .= 'เพิ่มประเภทความพิการเรียบร้อย';
	// 		}
	// 		break;

	// 	case 'disabled.remove':
	// 		if ($isEdit) {
	// 			$stmt = 'DELETE FROM %imed_disabled% WHERE `pid` = :psnid LIMIT 1';
	// 			mydb::query($stmt, ':psnid', $psnId);

	// 			$stmt = 'DELETE FROM %imed_disabled_defect% WHERE `pid` = :psnid';
	// 			mydb::query($stmt, ':psnid', $psnId);

	// 			$stmt = 'DELETE FROM %imed_care% WHERE `pid` = :psnid AND `careid`=:careid LIMIT 1';
	// 			mydb::query($stmt,':psnid',$psnId,':careid',_IMED_CARE_DISABLED);
	// 		}
	// 		break;

	// 	case 'disabled.defect.remove':
	// 		if ($isEdit && $tranId && SG\confirm()) {
	// 			$defect_id = intval($tranId);
	// 			mydb::query('DELETE FROM %imed_disabled_defect% WHERE `pid`=:pid AND `defect`=:defect LIMIT 1',':pid',$psnId,':defect',$defect_id);
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'rehab.add':
	// 		if ($isEdit) {
	// 			$stmt = 'INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created) ON DUPLICATE KEY UPDATE `careid` = :careid';
	// 			mydb::query($stmt, ':pid',$psnId, ':careid',_IMED_CARE_REHAB, ':uid',i()->uid, ':created', date('U'));
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'rehab.remove':
	// 		if ($isEdit) {
	// 			$stmt = 'DELETE FROM %imed_care% WHERE `pid` = :psnid AND `careid`=:careid LIMIT 1';
	// 			mydb::query($stmt,':psnid',$psnId,':careid',_IMED_CARE_REHAB);
	// 		}
	// 		break;

	// 	case 'elder.add':
	// 		if ($isEdit) {
	// 			mydb::query('INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created) ON DUPLICATE KEY UPDATE `careid` = :careid', ':pid',$psnId, ':careid',_IMED_CARE_ELDER, ':uid',i()->uid, ':created', date('U'));
	// 		}
	// 		break;

	// 	case 'elder.remove':
	// 		if ($isEdit) {
	// 			mydb::query('DELETE FROM %imed_care% WHERE `pid`=:pid AND `careid`=:careid LIMIT 1',':pid',$psnId,':careid',_IMED_CARE_ELDER);
	// 		}
	// 		break;

	// 	case 'photo.upload':
	// 		$photo = (object)$_FILES['photo'];

	// 		if (is_uploaded_file($photo->tmp_name)) {
	// 			$photo->name = 'profile-'.$psnId.'.jpg';
	// 			$photo->overwrite = true;
	// 			$result = R::Model('photo.save', $photo, 'upload/imed/');
	// 			if ($result->complete && $result->save->_file) {
	// 				$ret .= '<img src="'.imed_model::patient_photo($psnId).'" width="100%" height="100%" />';
	// 			}
	// 		}
	// 		//$ret .= print_o($result,'$result');
	// 		break;

	// 	case 'gis.save':
	// 		// Save GIS
	// 		//$ret .= print_o(post(),'post()');
	// 		//return (object) post();
	// 		if ($getLoc = post('location')) {
	// 			//$ret .= 'SAVE GIS '.post('loc').'<br />';

	// 			$data = new stdClass;
	// 			$data->gis = SG\getFirst($psnInfo->info->gis);
	// 			$data->psnId = $psnId;
	// 			$data->uid = i()->uid;
	// 			$data->table = 'sgz_db_person';
	// 			$data->latlng = 'func.POINT('.$getLoc.')';
	// 			$data->created = date('U');

	// 			$stmt = 'INSERT INTO %gis%
	// 				(`gis`, `table`, `latlng`) VALUES (:gis, :table, :latlng)
	// 				ON DUPLICATE KEY UPDATE `latlng` = :latlng
	// 				';
	// 			mydb::query($stmt, $data);
	// 			//$ret .= mydb()->_query.'<br />';

	// 			if (empty($data->gis)) {
	// 				$data->gis = mydb()->insert_id;
	// 				$stmt = 'UPDATE %db_person% SET `gis` = :gis WHERE `psnid` = :psnId LIMIT 1';
	// 				mydb::query($stmt, $data);
	// 				//$ret .= mydb()->_query.'<br />';
	// 			}

	// 			// Save location history
	// 			mydb::query('INSERT INTO %imed_patient_gis% SET `pid` = :psnId, `uid` = :uid, `latlng` = :latlng, `created` = :created', $data);
	// 			//$ret .= mydb()->_query.'<br />';
	// 			//$log_message='แก้ไข: pid['.$log_name.'] GIS ['.$psnInfo->latlng.'] เป็น ['.$value.']';
	// 			//$ret['value']=$getLoc;
	// 		} else {
	// 			$stmt = 'UPDATE %db_person% SET `gis` = NULL WHERE `psnid` = :psnId LIMIT 1';
	// 			mydb::query($stmt, ':psnId', $psnId);
	// 			//$ret .= mydb()->_query.'<br />';
	// 		}
	// 		break;

	// 	case 'gis.remove':
	// 		// Remove GIS
	// 		if (SG\confirm()) {
	// 			$stmt = 'UPDATE %db_person% SET `gis` = NULL WHERE `psnid` = :psnid LIMIT 1';
	// 			mydb::query($stmt, ':psnid', $psnId);
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'tran.save':
	// 		if ($isEdit) {
	// 			$code = post('code');
	// 			mydb::query(
	// 				'INSERT INTO %imed_tr%
	// 				(`pid`, `uid`, `tr_code`, `cat_id`, `status`, `detail1`, `detail2`, `created`)
	// 				VALUES
	// 				(:pid, :uid, :tr_code, :cat_id, :status, :detail1, :detail2, :created)',
	// 				':pid', $psnId,
	// 				':uid', i()->uid,
	// 				':tr_code', $code,
	// 				':cat_id', post('catid') ,
	// 				':status', post('status'),
	// 				':detail1', post('detail1'),
	// 				':detail2', post('detail2'),
	// 				':created', date('U')
	// 			);
	// 			if (!mydb()->_error) {
	// 				$insertId = mydb()->insert_id;

	// 				if (post('admit')) {
	// 					mydb::query('UPDATE %db_person% SET `admit` = 1 WHERE `psnid` = :psnId LIMIT 1', ':psnId', $psnId);
	// 					// debugMsg(mydb()->_query);
	// 				}
	// 				$updatePatient = R::Model('imed.patient.get', $psnId);
	// 				$ret = $updatePatient->{$code}[$insertId];
	// 				// debugMsg($updateTran, '$updateTran');
	// 			}
	// 			// $ret->query = mydb()->_query;
	// 			// $ret = SG\json_decode($ret);
	// 		}
	// 		break;

	// 	case 'tran.remove':
	// 		if ($isEdit && $tranId) {
	// 			mydb::query('DELETE FROM %imed_tr% WHERE `pid` = :psnId AND `tr_id` = :tranId LIMIT 1', ':psnId', $psnId, ':tranId', $tranId);
	// 		} else {
	// 			$ret = message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'Invalid request']);
	// 		}
	// 		break;

	// 	case 'carer.add':
	// 		if ($isEdit) {
	// 			mydb::query(
	// 				'INSERT INTO %imed_tr% (`pid`, `uid`, `tr_code`, `cat_id`, `status`, `created`) VALUES (:pid, :uid, :tr_code, :cat_id, :status, :created)',
	// 				':pid', $psnId,
	// 				':uid', i()->uid,
	// 				':tr_code', 'carer',
	// 				':cat_id', 'func.NULL' ,
	// 				':status',SG\getFirst(imed_model::get_category('carerstate',NULL,NULL,'default'),'func.NULL'),
	// 				':created', date('U')
	// 			);
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	case 'carer.remove':
	// 		if ($isEdit && $tranId && SG\confirm()) {
	// 			mydb::query('DELETE FROM %imed_tr% WHERE `tr_id` = :tr_id LIMIT 1',':tr_id', $tranId);
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		break;

	// 	default:
	// 		$ret = 'ERROR : ACTION NOT FOUND';
	// 		break;
	// }
	// return $ret;
}
?>