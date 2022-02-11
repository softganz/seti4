<?php
/**
* iMed API :: Create Patient Visit Item
* Created 2021-08-17
* Modify  2021-09-05
*
* @param Array $_REQUEST
* @return Widget
*
* @usage imed/api/visit/create
*/

$debug = true;

class ImedApiVisitCreate extends Page {
	function build() {
		$isCreateVisit = i()->ok;
		if (!$isCreateVisit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'msg' => 'ขออภัย ท่านไม่สามารถเขียนบันทึกการเยี่ยมบ้านได้']);
		else if (!post('msg')) return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'msg' => 'ขออภัย ข้อมูลการเยี่ยมบ้านไม่ครบถ้วน']);

		$result = (Object) [
			'seqId' => NULL,
			'psnId' => intval(post('psnId')),
			'error' => false,
			'msg' => NULL,
		];

		$post->seqId = SG\getFirst(post('seqId'),NULL);
		$post->psnId = SG\getFirst(post('psnId'), post('pid'));
		$post->visitType = SG\getFirst(post('visitType'));
		$post->msg = post('msg');
		$post->uid = i()->uid;
		$post->timedata = sg_date(post('timedata')?post('timedata'):date('U'),'U');
		$post->created = date('U');
		$post->service = post('service');
		//$ret .= print_o(R()->appAgent,'R()->appAgent');
		if (R()->appAgent) {
			$post->appsrc = R()->appAgent->OS;
			$post->appagent = R()->appAgent->dev.'/'.R()->appAgent->ver.' ('.R()->appAgent->type.';'.R()->appAgent->OS.')';
		} else if (preg_match('/imed\/app/',$_SERVER["HTTP_REFERER"])) {
			$post->appsrc = 'Web App';
			$post->appagent = 'Web App';
		} else {
			$post->appsrc = 'Web';
			$post->appagent = 'Web';
		}
		//$ret .= 'SET appsrc = '.$post->appsrc.' '.$post->appagent.'<br />';

		$stmt = 'INSERT INTO %imed_service%
				(`seq`, `pid`, `uid`, `visitType`, `service`, `appsrc`, `appagent`, `rx`, `timedata`, `created`)
					VALUES
				(:seqId, :psnId, :uid, :visitType, :service, :appsrc, :appagent, :msg, :timedata, :created)
				ON DUPLICATE KEY UPDATE
				`rx` = :msg
				';
		mydb::query($stmt, $post);

		if (empty($post->seqId)) $post->seqId = mydb()->insert_id;

		// $ret.=mydb()->_query;

		$result->seqId = $post->seqId;

		// Save service complete
		if (mydb()->affected_rows == 1) {
			$stmt = 'INSERT INTO %imed_patient% (`pid`, `uid`, `created`) VALUES (:psnId, :uid, :created) ON DUPLICATE KEY UPDATE `service` = `service` + 1';
			mydb::query($stmt, $post);
			//$ret .= mydb()->_query;
		}

		$result->msg = 'บันทีกการเยี่ยมบ้านเรียบร้อย';

		// debugMsg($post,'$post');
		// $ret .= '$result = '.$result;
		// $ret .= print_o($dataFB,'$dataFB');
		// $ret .= print_o($result,'$result');
		// $ret .= print_o(post(),'post()');
		return $result;
	}
}
?>