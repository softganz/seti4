<?php
/**
* Project :: Proposal Course Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.course
*/

$debug = true;

class ProjectProposalInfoCourse extends Page {
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
				'id' => 'propoject-proposal-info-title',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'bigdata::objective',
							'fld' => 'objective',
							'label' => '3.1 หลักการ เหตุผล และเป้าประสงค์ของหลักสูตร',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุหลักการ เหตุผล"}',
							'value' => trim($this->proposalInfo->data['objective']),
						],
						nl2br($this->proposalInfo->data['objective']),
						$this->editMode,
						'textarea'
					),
					// view::inlineedit(
					// 	[
					// 		'group' => 'bigdata::goal',
					// 		'fld' => 'goal',
					// 		'label' => 'เป้าประสงค์',
					// 		'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุเป้าประสงค์"}',
					// 		'value' => trim($this->proposalInfo->data['goal']),
					// 	],
					// 	nl2br($this->proposalInfo->data['goal']),
					// 	$this->editMode,
					// 	'textarea'
					// ),
					view::inlineedit(
						[
							'group' => 'bigdata::studentSpec',
							'fld' => 'studentSpec',
							'label' => '3.2 วิธีการกำหนดคุณลักษณะของบัณฑิตที่พึ่งประสงค์ ผลการเรียนรู้ที่คาดหวังเมื่อสำเร็จการศึกษา(สอดคล้องกับข้อกำหนดของโครงการ)',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุเป้าประสงค์"}',
							'value' => trim($this->proposalInfo->data['studentSpec']),
						],
						nl2br($this->proposalInfo->data['studentSpec']),
						$this->editMode,
						'textarea'
					),
					view::inlineedit(
						[
							'group' => 'bigdata::companyCo',
							'fld' => 'companyCo',
							'label' => '3.3. แสดงวิธีการร่วมมือระหว่างสถาบันอุดมศึกษากับสถานประกอบการในการจัดร่วมทำหลักสูตรอย่างชัดเจน (Degree)',
							'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุวิธีการร่วมมือระหว่างสถาบันอุดมศึกษากับสถานประกอบการในการจัดร่วมทำหลักสูตรอย่างชัดเจน"}',
							'value' => trim($this->proposalInfo->data['companyCo']),
						],
						nl2br($this->proposalInfo->data['companyCo']),
						$this->editMode,
						'textarea'
					),
				], // children
			]), // Container,
		]);
	}
}
?>