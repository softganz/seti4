<?php
/**
* iMed :: Home Visit Page Controller
* Created 2019-03-11
* Modify  2021-08-30
*
* @param Int $psnId
* @param String $action
* @param Int $seqId
* @return String
*
* @usage imed/api/visit/{psnId}/{action}/{seqId}
*/

$debug = true;

import('model:imed.visit');

class ImedApiVisit extends Page {
	var $psnId;
	var $action;
	var $seqId;
	var $patientInfo;

	function __construct($psnId, $action, $seqId = NULL) {
		$this->patientInfo = R::Model('imed.patient.get',$psnId);
		$this->psnId = $this->patientInfo->psnId;
		$this->action = $action;
		$this->seqId = $seqId;
	}

	function build() {
		// debugMsg('psnId '.$this->psnId.' Action = '.$this->action.' seqId = '.$this->seqId);

		if ($this->psnId && $this->seqId == -1) {
			$this->seqId = $seqId = ($this->_createNewSeq($this->psnId))->seqId;
			// debugMsg('$seqId = '.$seqId);
		}

		$psnId = $this->psnId;
		$seqId = $this->seqId;
		$psnInfo = $this->patientInfo;
		$uid = i()->uid;

		$visitInfo = $psnId && $seqId ? ImedVisitModel::get($psnId, $seqId, '{debug: false}') : NULL;
		$isAccessPatient = $psnInfo->RIGHT & _IS_ACCESS || $visitInfo->uid == $uid;
		$isEdit = is_admin('imed') || $visitInfo->uid == $uid;

		// debugMsg($visitInfo,'$visitInfo');

		if (empty($psnId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลบุคคลตามที่ระบุ']);
		else if ($seqId > 0 && empty($visitInfo)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลเยี่ยมบ้านตามที่ระบุ']);
		else if (!$isAccessPatient) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => $psnInfo->error]);

		$ret = '';

		switch ($this->action) {
			case 'delete':
				if ($seqId && $isEdit && SG\confirm()) {
					$ret .= 'ลบรายการ';
					// Start delete sequence and files relate to this sequence
					$filesDb = mydb::select('SELECT `file` FROM %imed_files% WHERE `seq` = :seq', ':seq', $seqId);
					mydb::query('DELETE FROM %imed_barthel% WHERE `seq` = :seq LIMIT 1', ':seq', $seqId);
					mydb::query('DELETE FROM %imed_need% WHERE `seq` = :seq', ':seq', $seqId);
					mydb::query('DELETE FROM %imed_2q9q% WHERE `seq` = :seq', ':seq', $seqId);
					mydb::query('DELETE q,tr FROM %qtmast% q LEFT JOIN %qttran% tr USING(`qtref`) WHERE q.`seq` = :seq', ':seq', $seqId);
					mydb::query('UPDATE %imed_careplantr% SET `seq` = NULL WHERE `seq` = :seq', ':seq', $seqId);
					mydb::query('DELETE FROM %imed_files% WHERE `seq` = :seq', ':seq', $seqId);
					mydb::query('DELETE FROM %imed_service% WHERE `seq` = :seq LIMIT 1', ':seq', $seqId);

					// Delete file
					foreach ($filesDb->items as $rs) {
						if ($rs->file) unlink('upload/imed/photo/'.$rs->file);
					}
					$ret .= 'เรียบร้อย';
				}
				break;

			case 'photo.upload':
				$data->psnid = $psnId;
				$data->seq = $seqId;
				$data->prename = 'visit_'.$psnId.'_'.date('ymdhis').'_';
				$data->deleteurl = url('imed/api/visit/'.$psnId.'/photo.delete/'.$seqId.'?f=');
				$uploadResult = R::Model('imed.visit.photo.upload',$_FILES['photo'],$data);
				$ret .= $uploadResult['link'];
				//$ret.=print_o($uploadResult,'$uploadResult');
				ImedVisitModel::firebaseChanged($psnId, $seqId);
				return $ret;
				break;

			case 'photo.delete':
				if ($isEdit && post('f') && SG\confirm()) {
					//$ret .= 'Delete '.$seqId;
					$result = R::Model('imed.visit.photo.delete',post('f'));
					//$ret .= print_o($result,'$result');
					ImedVisitModel::firebaseChanged($psnId, $seqId);
				}
				break;

			case 'need.save':
				if (post('needtype')) {
					$data = new stdClass;
					$data->needid = SG\getFirst(post('needid'));
					$data->psnid = $psnId;
					$data->seq = $seqId;
					$data->uid = i()->uid;
					$data->needof = post('needof');
					$data->needtype = post('needtype');
					$data->urgency = post('urgency');
					$data->detail = post('detail',_TRIM);
					$data->created = date('U');
					$stmt = 'INSERT INTO %imed_need%
						(`needid`, `seq`, `psnid`, `uid`, `needof`, `needtype`, `urgency`, `detail`, `created`)
						VALUES
						(:needid, :seq, :psnid, :uid, :needof, :needtype, :urgency, :detail, :created)
						ON DUPLICATE KEY UPDATE
						  `needof` = :needof
						, `needtype` = :needtype
						, `urgency` = :urgency
						, `detail` = :detail
						';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query;

					ImedVisitModel::firebaseChanged($psnId, $seqId);

				}
				break;

			case 'need.status':
				$stmt = 'UPDATE %imed_need% SET `status` = IF(`status` IS NULL,1,NULL) WHERE `seq` = :seq AND `needid` = :needid LIMIT 1';
				mydb::query($stmt, ':seq', $seqId, ':needid', post('id'));

				ImedVisitModel::firebaseChanged($psnId, $seqId);
				break;

			case 'need.delete':
				if (post('id') && SG\confirm()) {
					$stmt = 'DELETE FROM %imed_need% WHERE `needid` = :needid LIMIT 1';
					mydb::query($stmt, ':needid', post('id'));
					//$ret .= mydb()->_query;
					ImedVisitModel::firebaseChanged($psnId, $seqId);
				}
				break;

			case 'need.view':
				$stmt = 'SELECT
						n.*
					, u.`username`, u.`name`, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `patient_name`
					, nt.`name` `needTypeName`
					FROM %imed_need% n
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
					WHERE `needid` = :needid
					LIMIT 1';

				$rs = mydb::select($stmt, ':needid', post('id'));
				//$ret .= print_o($rs,'$rs');

				$ui = new Ui('div','ui-card imed-my-note -need');
				$ui->addId('imed-my-note');

				$ret .= '<div class="ui-item -urgency-'.$rs->urgency.'" id="noteUnit-'.$seqId.'">';
				$ret .= R::View('imed.need.render',$rs, '{page: "'.post('ref').'"}');
				$ret .= '</div>';
				break;

			case 'vitalsign.save':
				$data = (object) post('data', _TRIM);
				if (empty($data->seq)) $data->seq = NULL;
				if (empty($data->weight)) $data->weight = NULL;
				if (empty($data->height)) $data->height = NULL;
				if (empty($data->temperature)) $data->temperature = NULL;
				if (empty($data->pulse)) $data->pulse = NULL;
				if (empty($data->respiratoryrate)) $data->respiratoryrate = NULL;
				if (empty($data->dbp)) $data->dbp = NULL;
				if (empty($data->sbp)) $data->sbp = NULL;
				if (empty($data->fbs)) $data->fbs = NULL;

				$stmt = 'UPDATE %imed_service% SET
					  `weight` = :weight
					, `height` = :height
					, `temperature` = :temperature
					, `pulse` = :pulse
					, `respiratoryrate` = :respiratoryrate
					, `sbp` = :sbp
					, `dbp` = :dbp
					, `fbs` = :fbs
					WHERE `seq` = :seq
					';

				mydb::query($stmt,$data);
				if (empty($data->seq)) $data->seq=mydb()->insert_id;
				//$ret .= mydb()->_query;
				ImedVisitModel::firebaseChanged($psnId, $data->seq);
				break;

			case 'barthel.save':
				$data = (object) post('data');
				$data->seqId = $seqId;
				$data->psnId = $psnId;
				if (!property_exists($data, 'qt01')) $data->qt01=NULL;
				if (!property_exists($data, 'qt02')) $data->qt02=NULL;
				if (!property_exists($data, 'qt03')) $data->qt03=NULL;
				if (!property_exists($data, 'qt04')) $data->qt04=NULL;
				if (!property_exists($data, 'qt05')) $data->qt05=NULL;
				if (!property_exists($data, 'qt06')) $data->qt06=NULL;
				if (!property_exists($data, 'qt07')) $data->qt07=NULL;
				if (!property_exists($data, 'qt08')) $data->qt08=NULL;
				if (!property_exists($data, 'qt09')) $data->qt09=NULL;
				if (!property_exists($data, 'qt10')) $data->qt10=NULL;
				$data->score = $data->qt01+$data->qt02+$data->qt03+$data->qt04+$data->qt05+$data->qt06+$data->qt07+$data->qt08+$data->qt09+$data->qt10;

				$stmt = 'INSERT INTO %imed_barthel%
					(
					  `seq`, `psnId`, `score`
					, `qt01`, `qt02`, `qt03`, `qt04`, `qt05`
					, `qt06`, `qt07`, `qt08`, `qt09`, `qt10`
					)
					VALUES
					(
					  :seqId, :psnId, :score
					, :qt01, :qt02, :qt03, :qt04, :qt05
					, :qt06, :qt07, :qt08, :qt09, :qt10
					)
					ON DUPLICATE KEY UPDATE
					  `score` = :score
					, `qt01` = :qt01, `qt02` = :qt02, `qt03` = :qt03, `qt04` = :qt04, `qt05` = :qt05
					, `qt06` = :qt06, `qt07` = :qt07, `qt08` = :qt08, `qt09` = :qt09, `qt10` = :qt10
					';

				mydb::query($stmt,$data);
				if (empty($data->seqId)) $data->seqId = mydb()->insert_id;
				//$ret .= mydb()->_query;

				//$lastSeq =
				if (empty($data->seqId)) {
					$stmt = 'UPDATE %db_person% SET `adl` = :score WHERE `psnId` = :psnId LIMIT 1';
					mydb::query($stmt, $data);
				} else {
					$stmt = 'UPDATE %db_person% SET
						`adl` = (SELECT `score` FROM %imed_barthel% WHERE `psnId` = :psnId ORDER BY `seq` DESC LIMIT 1)
						WHERE `psnId` = :psnId LIMIT 1';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query;
				}

				$ret = (Object)['seqId' => $data->seqId, 'psnId' => $data->psnId];

				ImedVisitModel::firebaseChanged($psnId, $data->seqId);
				break;

			case 'barthel.delete':
				if ($seqId && $isEdit && SG\confirm()) {
					$stmt = 'DELETE FROM %imed_barthel% WHERE `psnid` = :psnid AND `seq` = :seq LIMIT 1';
					mydb::query($stmt, ':psnid', $psnId, ':seq', $seqId);
					ImedVisitModel::firebaseChanged($psnId, $seqId);
				}
				break;

			case 'depress.save':
				$data = (object) post('data');
				$data->seqId = $seqId;
				$data->psnId = $psnId;
				if (!property_exists($data, 'q2_1')) $data->q2_1=NULL;
				if (!property_exists($data, 'q2_2')) $data->q2_2=NULL;
				if (!property_exists($data, 'q9_1')) $data->q9_1=NULL;
				if (!property_exists($data, 'q9_2')) $data->q9_2=NULL;
				if (!property_exists($data, 'q9_3')) $data->q9_3=NULL;
				if (!property_exists($data, 'q9_4')) $data->q9_4=NULL;
				if (!property_exists($data, 'q9_5')) $data->q9_5=NULL;
				if (!property_exists($data, 'q9_6')) $data->q9_6=NULL;
				if (!property_exists($data, 'q9_7')) $data->q9_7=NULL;
				if (!property_exists($data, 'q9_8')) $data->q9_8=NULL;
				if (!property_exists($data, 'q9_9')) $data->q9_9=NULL;
				if (!property_exists($data, 'q8_1')) $data->q8_1=NULL;
				if (!property_exists($data, 'q8_2')) $data->q8_2=NULL;
				if (!property_exists($data, 'q8_3')) $data->q8_3=NULL;
				if (!property_exists($data, 'q8_4')) $data->q8_4=NULL;
				if (!property_exists($data, 'q8_5')) $data->q8_5=NULL;
				if (!property_exists($data, 'q8_6')) $data->q8_6=NULL;
				if (!property_exists($data, 'q8_7')) $data->q8_7=NULL;
				if (!property_exists($data, 'q8_8')) $data->q8_8=NULL;

				$data->q2_score = $data->q2_1 + $data->q2_2;
				$data->q9_score = $data->q9_1 + $data->q9_2 + $data->q9_3 + $data->q9_4 + $data->q9_5 + $data->q9_6 + $data->q9_7 + $data->q9_8 + $data->q9_9;
				//$data->q8_score = $data->q8_1 + $data->q8_2 + $data->q8_3 + $data->q8_4 + $data->q8_5 + $data->q8_6 + $data->q8_7 + $data->q8_8;
				$data->q8_score = NULL;

				if ($data->q2_score == 0) {
					$data->q9_score = NULL;
					$data->q9_1 = $data->q9_2 = $data->q9_3 = $data->q9_4 = $data->q9_5 = $data->q9_6 = $data->q9_7 = $data->q9_8 = $data->q9_9 = NULL;
				}

				$stmt = 'INSERT INTO %imed_2q9q%
					(
					  `seq`, `psnid`
					, `q2_score`, `q2_1`, `q2_2`
					, `q9_score`, `q9_1`, `q9_2`, `q9_3`, `q9_4`, `q9_5`, `q9_6`, `q9_7`, `q9_8`, `q9_9`
					, `q8_score`, `q8_1`, `q8_2`, `q8_3`, `q8_4`, `q8_5`, `q8_6`, `q8_7`, `q8_8`
					)
					VALUES
					(
					  :seqId, :psnId
					, :q2_score, :q2_1, :q2_2
					, :q9_score, :q9_1, :q9_2, :q9_3, :q9_4, :q9_5, :q9_6, :q9_7, :q9_8, :q9_9
					, :q8_score, :q8_1, :q8_2, :q8_3, :q8_4, :q8_5, :q8_6, :q8_7, :q8_8
					)
					ON DUPLICATE KEY UPDATE
					  `q2_score` = :q2_score, `q2_1` = :q2_1, `q2_2` = :q2_2
					, `q9_score` = :q9_score, `q9_1` = :q9_1, `q9_2` = :q9_2, `q9_3` = :q9_3, `q9_4` = :q9_4, `q9_5` = :q9_5, `q9_6` = :q9_6, `q9_7` = :q9_7, `q9_8` = :q9_8, `q9_9` = :q9_9
					, `q8_score` = :q8_score, `q8_1` = :q8_1, `q8_2` = :q8_2, `q8_3` = :q8_3, `q8_4` = :q8_4, `q8_5` = :q8_5, `q8_6` = :q8_6, `q8_7` = :q8_7, `q8_8` = :q8_8
					';

				mydb::query($stmt,$data);
				if (empty($data->seqId)) $data->seqId = mydb()->insert_id;
				// debugMsg(mydb()->_query);
				$ret = (Object)['seqId' => $data->seqId, 'psnId' => $data->psnId];

				ImedVisitModel::firebaseChanged($psnId, $data->seqId);
				break;

			case 'depress.delete':
				if ($seqId && $isEdit && SG\confirm()) {
					$stmt = 'DELETE FROM %imed_2q9q% WHERE `psnid` = :psnid AND `seq` = :seq LIMIT 1';
					mydb::query($stmt, ':psnid', $psnId, ':seq', $seqId);
					ImedVisitModel::firebaseChanged($psnId, $seqId);
				}
				break;

			case 'qt.save':
				$ret .= 'บันทึกเรียบร้อย';
				$post = (Object) post();
				$data = (Object) post('data');

				$mastData->qtRef = SG\getFirst($post->refid);
				$mastData->uid = i()->uid;
				$mastData->qtdate = date('Y-m-d');
				$mastData->qtgroup = $post->group;
				$mastData->qtform = $post->formid;
				$mastData->orgId = SG\getFirst($post->orgId);
				$mastData->psnId = $psnId;
				$mastData->seqId = $seqId;
				$mastData->value = $post->value;
				$mastData->data = $data;
				$mastData->collectname = i()->name;
				$mastData->created = date('U');

				$result = R::Model('qt.save', $mastData);

				$ret = (Object)['psnId' => $result->psnId, 'seqId' => $result->seqId];

				ImedVisitModel::firebaseChanged($psnId, $mastData->seqId);

				// debugMsg($ret, '$ret');

				/*
				$tranData = new stdClass();
				$tranData->qtref = $mastData->qtref;
				$tranData->part = 'JSON';
				$tranData->value = SG\json_encode($data);
				$tranResult = R::Model('qt.tran.save', $tranData);
				*/

				//$ret .= print_o($tranResult, '$tranResult');

				//$ret .= print_o($tranData,'$tranData');
				//$ret .= print_o(post(),'post()');
				break;

			case 'qt.delete':
				$ret .= 'ขออภัย : ยังไม่สามารถลบแบบสอบถามได้ในขณะนี้';
				break;


			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}

	function _createNewSeq($psnId) {
		$post = (Object) [
			'psnId' => $psnId,
			'msg' => 'ประเมินก่อนเยี่ยมบ้าน',
			'service' => 'Home Visit',
		];
		return ImedVisitModel::create($post, '{debug: false}');
	}
}
?>