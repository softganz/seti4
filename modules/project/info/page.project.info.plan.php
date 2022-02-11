<?php
/**
* Project :: Follow Plan Information
* Created 2021-10-26
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.plan
*/

$debug = true;

class ProjectInfoPlan extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$isEdit = $this->projectInfo->info->isEdit;
		$activityGroupBy = SG\getFirst($_COOKIE['planby'],'tree');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Container([
				'children' => [
					new ScrollView([
						'style' => 'padding: 0 4px; width: calc(100% - 8px);',
						'children' => [
							'<div class="sg-tabs">'._NL
							.'<ul class="tabs">'._NL
							. '<li class="'.($activityGroupBy == 'tree' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info.plan.tree').'" data-rel="replace:#project-plan-list">จำแนกตามกลุ่มกิจกรรม</a></li>'
							. '<li class="'.(empty($activityGroupBy) || $activityGroupBy == 'time' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info.plan.time').'" data-rel="replace:#project-plan-list">จำแนกตามวันที่</a></li>'
							. '<li class="'.($activityGroupBy == 'objective' ? '-active' : '').'"><a class="sg-action" href="'.url('project/'.$this->projectId.'/info.plan.objective').'" data-rel="replace:#project-plan-list">จำแนกตามวัตถุประสงค์</a></li>'
							. '</ul>'._NL,
							new Container([
								'tagName' => 'section',
								'id' => 'project-info-plan',
								'class' => 'project-info-plan',
								'children' => [
									R::PageWidget('project.info.plan.'.$activityGroupBy,[$this->projectInfo]),
								], // children
							]), // Container
							'</div>',
						], // children
					]), // ScrollView

					// บรรยาย
					new Container([
						'children' => [
							view::inlineedit(
								[
									'group' => 'project',
									'fld' => 'activity',
									'ret' => 'html',
									'options' => [
										'class' => '-fill',
										'placeholder' => 'กรณีที่ต้องการบรรยายรายละเอียดวิธีดำเนินการเพิ่มเติม ให้บันทึกไว้ในช่องบรรยายนี้',
									],
								],
								$this->projectInfo->info->activity,
								$isEdit,
								'textarea'
							),
						], // children
					]), // Container
				], // children
			]), // Container
		]);
	}
}
?>