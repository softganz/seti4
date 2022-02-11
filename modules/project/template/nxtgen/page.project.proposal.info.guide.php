<?php
/**
* Project :: Proposal Guide Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.guide
*/

$debug = true;

class ProjectProposalInfoGuide extends Page {
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
				'id' => 'propoject-proposal-info-guide',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'bigdata::guideContent',
							'fld' => 'guideContent',
							'label' => '4.1 แนวทางการกำหนดเนื้อหาสาระและโครงสร้างหลักสูตรที่สอดคล้องกับผลการเรียนรู้',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุแนวทางการกำหนดเนื้อหาสาระและโครงสร้างหลักสูตรที่สอดคล้องกับผลการเรียนรู้"}',
							'value' => trim($this->proposalInfo->data['guideContent']),
						],
						nl2br($this->proposalInfo->data['guideContent']),
						$this->editMode,
						'textarea'
					),
					view::inlineedit(
						[
							'group' => 'bigdata::guideLearn',
							'fld' => 'guideLearn',
							'label' => '4.2 แนวทางการจัดการเรียนการสอนที่ต้องร่วมมือกับสถานประกอบการอย่างเข้มข้น',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุแนวทางการกำหนดเนื้อหาสาระและโครงสร้างหลักสูตรที่สอดคล้องกับผลการเรียนรู้"}',
							'value' => trim($this->proposalInfo->data['guideLearn']),
						],
						nl2br($this->proposalInfo->data['guideLearn']),
						$this->editMode,
						'textarea'
					),
				], // children
			]), // Container,
		]);
	}
}
?>