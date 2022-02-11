<?php
/**
* Project calendar
*
* @param Object $self
* @param Object $psnId
* @param String $action
* @param Int $tranId
* @return String
*/
function imed_care_calendar($self, $psnId, $action = NULL, $tranId = NULL) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	list($year,$month)=explode('-',date('Y-m'));

	$nav .= '<nav class="nav -submodule -calendar">';

	$ui = new Ui(NULL,'ui-nav -calendar');

	$ui->add('<a class="" href="'.url('calendar',NULL,'prev').'"><i class="icon -back"></i></a>','{class: "-nav -prev"}');
	$ui->add('<a class="" href="'.url('calendar',NULL,'next').'"><i class="icon -forward"></i></a>','{class: "-nav -next"}');
	$ui->add('<a id="calendar-today" href="'.url('calendar',NULL,'today').'" title="Today">'.tr('Today').'</a>','{class: "-nav -today"}');

	$ui->add('<a><strong id="calendar-current-month">'.sg_date('ดดด ปปปป').'</strong></a>','{class: "-current -month"}');

	$ui->add('<a href="'.url('calendar',NULL,'week').'" title="Week">'.tr('Week').'</a>','{class: "-list -week"}');
	$ui->add('<a href="'.url('calendar',NULL,'month').'" title="Month">'.tr('Month').'</a>','{class: "-list -month"}');
	$ui->add('<a href="'.url('calendar',NULL,'agenda').'" title="Agenda">'.tr('Agenda','แผนงาน').'</a>','{class: "-list -agenda"}');
	// Add Refresh link when delete complete, will refresh current month
	$ui->add('<a id="calendar-refresh" href="'.url('calendar',NULL,'refresh').'" title="Refresh"></a>','{class: "-list -refresh"}');

	$nav .= $ui->build();
	$nav .= '</nav>';

	$ret .= $nav;

	$ret.='<div id="calendar-body" class="sg-load" data-psnid="'.$psnId.'" data-module="imed" data-url="'.url('calendar').'">'.R::Page('calendar', NULL).'</div>'._NL;
	cfg('page_id','imed');

	$ret .= '<script type="text/javascript">
	$.getScript("/calendar/js.calendar.js", function(){})
	</script>';

	//head('calendar.js','<script type="text/javascript" src="/calendar/js.calendar.js"></script>');
	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>