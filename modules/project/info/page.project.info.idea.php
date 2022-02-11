<?php
/**
* Project :: Follow Idea Information
* Created 2021-10-27
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.idea
*/

$debug = true;

class ProjectInfoIdea extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$basicInfo = reset(SG\getFirst(project_model::get_tr($this->projectId, 'info:basic')->items['basic'], []));

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Container([
				'children' => [
					view::inlineedit(
						[
							'group' => 'info:basic',
							'fld' => 'text6',
							'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => ['placeholder' => 'บรรยายกรอบแนวคิดและยุทธศาสตร์หลักเพิ่มเติมได้ในช่องนี้',]
						],
						$basicInfo->text6,
						$isEdit,
						'textarea'
					),
				], // children
			]), // Container
		]);
	}
}
?>