<?php
/**
* Project :: Planning Problem Form
* Created 2021-08-23
* Modify  2022-02-13
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/planning/{id}/info.problem.form
*/

class ProjectPlanningInfoProblemForm extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $this->projectInfo->info->isEdit,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);
		else if (!$this->right->edit) return new ErrorMessage(['code' => _HTTP_ERROR_UNAUTHORIZED, 'text' => 'Access Denied']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สถานการณ์ปัญหา',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'variable' => 'problem',
				'action' => url('project/planning/info/api/'.$this->projectId.'/problem.save'),
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'close | load:#main:'.url('project/planning/'.$this->projectId, ['mode' => 'edit']),
				'children' => [
					'problemname' => [
						'type' => 'text',
						'label' => 'สถานการณ์ปัญหา',
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'ระบุสถานการณ์ปัญหา เช่น ร้อยละประชาชนสูบบุหรี่',
					],
					'problemsize' => [
						'type' => 'text',
						'label' => 'ขนาดปัญหา (จำนวนตามหน่วยของสถานการณ์ปัญหา)',
						'class' => '-numeric',
						'require' => true,
						'placeholder' => '0.00',
					],
					'objective' => [
						'type' => 'text',
						'label' => 'วัตถุประสงค์',
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'ระบุวัตถุประสงค์ เช่น เพื่อลดจำนวนประชาชนสูบบุหรี่',
					],
					'indicator' => [
						'type' => 'text',
						'label' => 'ตัวชี้วัด',
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'ระบุตัวชี้วัด เช่น ร้อยละจำนวนประชาชนสูบบุหรี่(ร้อยละ)',
					],
					'targetsize' => [
						'type' => 'text',
						'label' => 'เป้าหมาย 1 ปี (จำนวนตามหน่วยของขนาดปัญหา)',
						'class' => '-numeric',
						'require' => true,
						'placeholder' => '0.00',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>