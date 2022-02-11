<?php
/**
* Project calendar information
*
* @param Object $self
* @param Object/Integer $projectInfo
* @param String $action
* @param Integer $calid
* @return String
*/
function org_calendar($self, $orgInfo) {
	$orgId = $orgInfo->orgid;
	if (!$orgId) return message('error', 'PROCESS ERROR');


	$isEdit = $projectInfo->info->isEdit;
	$isEditDetail = $projectInfo->info->isEditDetail;

	R::View('org.toolbar',$self,'Info', NULL, $orgInfo);

	list($year,$month)=explode('-',date('Y-m'));

	$nav .= '<nav class="nav -submodule -calendar">';

	$ui = new Ui(NULL,'ui-nav -calendar');

	$ui->add('<a class="" href="'.url('calendar',NULL,'prev').'"><i class="icon -back"></i></a>','{class: "-nav -prev"}');
	$ui->add('<a class="" href="'.url('calendar',NULL,'next').'"><i class="icon -forward"></i></a>','{class: "-nav -next"}');
	$ui->add('<a id="calendar-today" href="'.url('calendar',NULL,'today').'" title="Today">'.tr('Today').'</a>','{class: "-nav -today"}');

	$ui->add('<a><strong id="calendar-current-month">'.sg_date('ดดด ปปปป').'</strong></a>','{class: "-current -month"}');

	$ui->add('<a href="'.url('calendar',NULL,'day').'" title="Day">'.tr('Day').'</a>','{class: "-list -day"}');
	$ui->add('<a href="'.url('calendar',NULL,'week').'" title="Week">'.tr('Week').'</a>','{class: "-list -week"}');
	$ui->add('<a href="'.url('calendar',NULL,'month').'" title="Month">'.tr('Month').'</a>','{class: "-list -month"}');
	$ui->add('<a href="'.url('calendar',NULL,'next7day').'" title="Next 7 days">'.tr('Next 7 days','7 วัน').'</a>','{class: "-list -day7"}');
	$ui->add('<a href="'.url('calendar',NULL,'year').'" title="Year">'.tr('Year','ปี').'</a>','{class: "-list -year"}');
	$ui->add('<a href="'.url('calendar',NULL,'agenda').'" title="Agenda">'.tr('Agenda','แผนงาน').'</a>','{class: "-list -agenda"}');
	// Add Refresh link when delete complete, will refresh current month
	$ui->add('<a id="calendar-refresh" href="'.url('calendar',NULL,'refresh').'" title="Refresh"></a>','{class: "-list -refresh"}');

	$nav .= $ui->build();
	$nav .= '</nav>';

	$self->theme->toolbar .= $nav;

	$ret.='<div id="calendar-body" class="sg-load" data-orgid="'.$orgId.'" data-url="'.url('calendar').'" data-add="false"></div>'._NL;
	cfg('page_id','project');

	head('calendar.js','<script type="text/javascript" src="/calendar/js.calendar.js"></script>');
	return $ret;
}
?>