<?php
/**
* Project :: Proposal Assessment Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.assessment
*/

$debug = true;

class ProjectProposalInfoAssessment extends Page {
	var $projectId;
	var $editMode;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->editMode = $this->proposalInfo->editMode;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
			]),
			'body' => new Container([
				'id' => 'propoject-proposal-info-assessment',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'bigdata::assessment',
							'fld' => 'assessment',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุการประเมินผลหรือวิธีการวัด"}',
							'value' => trim($this->proposalInfo->data['assessment']),
						],
						nl2br($this->proposalInfo->data['assessment']),
						$this->editMode,
						'textarea'
					),
				], // children
			]), // Container,
		]);
	}
}
?>