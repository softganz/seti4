<?php
/**
* Project :: Proposal Student Information
* Created 2021-11-03
* Modify  2021-11-15
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.student
*/

$debug = true;

class ProjectProposalInfoStudent extends Page {
	var $projectId;
	var $editMode;
	var $totalSerie;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->editMode = SG\getFirst($this->proposalInfo->editMode, post('mode') == 'edit');
	}

	function build() {
		$isDegree = $this->proposalInfo->parentId == cfg('project')->nxt->degreeId;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
			]),
			'body' => new Container([
				'id' => 'propoject-proposal-info-student',
				'class' => 'propoject-proposal-info-student section -box',
				'children' => [
					$isDegree ? $this->degreeStudentPlanning() : $this->nonDegreeStudentPlanning(),
					'<style type="text/css">
					.propoject-proposal-info-student .inline-edit-field.-numeric {width: 40px; min-width: 40px;}
					</style>',
				], // children
			]), // Container,
		]);
	}

	function degreeStudentPlanning() {
		$planInfo = mydb::select(
			'SELECT ROUND(`num1`) `studentPerLot`, ROUND(`num3`) `year`
			FROM %project_tr%
			WHERE `tpid` = :projectId AND `formid` = "develop" AND `part` = "studentPlan" LIMIT 1',
			[':projectId' => $this->projectId]
		);
		$planInfo = mydb::select(
			'SELECT
			`trid`
			, `formid`, `part`
			, `detail1` `yearStart`
			, ROUND(`num1`) `studentPerLot`
			, ROUND(`num2`)+1 `lotPerYear`
			, ROUND(`num3`) `year`
			, ROUND(`num4`) `hourAll`
			, ROUND(`num5`) `hourType1`
			, ROUND(`num6`) `hourType2`
			, ROUND(`num7`) `totalSerie`
			, `detail2` `learnType`
			FROM %project_tr%
			WHERE `tpid` = :projectId AND `formid` = "develop" AND `part` = "studentPlan" LIMIT 1',
			[':projectId' => $this->projectId]
		);
		// debugMsg($planInfo);

		$targetList = mydb::select(
			'SELECT
			tag.`catid`, tag.`name`, target.*
			FROM %tag% tag
				LEFT JOIN %project_target% target ON target.`tgtid` = tag.`catid` AND target.`tpid` = :projectId AND target.`tagname` = "develop"
			WHERE tag.`taggroup` = "project:target" AND tag.`catparent` = 1',
			[':projectId' => $this->projectId]
		)->items;

		$this->totalSerie = $planInfo->totalSerie;
		if ($this->totalSerie > cfg('project')->nxt->maxSerie) $this->totalSerie = cfg('project')->nxt->maxSerie;

		return new Column([
			'id' => 'section-2',
			'class' => 'section -box',
			'children' => [
				// new DebugMsg($planInfo, '$planInfo'),
				'2.1 จำนวนนักศึกษาต่อปีการศึกษา '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num1',
						'tr' => $planInfo->trid,
						'options' => '{class: "-numeric",ret: "numeric", placeholder: "?"}',
						'value' => $planInfo->studentPerLot,
					],
					$planInfo->studentPerLot,
					$this->editMode,
					'text'
				)
				. ' คน (ต้องสอดคล้องกับเอกสารหลักสูตร (มคอ 2) ด้วย)<br />',

				'2.2 ปีการศึกษาในการเปิดสอน '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'detail1',
						'tr' => $planInfo->trid,
						'options' => [
							'placeholder' => '?',
						],
						'value' => $planInfo->yearStart ? $planInfo->yearStart : NULL,
					],
					$planInfo->yearStart ? $planInfo->yearStart+543 : NULL,
					$this->editMode,
					'select',
					[2019 => 2562, 2020 => 2563, 2021 => 2564, 2022 => 2565]
				),

				'2.3 ระยะเวลาในการดำเนินการ '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num3',
						'tr' => $planInfo->trid,
						'options' => [
							'class' => '-numeric',
							'ret' => 'numeric',
							'placeholder' => '?',
						],
						'value' => $planInfo->year,
					],
					$planInfo->year,
					$this->editMode,
					'select',
					'1,2,3,4,5,6,7,8'
				)
				. ' ปี',

				'2.4 จำนวนรุ่นในการจัดการศึกษา '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num7',
						'tr' => $planInfo->trid,
						'options' => [
							'class' => '-numeric',
							'ret' => 'numeric',
							'placeholder' => '?',
							'done' => 'load->replace:#propoject-proposal-info-student:'.url('project/proposal/'.$this->projectId.'/info.student', ['mode' => 'edit']),
						],
						'value' => $planInfo->totalSerie,
					],
					$planInfo->totalSerie,
					$this->editMode,
					'select',
					'1,2,3'
				)
				. ' รุ่น!',

				// '2.4 รูปแบบการศึกษา '
				// . view::inlineedit(
				// 	[
				// 		'group' => 'tr:develop:studentPlan',
				// 		'fld' => 'detail2',
				// 		'tr' => $planInfo->trid,
				// 		'options' => [
				// 			'placeholder' => '?',
				// 		],
				// 		'value' => $planInfo->learnType,
				// 	],
				// 	$planInfo->learnType,
				// 	$this->editMode,
				// 	'select',
				// 	['บัณฑิตศึกษา' => 'บัณฑิตศึกษา', '4 ปี' => '4 ปี', 'ต่อเนื่อง' => 'ต่อเนื่อง', 'เทียบโอน' => 'เทียบโอน']
				// )
				// . ' ปี',

				'2.5 กลุ่มเป้าหมายที่เข้ารับการศึกษา',
				new ScrollView([
					'child' => new Table([
						'class' => 'student-estimate',
						'caption' => 'ประมาณการณ์จำนวน นศ.',
						'thead' => (function() {
							$widgets = ['กลุ่มเป้าหมายที่เข้ารับการศึกษา'];
							for ($serie = 1; $serie <= $this->totalSerie; $serie++) {
								$widgets[] = 'รุ่น '.$serie;
							}
							return $widgets;
						})(),
						'children' => (function($targetList, $planInfo) {
							$rows = [];
							foreach ($targetList as $targetItem) {
								$row = [$targetItem->name];
								for ($serie = 1; $serie <= $this->totalSerie; $serie++) {
									$field = 'expectind'.$serie;
									$targetAmount = round($targetItem->{$field});
									$row[$serie+1] = view::inlineedit(
										[
											'group' => 'target::'.$targetItem->catid,
											'fld' => $field,
											'tgtid' => $targetItem->catid,
											'value' => $targetAmount,
											'options' => ['class' => '-numeric'],
										],
										$targetAmount,
										$this->editMode,
										'text'
									);
								}
								$rows[] = $row;
							}
							return $rows;
						})($targetList, $planInfo),
					]), // Table
				]), // ScrollView
			], // children
		]); // Container
	}

	function nonDegreeStudentPlanning() {
		$planInfo = mydb::select(
			'SELECT
			`trid`
			, `formid`, `part`
			, `detail1` `yearStart`
			, ROUND(`num1`) `studentPerLot`
			, ROUND(`num2`) `lotPerYear`
			, ROUND(`num3`) `year`
			, ROUND(`num4`) `hourAll`
			, ROUND(`num5`) `hourType1`
			, ROUND(`num6`) `hourType2`
			, ROUND(`num7`) `totalSerie`
			, `detail2` `learnType`
			FROM %project_tr%
			WHERE `tpid` = :projectId AND `formid` = "develop" AND `part` = "studentPlan" LIMIT 1',
			[':projectId' => $this->projectId]
		);
		// debugMsg($planInfo);

		$targetList = mydb::select(
			'SELECT
			tag.`catid`, tag.`name`, target.*
			FROM %tag% tag
				LEFT JOIN %project_target% target ON target.`tgtid` = tag.`catid` AND target.`tpid` = :projectId AND target.`tagname` = "develop"
			WHERE tag.`taggroup` = "project:target" AND tag.`catparent` = 1',
			[':projectId' => $this->projectId]
		)->items;

		$this->totalSerie = $planInfo->totalSerie;
		if ($this->totalSerie > cfg('project')->nxt->maxSerie) $this->totalSerie = cfg('project')->nxt->maxSerie;

		return new Column([
			'id' => 'section-2',
			'class' => 'section -box',
			'children' => [
				'2.1 จำนวนนักศึกษาต่อรุ่น '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num1',
						'tr' => $planInfo->trid,
						'options' => '{class: "-numeric",ret: "numeric", placeholder: "?"}',
						'value' => $planInfo->studentPerLot,
					],
					$planInfo->studentPerLot,
					$this->editMode,
					'text'
				)
				. ' คน จำนวน '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num2',
						'tr' => $planInfo->trid,
						'options' => [
							'class' => '-numeric',
							'ret' => 'numeric',
							'placeholder' => '?',
						],
						'value' => $planInfo->lotPerYear,
					],
					$planInfo->lotPerYear,
					$this->editMode,
					'select',
					'1,2,3'
				)
				. ' รุ่นต่อปี',

				'2.2 ระยะเวลาในการจัดการศึกษา '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num3',
						'tr' => $planInfo->trid,
						'options' => [
							'class' => '-numeric',
							'ret' => 'numeric',
							'placeholder' => '?',
						],
						'value' => $planInfo->year,
					],
					$planInfo->year,
					$this->editMode,
					'select',
					'1,2,3'
				)
				. ' ปี',

				'2.3 จำนวนชั่วโมงในการดำเนินการ '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num4',
						'tr' => $planInfo->trid,
						'options' => '{class: "-numeric",ret: "numeric", placeholder: "?"}',
						'value' => $planInfo->hourAll,
					],
					$planInfo->hourAll,
					$this->editMode,
					'text'
				)
				. ' ชม. (ทฤษฎี '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num5',
						'tr' => $planInfo->trid,
						'options' => '{class: "-numeric",ret: "numeric", placeholder: "?"}',
						'value' => $planInfo->hourType1,
					],
					$planInfo->hourType1,
					$this->editMode,
					'text'
				)
				. ' ชม. , ปฏิบัติ '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num6',
						'tr' => $planInfo->trid,
						'options' => '{class: "-numeric",ret: "numeric", placeholder: "?"}',
						'value' => $planInfo->hourType2,
					],
					$planInfo->hourType2,
					$this->editMode,
					'text'
				)
				. ' ชม. )',

				'2.4 จำนวนรุ่นในการจัดการศึกษา '
				. view::inlineedit(
					[
						'group' => 'tr:develop:studentPlan',
						'fld' => 'num7',
						'tr' => $planInfo->trid,
						'options' => [
							'class' => '-numeric',
							'ret' => 'numeric',
							'placeholder' => '?',
							'done' => 'load->replace:#propoject-proposal-info-student:'.url('project/proposal/'.$this->projectId.'/info.student', ['mode' => 'edit']),
						],
						'value' => $planInfo->totalSerie,
					],
					$planInfo->totalSerie,
					$this->editMode,
					'select',
					'1,2,3'
				)
				. ' รุ่น!',

				'2.5 กลุ่มเป้าหมายที่เข้ารับการศึกษา',
				new ScrollView([
					'child' => new Table([
						'class' => 'student-estimate',
						'caption' => 'ประมาณการณ์จำนวน นศ.',
						'thead' => (function() {
							$widgets = ['กลุ่มเป้าหมายที่เข้ารับการศึกษา'];
							for ($serie = 1; $serie <= $this->totalSerie; $serie++) {
								$widgets[] = 'รุ่น '.$serie;
							}
							return $widgets;
						})(),
						'children' => (function($targetList, $planInfo) {
							$rows = [];
							foreach ($targetList as $targetItem) {
								$row = [$targetItem->name];
								for ($serie = 1; $serie <= $this->totalSerie; $serie++) {
									$field = 'expectind'.$serie;
									$targetAmount = round($targetItem->{$field});
									$row[$serie+1] = view::inlineedit(
										[
											'group' => 'target::'.$targetItem->catid,
											'fld' => $field,
											'tgtid' => $targetItem->catid,
											'value' => $targetAmount,
											'options' => ['class' => '-numeric'],
										],
										$targetAmount,
										$this->editMode,
										'text'
									);
								}
								$rows[] = $row;
							}
							return $rows;
						})($targetList, $planInfo),
					]), // Table
				]), // ScrollView

				// new Nav([
				// 	'mainAxisAlignment' => 'end',
				// 	'class' => '-sg-paddingmore',
				// 	'children' => [
				// 		'<a class="btn" href=""><i class="icon -material">add_circle</i><span>เพิ่มรุ่นนักศึกษา</span></a>'
				// 	], // children
				// ]),
				// new DebugMsg($planInfo, '$planInfo'),
				// new DebugMsg($targetList, '$targetList'),

				'<p>* สำหรับ Non-Degree เกณฑ์การพิจารณาเบื้องต้นไม่น้อยกว่า 9 หน่วยกิต โดยกำหนดเงื่อนไขการพิจารณาต้องจัดการเรียนการสอนไม่ต่ำกว่า 285 ชั่วโมง : ทฤษฎี 60 ชั่วโมง และปฏิบัติงานในสถานประกอบการ 225 ชั่วโมง</p>',
				'<p class="-no-print">* จำนวนรุ่นสูงสุดที่รองรับคือ <b>'.cfg('project')->nxt->maxSerie.'</b> รุ่น</p>',
			], // children
		]); // Container
	}
}
?>