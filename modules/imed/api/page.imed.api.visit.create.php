<?php
/**
* iMed API :: Create Patient Visit Item
* Created 2021-08-17
* Modify  2022-02-20
*
* @param Array $_REQUEST
* @return Widget
*
* @usage imed/api/visit/create
*/

class ImedApiVisitCreate extends Page {
	var $psnId;

	function __construct() {
		$this->psnId = intval(post('psnId'));
	}
	function build() {
		$isCreateVisit = i()->ok;
		if (!$isCreateVisit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'msg' => 'ขออภัย ท่านไม่สามารถเขียนบันทึกการเยี่ยมบ้านได้']);
		else if (!post('msg')) return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'msg' => 'ขออภัย ข้อมูลการเยี่ยมบ้านไม่ครบถ้วน']);

		$result = (Object) [
			'seqId' => NULL,
			'psnId' => $this->psnId,
			'error' => false,
			'msg' => NULL,
		];

		$post = (Object) [
			'seqId' => SG\getFirst(post('seqId'),NULL),
			'psnId' => SG\getFirst(post('psnId'), post('pid')),
			'visitType' => SG\getFirst(post('visitType')),
			'msg' => post('msg'),
			'uid' => i()->uid,
			'timedata' => sg_date(post('timedata')?post('timedata'):date('U'),'U'),
			'created' => date('U'),
			'service' => post('service'),
			'appSrc' => NULL,
			'appAgent' => NULL,
		];

		if (R()->appAgent) {
			$post->appSrc = R()->appAgent->OS;
			$post->appAgent = R()->appAgent->dev.'/'.R()->appAgent->ver.' ('.R()->appAgent->type.';'.R()->appAgent->OS.')';
		} else if (preg_match('/imed\/app/',$_SERVER["HTTP_REFERER"])) {
			$post->appSrc = 'Web App';
			$post->appAgent = 'Web App';
		} else {
			$post->appSrc = 'Web';
			$post->appAgent = 'Web';
		}
		//$ret .= 'SET appSrc = '.$post->appSrc.' '.$post->appAgent.'<br />';

		mydb::query(
			'INSERT INTO %imed_service%
			(`seq`, `pid`, `uid`, `visitType`, `service`, `appSrc`, `appAgent`, `rx`, `timedata`, `created`)
				VALUES
			(:seqId, :psnId, :uid, :visitType, :service, :appSrc, :appAgent, :msg, :timedata, :created)
			ON DUPLICATE KEY UPDATE
			`rx` = :msg
			',
			$post
		);

		if (empty($post->seqId)) $post->seqId = mydb()->insert_id;

		$result->seqId = $post->seqId;
		$result->affected_rows = mydb()->_affected_rows;
		// debugMsg(mydb(),'mydb()');

		// Save service complete
		if (mydb()->_affected_rows == 1) {
			mydb::query(
				'INSERT INTO %imed_patient%
				(`pid`, `uid`, `created`)
				VALUES
				(:psnId, :uid, :created)
				ON DUPLICATE KEY UPDATE
				`service` = `service` + 1',
				$post
			);

			// Send data to https://www.khonsongkhla.com
			import('model:imed.khonsongkhla.php');

			$patientInfo = R::Model('imed.patient.get',$this->psnId);

			if (strlen($patientInfo->info->cid) == 13 && substr($patientInfo->info->areacode,0, 2) == '90') {
				$khonSongkhlaModel = new ImedKhonsongkhlaModel();
				// $khonSongkhlaModel->login();
				// debugMsg($khonSongkhlaModel->refreshToken(), 'refreshToken');
				// debugMsg($khonSongkhlaModel, '$khonSongkhlaModel');
				// debugMsg($this,'$this');



				$data = (Object) [
					'cid' => $patientInfo->info->cid,
					'date' => sg_date($post->timedata, 'ปปปป-m-d'),
					'socialActivity' => 'elder_care',
					'source' => 'scf',
					'serviceUnit' => 'scf',
					'description' => $post->msg,
				];

				$result->khonsongkhla = $khonSongkhlaModel->addPublicService($data);
				// debugMsg($result, '$result');

				// $khonSongkhlaModel->deletePublicService(['cid' => $this->patientInfo->info->cid, 'id' => 12]);

				// debugMsg($khonSongkhlaModel->getPublicServiceList($this->patientInfo->info->cid), '$aid');
			}


		}

		$result->msg = 'บันทีกการเยี่ยมบ้านเรียบร้อย';

		return $result;
	}
}
?>