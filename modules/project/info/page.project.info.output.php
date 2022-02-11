<?php
/**
* Project :: Follow Output Information
* Created 2021-10-26
* Modify  2021-10-26
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.output
*/

$debug = true;

class ProjectInfoOutput extends Page {
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
							'fld' => 'text5',
							'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => [
								'class' => '-fill',
								'placeholder' => 'ระบุผลที่คาดว่าจะได้รับเป็นข้อๆ',
							],
						],
						$basicInfo->text5,
						$isEdit,
						'textarea'
					),
				], // children
			]), // Container
		]);
	}
}
?>