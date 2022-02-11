<?php
/**
* Project :: Research Information
* Created 2022-01-24
* Modify  2022-01-24
*
* @param Object $projectInfo
* @return Widget
*
* @usage proejct/{id}/info.research
*/

class ProjectInfoResearchNew extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'editable' => $projectInfo->info->isRight,
			'edit' => $projectInfo->info->isRight,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);
		else if (!$this->right->edit) return new ErrorMessage(['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'ขออภัย!!! ท่านไม่สามารถเพิ่มงานวิจัยได้']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'งานวิจัย',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('project/info/api/'.$this->projectId.'/research.add'),
						'class' => 'sg-form',
						// 'rel' => 'notify',
						// 'done' => 'close',
						'checkValid' => true,
						'children' => [
							'title' => [
								'label' => 'ชื่องานวิจัย',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}'
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}
}
?>