<?php
/**
* Project :: Set Detail Information
* Created 2022-02-01
* Modify  2022-02-01
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.detail
*/

$debug = true;

class ProjectSetInfoDetail extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

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
								'ชื่อชุดโครงการ',
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

							$projectInfo->info->projectset_name ? [
								'ภายใต้แผนงาน/ชุดโครงการ',
								'<a href="'.url('project/'.$projectInfo->info->projectset).'">'.$projectInfo->info->projectset_name.'</a>'
							] : NULL,

							cfg('project.option.argno') ? [
								'เลขที่ข้อตกลง',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'agrno'
									],
									$projectInfo->info->agrno,
									$isEdit
								)
							] : NULL,

							[
								'รหัสชุดโครงการ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'prid'
									],
									$projectInfo->info->prid,
									$isEditDetail
								)
							],

							$isAdmin ? [
								'ปีงบประมาณ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'pryear',
										'value' => $projectInfo->info->pryear,
									],
									$projectInfo->info->pryear+543,
									$isEditDetail,
									'select',
									(function() {
										$openYear = SG\getFirst($this->projectInfo->info->pryear,date('Y'));
										$pryearList = [];
										for ($i = $openYear-1; $i <= date('Y')+1; $i++) {
											$pryearList[$i] = $i + 543;
										}
										return $pryearList;
									})()
								).' (เฉพาะแอดมิน)'
							] : ['ปีงบประมาณ', ($projectInfo->info->pryear+543).' (เฉพาะแอดมิน)'],

							[
								'วันที่อนุมัติ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_approve',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $projectInfo->info->date_approve ? sg_date($projectInfo->info->date_approve,'d/m/Y') : ''
									],
									$projectInfo->info->date_approve,
									$isEditDetail,
									'datepicker'
								)
								. ($isEdit?' <span class="form-required">*</span>':'')
							],

							[
								'ระยะเวลาดำเนินโครงการ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_from',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $projectInfo->info->date_from ? sg_date($projectInfo->info->date_from,'d/m/Y') : ''
									],
									$projectInfo->info->date_from,
									$isEdit,
									'datepicker'
								)
								. ' - '
								. view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'date_end',
										'ret' => 'date:ว ดดด ปปปป',
										'value' => $projectInfo->info->date_end ? sg_date($projectInfo->info->date_end,'d/m/Y') : ''
									],
									$projectInfo->info->date_end,
									$isEdit,
									'datepicker'
								)
								.($isEdit ? ' <span class="form-required">*</span>' : '')
							],

							$showBudget ? [
								'งบประมาณ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'budget',
										'ret' => 'money'
									],
									$projectInfo->info->budget,
									$isEditDetail,
									'money'
								)
								. ' บาท'.($isEdit ? ' <span class="form-required">*</span>' : '')
							] : NULL,

							[
								'ชื่อองค์กรที่รับผิดชอบ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'orgnamedo',
										'options' => ['class' => '-fill'],
									],
									$projectInfo->info->orgnamedo,
									$isEditDetail
								)
							],

							[
								'ผู้รับผิดชอบโครงการ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'prowner',
										'options' => ['class' => '-fill'],
									],
									$projectInfo->info->prowner,
									$isEdit
								)
							],

							[
								'พื้นที่ดำเนินการ',
								view::inlineedit(
									[
										'group' => 'project',
										'fld' => 'area',
										'areacode' => $projectInfo->info->areacode,
										'options' => [
											'class' => '-fill',
											'autocomplete' => [
												'target' => 'areacode',
												'query' => url('api/address'),
												'minlength' => 5
											]
										],
									],
									SG\getFirst($projectInfo->info->area,$projectInfo->info->areaName),
									$isEditDetail,
									'autocomplete'
								)
							],

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