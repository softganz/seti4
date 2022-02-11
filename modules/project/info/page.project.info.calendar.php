<?php
/**
* Project :: Follow Calendar Information
* Created 2021-05-31
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.calendar
*/

import('widget:project.info.appbar.php');

$debug = true;

class ProjectInfoCalendar extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		head('calendar.js','<script type="text/javascript" src="/calendar/js.calendar.js"></script>');

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Container([
							'tagName' => 'nav',
							'class' => 'nav -submodule -calendar',
							'children' => [
								new Ui([
									'type' => 'nav',
									'class' => '-calendar -sg-paddingnorm',
									'children' => [
										[
											'text' => '<a class="" href="'.url('calendar',NULL,'prev').'"><i class="icon -back"></i></a>',
											'options' => '{class: "-nav -prev"}'
										],
										[
											'text' => '<a class="" href="'.url('calendar',NULL,'next').'"><i class="icon -forward"></i></a>',
											'options' => '{class: "-nav -next"}'
										],
										[
											'text' => '<a id="calendar-today" href="'.url('calendar',NULL,'today').'" title="Today">'.tr('Today').'</a>',
											'options' => '{class: "-nav -today"}'
										],
										[
											'text' => '<a><strong id="calendar-current-month">'.sg_date('ดดด ปปปป').'</strong></a>',
											'options' => '{class: "-current -month"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'day').'" title="Day">'.tr('Day').'</a>',
											'options' => '{class: "-list -day"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'week').'" title="Week">'.tr('Week').'</a>',
											'options' => '{class: "-list -week"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'month').'" title="Month">'.tr('Month').'</a>',
											'options' => '{class: "-list -month"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'next7day').'" title="Next 7 days">'.tr('Next 7 days','7 วัน').'</a>',
											'options' => '{class: "-list -day7"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'year').'" title="Year">'.tr('Year','ปี').'</a>',
											'options' => '{class: "-list -year"}'
										],
										[
											'text' => '<a href="'.url('calendar',NULL,'agenda').'" title="Agenda">'.tr('Agenda','แผนงาน').'</a>',
											'options' => '{class: "-list -agenda"}'
										],
										// Add Refresh link when delete complete, will refresh current month
										[
											'text' => '<a id="calendar-refresh" href="'.url('calendar',NULL,'refresh').'" title="Refresh"></a>',
											'options' => '{class: "-list -refresh"}'
										],
									], // children
								]), // Ui
							], // children
						]), // Nav
					 ]), // ScrollView

					'<div id="calendar-body" class="sg-load" data-tpid="'.$this->projectId.'" data-module="project" data-url="'.url('calendar').'"></div>',
				],
			]),
		]);
	}
}
?>