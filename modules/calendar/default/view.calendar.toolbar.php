<?php
	/**
	 * Module toolbar
	 *
	 * @param Record Set $calInfo
	 * @param Array $para
	 * @return String
	 */

$debug = true;

function view_calendar_toolbar($self, $calInfo=NULL,$options = '{}') {
	//$ret.='<div id="search" class="search-box -hidden">&nbsp;</div>';

	$self->theme->title = 'Calendar';

	$ret.='<nav class="nav -submodule -calendar">';
	$ui=new Ui(NULL,'ui-nav -calendar');
	$ui->add('<a class="" href="'.url('calendar',NULL,'prev').'"><i class="icon -material">navigate_before</i></a>','{class: "-nav -prev"}');
	$ui->add('<a class="" href="'.url('calendar',NULL,'next').'"><i class="icon -material">navigate_next</i></a>','{class: "-nav -next"}');
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



	// Show calendar room reservation menu
	if (property('calendar.room:title') && user_access('access calendar rooms')) {
		$ui->add('<a href="'.url('calendar/room').'">รายการจองห้องประชุม</a>','{class: "-room"}');
		if (user_access('create calendar room content')) {
			$ui->add('<a href="'.url('calendar/room/post').'" title="ลงรายการขอจองใช้ห้องประชุม">ขอจองห้องประชุม</a>','{class: "-room -post"}');
			$ui->add('<a href="'.url('calendar/room/report').'" title="รายงาน">รายงาน</a>','{class: "-room -report"}');
		}

		if ($calInfo->resvid) {
			if (user_access('access calendar rooms')) $ui->add('<a href="'.url('calendar/room/info/'.$calInfo->resvid).'">รายละเอียด</a>','{class: "-room -resv"}');
			if (user_access('administer calendar rooms','edit own calendar room content',$calInfo->uid)) {
				$ui->add('<a href="'.url('calendar/room/edit/'.$calInfo->resvid).'">แก้ไขรายละเอียด</a>','{class: "-room -edit"}');
			}
		}
	}
	$ret.=$ui->build();
	$ret.='</nav>';

	$self->theme->toolbar = $ret;

	return $ret;
}
?>