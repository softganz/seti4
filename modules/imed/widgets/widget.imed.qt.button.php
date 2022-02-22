<?php
/**
* iMed Widget :: Visit QT Button List
* Created 2021-09-03
* Modify  2021-09-03
*
* @param Array $args
* @return Widget
*
* @usage new ImedQtButtonWidget([])
*/

$debug = true;

class ImedQtButtonWidget extends Widget {
	var $psnId;
	var $seqId;
	var $qtKey;
	var $refApp;
	var $formDone;
	var $firebaseUpdate = true;

	function build() {
		// debugMsg($this, '$this');
		if ($this->qtKey) return $this->_renderChildren($this->qtKey);

		if ($this->refApp == 'psyc') {
			$qtChildren = ['2Q', 'ISPSYC', 'SMIV', 'PVSS', 'MARS', 'DST', 'ADL', 'CUADL', 'FallRisk'];
		} else {
			$qtChildren = ['ADL', '2Q', 'CUADL', 'FallRisk', 'DST', 'MARS', 'ISPSYC', 'SMIV', 'PVSS', ];
		}

		return new Widget([
			'children' => [
				new Ui([
					'container' => '{tag: "nav", class: "nav imed-visit-qt-menu -app-menu -fill"}',
					'children' => (function($qtChildren) {
						$widgets = [];
						foreach ($qtChildren as $item) {
							$widgets[] = $this->_renderChildren($item);
						}
						return $widgets;
					})($qtChildren), // children
				]), // Ui
			],
		]);
	}

	function _renderChildren($childrens = [], $args = []) {
		$qtInfo = $this->items($childrens);
		return '<a class="sg-action imed-visit-qt-'.strtolower($childrens).'" href="'.url($qtInfo['url'], ['ref' => $this->refApp, 'formDone' => $this->formDone, 'fb' => $this->firebaseUpdate ? NULL : 'no']).'" data-rel="box" data-width="full">'.($qtInfo['icon'] ? $qtInfo['icon'] : '').'<span class="lang-text -th">'.$qtInfo['title'].'</span></a>';
	}

	function items($index = NULL) {
		$qtList = [
			'ADL' => [
				'title' => 'ดัชนีบาร์เธล (Barthel ADL index)',
				'url' => 'imed/visit/'.$this->psnId.'/form.barthel/'.$this->seqId,
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'2Q' => [
				'title' => 'แบบประเมินภาวะซึมเศร้า (2Q/9Q)',
				'url' => 'imed/visit/'.$this->psnId.'/form.depress/'.$this->seqId,
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'CUADL' => [
				'title' => 'การประเมินความสามารถเชิงปฏิบัติการดัชนีจุฬาเอดีแอล (Chula ADL Index)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ChulaADL',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'FallRisk' => [
				'title' => 'ประเมินความเสี่ยงต่อการหกล้ม',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/FallRisk',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'DST' => [
				'title' => 'แบบคัดกรองภาวะสมองเสื่อมในผู้สูงอายุไทย (DST)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/DST',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			// 'ADR' => [
			// 	'title' => 'แบบบันทึกอาการไม่พึงประสงค์จากการใช้ยา (ADR)',
			// 	'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ADR',
			// ],
			'MARS' => [
				'title' => 'แบบประเมินความร่วมมือในการใช้ยา (MARS)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/MARS',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'ISPSYC' => [
				'title' => 'แบบคัดกรองโรคจิต (ISPSYC)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/ISPSYC',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'SMIV' => [
				'title' => 'แบบติดตามผู้ป่วยจิตเวชที่มีความเสี่ยงสูง (SMI-V)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/SMIV',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
			'PVSS' => [
				'title' => 'แบบประเมินระดับความรุนแรงของความเสี่ยงต่อการก่อความรุนแรง (PVSS)',
				'url' => 'imed/visit/'.$this->psnId.'/form/'.$this->seqId.'/PVSS',
				'icon' => '<i class="icon -material">accessibility</i>',
			],
		];
		return $index ? $qtList[$index] : $qtList;
	}
}
?>