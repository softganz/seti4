<?php
/**
* Project :: Change Organization
* Created 2021-04-25
* Modify  2021-09-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org
*/

$debug = true;

class ProjectInfoOrgChange extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return message('error', 'PROCESS ERROR');

		$isEdit = $this->projectInfo->right->isEdit;

		if (!$isEdit) return false;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ย้ายองค์กร',
				'boxHeader' => true,
			]),
			'body' => new Form([
				'action' => url('project/'.$this->projectId.'/info/org.move'),
				'id' => 'project-info-org',
				'class' => 'sg-form project-info-org',
				'rel' => 'none',
				'done' => 'close',
				'children' => [
					'org' => [
						'type' => 'select',
						'label' => 'ย้ายโครงการไปอยู่ภายใต้องค์กร:',
						'class' => '-fill',
						'value' => $this->projectInfo->orgId,
						'options' => mydb::select('SELECT `orgid`, `name` FROM %db_org% ORDER BY CONVERT(`name` USING tis620); -- {key: "orgid", value: "name"}')->items,
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>