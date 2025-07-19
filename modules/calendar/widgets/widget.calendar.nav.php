<?php
/**
 * Calendar:: Navigator Widget
 * Created :: 2025-07-19
 * Modify  :: 2025-07-19
 * Version :: 1
 *
 * @param Array $calendarInfo
 * @return Object
 *
 * @usage import('widget:calendar.nav.widget.php')
 * @usage new CalendarNavWidgetl([])
 */

class CalendarNavWidget extends Widget {
	function __construct($calendarInfo = NULL) {
		parent::__construct();
	}

	#[\Override]
	function build() {
		return new Nav([
			'class' => '-calendar',
			'children' => [
				new Button([
					'type' => 'link',
					'class' => '-today',
					'href' => url('calendar',NULL,'today'),
					'title' => 'Today',
					'text' => tr('Today')
				]), // Today button
				'<sep>', // Separator
				new Button([
					'type' => 'link',
					'class' => '-prev',
					'href' => url('calendar',NULL,'prev'),
					'icon' => new Icon('navigate_before')
				]), // Previous button
				new Button([
					'type' => 'link',
					'class' => '-next',
					'href' => url('calendar',NULL,'next'),
					'icon' => new Icon('navigate_next')
				]), // Next button

				new Button([
					'type' => 'link',
					'id' => 'calendar-current-month',
					'class' => '-current -month',
					'text' => '<strong>'.sg_date('ดดด ปปปป').'</strong>',
				]), // Current month display

				new Button([
					'type' => 'link',
					'class' => '-list -day',
					'href' => url('calendar',NULL,'day'),
					'title' => 'Day',
					'text' => tr('Day')
				]), // Day view button
				new Button([
					'type' => 'link',
					'class' => '-list -week',
					'href' => url('calendar',NULL,'week'),
					'title' => 'Week',
					'text' => tr('Week')
				]), // Week view button
				new Button([
					'type' => 'link',
					'class' => '-list -month',
					'href' => url('calendar',NULL,'month'),
					'title' => 'Month',
					'text' => tr('Month')
				]), // Month view button
				new Button([
					'type' => 'link',
					'class' => '-list -day7',
					'href' => url('calendar',NULL,'next7day'),
					'title' => 'Next 7 days',
					'text' => tr('Next 7 days','7 วัน')
				]), // Next 7 days button
				new Button([
					'type' => 'link',
					'class' => '-list -year',
					'href' => url('calendar',NULL,'year'),
					'title' => 'Year',
					'text' => tr('Year','ปี')
				]), // Year view button
				new Button([
					'type' => 'link',
					'class' => '-list -agenda',
					'href' => url('calendar',NULL,'agenda'),
					'title' => 'Agenda',
					'text' => tr('Agenda','แผนงาน')
				]), // Agenda view button

				// Add Refresh link when delete complete, will refresh current month
				new Button([
					'type' => 'link',
					'class' => '-list -refresh',
					'href' => url('calendar',NULL,'refresh'),
					'title' => 'Refresh',
					'icon' => new Icon('refresh'),
				]), // Refresh button




				// // Show calendar room reservation menu
				// if (property('calendar.room:title') && user_access('access calendar rooms')) {
				// 	$ui->add('<a href="'.url('calendar/room').'">รายการจองห้องประชุม</a>','{class: "-room"}');
				// 	if (user_access('create calendar room content')) {
				// 		$ui->add('<a class="sg-action" href="'.url('calendar/room/new').'" title="ลงรายการขอจองใช้ห้องประชุม" data-rel="box" data-width="full">ขอจองห้องประชุม</a>','{class: "-room -post"}');
				// 		$ui->add('<a href="'.url('calendar/room/report').'" title="รายงาน">รายงาน</a>','{class: "-room -report"}');
				// 	}

				// 	if ($calInfo->resvid) {
				// 		if (user_access('access calendar rooms')) $ui->add('<a href="'.url('calendar/room/info/'.$calInfo->resvid).'">รายละเอียด</a>','{class: "-room -resv"}');
				// 		if (user_access('administer calendar rooms','edit own calendar room content',$calInfo->uid)) {
				// 			$ui->add('<a href="'.url('calendar/room/edit/'.$calInfo->resvid).'">แก้ไขรายละเอียด</a>','{class: "-room -edit"}');
				// 		}
				// 	}
				// }
			], // children
		]);

		return new Widget([
		]);
	}
}
	/**
	 * Module toolbar
	 *
	 * @param Record Set $calInfo
	 * @param Array $para
	 * @return String
	 */

$debug = true;

function view_calendar_toolbar($calInfo=NULL,$options = '{}') {
	//$ret.='<div id="search" class="search-box -hidden">&nbsp;</div>';

	// $self->theme->title = 'Calendar';

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
			$ui->add('<a class="sg-action" href="'.url('calendar/room/new').'" title="ลงรายการขอจองใช้ห้องประชุม" data-rel="box" data-width="full">ขอจองห้องประชุม</a>','{class: "-room -post"}');
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

	// $self->theme->toolbar = $ret;

	return $ret;
}
?>