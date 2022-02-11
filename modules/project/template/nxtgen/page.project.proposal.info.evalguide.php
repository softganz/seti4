<?php
/**
* Project :: Proposal Eval Guide Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.evalguide
*/

$debug = true;

class ProjectProposalInfoEvalguide extends Page {
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
				'id' => 'propoject-proposal-info-evalguide',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'bigdata::guideEval',
							'fld' => 'guideEval',
							'label' => '7.1 แนวทางการปรับปรุงกระบวนการในระหว่างการจัดการเรียนการสอน',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุแนวทางการปรับปรุงกระบวนการในระหว่างการจัดการเรียนการสอน"}',
							'value' => trim($this->proposalInfo->data['guideEval']),
						],
						nl2br($this->proposalInfo->data['guideEval']),
						$this->editMode,
						'textarea'
					),
					view::inlineedit(
						[
							'group' => 'bigdata::longterm',
							'fld' => 'longterm',
							'label' => '7.2 แผนการพัฒนาคุณภาพการจัดหลักสูตรอย่างต่อเนื่อง',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุแผนการพัฒนาคุณภาพการจัดหลักสูตรอย่างต่อเนื่อง"}',
							'value' => trim($this->proposalInfo->data['longterm']),
						],
						nl2br($this->proposalInfo->data['longterm']),
						$this->editMode,
						'textarea'
					),
				], // children
			]), // Container,
		]);
	}
}
?>