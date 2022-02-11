<?php
/**
* iMed :: Visit Need Form
* Created 2019-03-06
* Modify  2021-05-28
*
* @param Object $patientInfo
* @param Object $visitInfo
* @return String
*
* @usage imed/visit/{psnId}/form.need/{seqId}
*/

$debug = true;

class ImedVisitFormNeed {
	var $patientInfo;
	var $visitInfo;

	function __construct($patientInfo, $visitInfo) {
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seq;

		$needId = post('id');


		if ($needId) {
			$data = mydb::select('SELECT * FROM %imed_need% WHERE `needid` = :needid LIMIT 1', ':needid', $needId);
		}

		return new Container([
			'children' => [
				'<header class="header -box -hidden">'._HEADER_BACK.'<h3>บันทึกความต้องการ</h3></header>'._NL,
				new Form([
					'action' => url('imed/api/visit/'.$psnId.'/need.save/'.$seqId),
					'class' => 'sg-form',
					'checkValid' => true,
					'rel' => 'notify',
					'done' => 'load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId, ['ref' => post('ref')]).' | close',
					'children' => [
						'needid' => ['type'=>'hidden','name'=>'needid','value'=>$data->needid],
						'needof' => [
							'type' => 'select',
							'class' => '-fill',
							'require' => true,
							'options' => ['patient' => 'ความต้องการของผู้ป่วย', 'carer' => 'ความต้องการของผู้ดูแล/ญาติ'],
						],
						'needtype' => [
							'type' => 'select',
							// 'label' => 'ความต้องการ:',
							'class' => '-fill',
							'require' => true,
							'options' => (function() {
								$result = ['' => '== เลือกความต้องการ =='];

								$stmt = 'SELECT c.*, p.`name` `parentName`
									FROM %imed_stkcode% c
										LEFT JOIN %imed_stkcode% p ON p.`stkid` = c.`parent`
									WHERE LEFT(c.`parent`,2) IN ("01","02","03","05","06","99") ORDER BY c.`stkid`';

								foreach (mydb::select($stmt)->items as $value) {
									$result[$value->parentName][$value->stkid] = $value->name;
								}
								return $result;
							})(),
							'value' => $data->needtype,
						],
						'urgency' => [
							'type' => 'select',
							'label' => 'ระดับความเร่งด่วน:',
							'class' => '-fill',
							'options' => array(1 => 'รอได้', 5 => 'เร่งด่วน', 9=> 'เร่งด่วนมาก'),
							'value' => $data->urgency,
						],
						'detail' => [
							'type' => 'textarea',
							'label' => 'รายละเอียด',
							'class' => '-fill',
							'rows' => 4,
							'value' => $data->detail,
							'placeholder' => 'ระบุสภาพปัญหา เช่น ลักษณะบ้าน เคยได้รับการช่วยเหลือหรือไม่',
						],
						'save' => [
							'type' => 'button',
							'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
							'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
							'container' => array('class' => '-sg-text-right'),
						],
					], // children
				]), // Form
			], // children
		]);
	}
}
?>