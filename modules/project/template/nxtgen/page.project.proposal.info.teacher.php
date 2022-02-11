<?php
/**
* Project :: Proposal Teacher Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.teacher
*/

$debug = true;

class ProjectProposalInfoTeacher extends Page {
	var $projectId;
	// var $editMode;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		// $this->editMode = $this->proposalInfo->editMode;
		$this->right = (Object) [
			'editMode' => ($this->proposalInfo->RIGHT & _IS_EDITABLE) && (SG\getFirst($this->proposalInfo->editMode, post('mode') === 'edit')),
		];

	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->teacher,
			]),
			'body' => new Container([
				'id' => 'propoject-proposal-info-guide',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'bigdata::guideTeacher',
							'fld' => 'guideTeacher',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุแสดงแนวทางการพัฒนาอาจารย์"}',
							'value' => trim($this->proposalInfo->data['guideTeacher']),
						],
						nl2br($this->proposalInfo->data['guideTeacher']),
						$this->right->editMode,
						'textarea'
					),
				], // children
			]), // Container,
		]);
	}
}
?>