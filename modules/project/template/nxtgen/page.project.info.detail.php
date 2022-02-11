<?php
/**
* Project :: Follow Detail Information
* Created 2021-10-26
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.detail
*/

$debug = true;

class ProjectInfoDetail extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$projectInfo = $this->projectInfo;

		$isWebAdmin = is_admin();
		$isAdmin = $this->projectInfo->info->isAdmin;
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$isEditDetail = $this->projectInfo->info->isEditDetail;
		$isInEditMode = $isEdit;
		$lockReportDate = $this->projectInfo->info->lockReportDate;

		$showBudget = $this->projectInfo->is->showBudget;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => 'item__card',
						'colgroup' => ['width="30%"','width="70%"'],
						'children' => [
							[
								'ชื่อหลักสูตร'.($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
								'<strong>'
								. view::inlineedit(
									[
										'group' => 'topic',
										'fld' => 'title',
										'options' => ['class' => '-fill'],
									],
									$projectInfo->title,
									$isAdmin || ($isEditDetail && $projectConfig->follow->ownerEditTitle)
								)
								. '</strong>'
								. ($isEditDetail && $projectConfig->follow->ownerEditTitle ? '<span class="form-required" style="margin-left:-16px;">*</span>' : '')
							],

							['สถาบันการศึกษา','<a href="'.url('org/'.$projectInfo->orgId).'">'.$projectInfo->info->orgName.'</a>'],

							$projectInfo->info->projectset_name ? [
								'ภายใต้หลักสูตร',
								'<a href="'.url('project/'.$projectInfo->info->projectset).'">'.$projectInfo->info->projectset_name.'</a>'
							] : NULL,

							$showBudget ? [
								'งบประมาณ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'budget',
										'ret' => 'money'
									],
									$projectInfo->info->budget,
									false,
									'money'
								)
								. ' บาท'.($isEdit ? ' <span class="form-required">*</span>' : '')
								. ($isWebAdmin ? '<a href="'.url('project/'.$this->projectId.'/info.nxt.period').'"><i class="icon -material">edit</i></a>' : '')
							] : NULL,

							// ['เลขที่ข้อตกลง', $this->projectInfo->info->agrno],
							['รหัสโครงการ', $this->projectInfo->info->prid],
							['ปีงบประมาณ', $this->projectInfo->info->pryear+543],
							['วันที่อนุมัติ', $this->projectInfo->info->date_approve ? sg_date($this->projectInfo->info->date_approve,'ว ดดด ปปปป') : ''],
							[
								'ระยะเวลาดำเนินงาน',
								($this->projectInfo->info->date_from ? sg_date($this->projectInfo->info->date_from,'ว ดดด ปปปป') : '')
								. ' - '
								. ($this->projectInfo->info->date_end ? sg_date($this->projectInfo->info->date_end,'ว ดดด ปปปป') : '')
							],
							// [
							// 	'หน่วยงานที่รับผิดชอบ',
							// 	view::inlineedit(
							// 		[
							// 			'group' => 'project',
							// 			'fld' => 'orgnamedo',
							// 			'options' => ['class' => '-fill'],
							// 		],
							// 		$projectInfo->info->orgnamedo,
							// 		$isEditDetail
							// 	)
							// ],

							// [
							// 	'ผู้รับผิดชอบหลักสูตร',
							// 	view::inlineedit(
							// 		[
							// 			'group' => 'project',
							// 			'fld' => 'prowner',
							// 			'options' => ['class' => '-fill'],
							// 		],
							// 		$projectInfo->info->prowner,
							// 		$isEdit
							// 	)
							// ],

							// [
							// 	'พื้นที่ดำเนินการ',
							// 	view::inlineedit(
							// 		[
							// 			'group' => 'project',
							// 			'fld' => 'area',
							// 			'areacode' => $projectInfo->info->areacode,
							// 			'options' => [
							// 				'class' => '-fill',
							// 				'autocomplete' => [
							// 					'target' => 'areacode',
							// 					'query' => url('api/address'),
							// 					'minlength' => 5
							// 				]
							// 			],
							// 		],
							// 		SG\getFirst($projectInfo->info->area,$projectInfo->info->areaName),
							// 		$isEditDetail,
							// 		'autocomplete'
							// 	)
							// ],

							[
								'ละติจูด-ลองจิจูด',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'location',
										'options' => ['class' => 'project-info-latlng -fill']
									],
									($projectInfo->info->location ? $projectInfo->info->lat.','.$projectInfo->info->lnt : ''),
									$isEdit
								)
								. '<a class="sg-action project-pin" href="'.url('project/'.$this->projectId.'/info.map').'" data-rel="box" data-width="full" style="position: absolute;margin-left: -24px; margin-top: 6px;"><i class="icon -pin"></i></a>'
							],

						], // children
					]), // Table
				],
			]),
		]);
	}
}
?>