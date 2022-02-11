<?php
/**
* iMed :: All Visit Survey Form
* Created 2020-12-11
* Modify  2021-05-28
*
* @param Object $patientInfo
* @param Object $visitInfo
* @return String
*
* @usage imed/visit/{id}/qt
*/

$debug = true;

import('widget:imed.qt.button');

class ImedVisitQt extends Page {
	var $psnId;
	var $seqId;
	var $refApp;
	var $formDone;
	var $patientInfo;
	var $visitInfo;

	function __construct($patientInfo, $visitInfo = NULL) {
		$this->psnId = $patientInfo->psnId;
		$this->seqId = $visitInfo->seqId;
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
		$this->refApp = post('ref');
	}

	function build() {
		// Data Model
		$psnId = $this->patientInfo->psnId;
		$seqId = $this->visitInfo->seq;

		if (!i()->ok) return message('error', 'Access Denied');

		// if ($this->refApp == 'psyc') {
		// 	$qtChildren = ['2Q', 'ISPSYC', 'SMIV', 'PVSS', 'MARS', 'DST', 'ADL', 'CUADL', 'FallRisk'];
		// } else {
		// 	$qtChildren = ['ADL', '2Q', 'CUADL', 'FallRisk', 'DST', 'MARS', 'ISPSYC', 'SMIV', 'PVSS', ];
		// }

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แบบบันทึกข้อมูล',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new ImedQtButtonWidget([
				'psnId' => $this->psnId,
				'seqId' => $this->seqId,
				'refApp' => $this->refApp,
				'formDone' => $this->formDone,
			]), // ImedVisitQt
		]);
				// '<header class="header">'._HEADER_BACK.'<h3>แบบบันทึกข้อมูล</h3></header>',

		return new Widget([
			'children' => [
				R()->appAgent ? '' : '<header class="header">'._HEADER_BACK.'<h3>แบบบันทึกข้อมูล</h3></header>',
				new Ui([
					'container' => '{tag: "nav", class: "nav imed-visit-qt-menu -app-menu -fill"}',
					'children' => (function($qtChildren) {
						$widgets = [];
						foreach ($qtChildren as $item) {
							$widgets[] = $this->_qtChildren($item);
						}
						return $widgets;
					})($qtChildren), // children
				]), // Ui
			],
		]);
	}

	function _qtChildren($index) {
		$qtList = [
			'ADL' => '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form.barthel/'.$this->seqId, ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="ดัชนีบาร์เธล (Barthel Index)"><i class="icon -material">accessibility</i><span>ดัชนีบาร์เธล (Barthel Index)</span></a>',
			'2Q' => '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form.depress/'.$this->seqId, ['formDone' => $this->formDone]).'" data-rel="box" data-width="480" data-webview="แบบประเมินภาวะซึมเศร้า"><i class="icon -material">accessibility_new</i><span>แบบประเมินภาวะซึมเศร้า (2Q/9Q)</span></a>',
			'CUADL' => [
				'text' => '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ChulaADL', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="การประเมินความสามารถเชิงปฏิบัติการดัชนีจุฬาเอดีแอล (Chula ADL Index)" title="การประเมินความสามารถเชิงปฏิบัติการดัชนีจุฬาเอดีแอล (Chula ADL Index)"><i class="icon -material">accessibility_new</i><span>การประเมินความสามารถเชิงปฏิบัติการดัชนีจุฬาเอดีแอล (Chula ADL Index)</span></a>',
				'options' => '{id: "qt-chulaadl"}'
			],
			'FallRisk' => [
				'text' => '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/FallRisk', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="ประเมินความเสี่ยงต่อการหกล้ม" title="ประเมินความเสี่ยงต่อการหกล้ม"><i class="icon -material">accessibility_new</i><span>ประเมินความเสี่ยงต่อการหกล้ม</span></a>', 'options' => '{id: "qt-fallrisk"}',
			],
			'DST' => [
				'text' => '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/DST', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="แบบคัดกรองภาวะสมองเสื่อมในผู้สูงอายุไทย (DST)" title="Dementia Screening Test (DST)"><i class="icon -material">accessibility_new</i><span>แบบคัดกรองภาวะสมองเสื่อมในผู้สูงอายุไทย (DST)</span></a>',
				'options' => '{id: "qt-dst"}',
			],
			// '<a class="sg-action -fill -disabled" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ADR').'" data-rel="box" data-width="480" data-webview="แบบบันทึกอาการไม่พึงประสงค์จากการใช้ยา"><i class="icon -material">accessibility_new</i><span class="lang-text -th">แบบบันทึกอาการไม่พึงประสงค์จากการใช้ยา (ADR)</span></a>',
			'MARS' => '<a class="sg-action -fill" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/MARS', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="แบบประเมินความร่วมมือในการใช้ยา"><i class="icon -material">accessibility_new</i><span class="lang-text -th">แบบประเมินความร่วมมือในการใช้ยา (MARS)</span></a>',
			'ISPSYC' => '<a class="sg-action -fill" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ISPSYC', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="แบบคัดกรองโรคจิต"><i class="icon -material">accessibility_new</i><span class="lang-text -th">แบบคัดกรองโรคจิต (ISPSYC)</span></a>',
			'SMIV' => '<a class="sg-action -fill" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/SMIV', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="แบบติดตามผู้ป่วยจิตเวชที่มีความเสี่ยงสูง"><i class="icon -material">accessibility_new</i><span class="lang-text -th">แบบติดตามผู้ป่วยจิตเวชที่มีความเสี่ยงสูง (SMI-V)</span></a>',
			'PVSS' => '<a class="sg-action -fill" href="'.url('imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/PVSS', ['formDone' => $this->formDone]).'" data-rel="box" data-width="full" data-webview="แบบประเมินระดับความรุนแรงของความเสี่ยงต่อการก่อความรุนแรง"><i class="icon -material">accessibility_new</i><span class="lang-text -th">แบบประเมินระดับความรุนแรงของความเสี่ยงต่อการก่อความรุนแรง (PVSS)</span></a>',
		];
		return $qtList[$index];
	}


}
?>