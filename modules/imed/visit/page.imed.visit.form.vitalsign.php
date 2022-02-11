<?php
/**
* iMed :: Virtal Sign Survey Form
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @param Object $visitInfo
* @return String
*
* @usage imed/visit/{psnId}/form.vitalsign/{seqId}
*/

$debug = true;

class ImedVisitFormVitalsign {
	var $patientInfo;
	var $visitInfo;

	function __construct($patientInfo, $visitInfo) {
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seq;

		$isEdit = is_admin('imed') || $this->visitInfo->uid == i()->uid;

		if (!$isEdit) return message('error','Access denied');

		return new Container([
			'children' => [
				'<header class="header -box -hidden">'._HEADER_BACK.'<h3>สัญญาณชีพ</h3></header>',
			new Form([
				'variable' => 'data',
				'action' => url('imed/api/visit/'.$psnId.'/vitalsign.save/'.$seqId),
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'load:#imed-visit-'.$seqId.':'.url('imed/visit/'.$psnId.'/item/'.$seqId,array('ref'=>post('ref'))).' | close',
				'children' => [
					'seq' => ['type'=>'hidden','value'=>$seqId],
					'ref' => ['type'=>'hidden','name'=>'ref','value'=>post('ref')],
					'weight' => [
						'label'=>'น้ำหนัก (ก.ก.)',
						'type'=>'text',
						'class'=>'-fill',
						'value' => $this->visitInfo->weight,
					],
					'height' => [
						'label' => 'ส่วนสูง (ซ.ม.)',
						'type' => 'text',
						'class' => '-fill',
						'value' => $this->visitInfo->height,
					],
					'temperature' => [
						'label'=>'อุณหภูมิ (C)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->temperature,
					],
					'pulse' => [
						'label'=>'ชีพจร (ครั้ง/นาที)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->pulse,
					],
					'respiratoryrate' => [
						'label'=>'อัตราการหายใจ (ครั้ง/นาที)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->respiratoryrate,
					],
					'sbp' => [
						'label'=>'{tr:Systolic Blood Pressure} - SBP (มม.ปรอท)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->sbp,
					],
					'dbp' => [
						'label'=>'{tr:Diastolic Blood Pressure} - DBP (มม.ปรอท)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->dbp,
					],
					'fbs' => [
						'label'=>'{tr:Fasting Blood Sugar} - FBS (mg/dL)',
						'type'=>'text',
						'class'=>'-fill',
						'value'=>$this->visitInfo->fbs,
					],
					'save' => [
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
						'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
						'container' => array('class'=>'-sg-text-right'),
					],
				], // children
			]), // Form
			], // children
		]);
	}
}
?>