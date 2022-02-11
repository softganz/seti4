<?php
/**
* Project Planning View Detail
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $tranId
* @return String
*/
class ProjectPlanningInfoProblemForm extends Page {
	var $planInfo;

	function __construct($planInfo) {
		$this->planInfo = $planInfo;
	}

	function build() {
		if (!($tpid = $this->planInfo->tpid)) return message('error', 'PROCESS ERROR');

		$tagname = 'info';

		$ret = '';

		R::View('project.toolbar',$self,$this->planInfo->title, 'planning', $this->planInfo,'{showPrint:true}');

		$ret .= '<header class="header">'._HEADER_BACK.'<h3>สถานการณ์ปัญหา</h3></header>';

		$form = new Form('problem', url('project/planning/'.$tpid.'/info/problem.save'), NULL, 'sg-form');
		$form->addData('checkValid', true);
		$form->addData('rel', 'notify');
		$form->addData('done', 'close | load:#main:'.url('project/planning/'.$tpid, ['mode' => 'edit']));

		$form->addField(
			'problemname',
			array(
				'type' => 'text',
				'label' => 'สถานการณ์ปัญหา',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ระบุสถานการณ์ปัญหา เช่น ร้อยละประชาชนสูบบุหรี่',
			)
		);

		$form->addField(
			'problemsize',
			array(
				'type' => 'text',
				'label' => 'ขนาดปัญหา (จำนวนตามหน่วยของสถานการณ์ปัญหา)',
				'class' => '-numeric',
				'require' => true,
				'placeholder' => '0.00',
			)
		);

		$form->addField(
			'objective',
			array(
				'type' => 'text',
				'label' => 'วัตถุประสงค์',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ระบุวัตถุประสงค์ เช่น เพื่อลดจำนวนประชาชนสูบบุหรี่',
			)
		);

		$form->addField(
			'indicator',
			array(
				'type' => 'text',
				'label' => 'ตัวชี้วัด',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ระบุตัวชี้วัด เช่น ร้อยละจำนวนประชาชนสูบบุหรี่(ร้อยละ)',
			)
		);

		$form->addField(
			'targetsize',
			array(
				'type' => 'text',
				'label' => 'เป้าหมาย 1 ปี (จำนวนตามหน่วยของขนาดปัญหา)',
				'class' => '-numeric',
				'require' => true,
				'placeholder' => '0.00',
			)
		);

		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

		$ret .= $form->build();

		//$ret .= print_o($this->planInfo, '$this->planInfo');

		return $ret;
	}
}
?>