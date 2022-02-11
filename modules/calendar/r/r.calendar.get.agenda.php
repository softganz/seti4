<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_calendar_get_agenda($year='',$month='') {
	$para=para(func_get_args(),2);

	$isAdd=user_access('administer calendars,create calendar content');

	$currentYear = date('Y');
	$currentMon = date('m');
	$currentDay = date('d');

	# if date is not specified
	# assume current date
	if ((!$month || !$year) || $month<1 || $year<1 || $month>12) {
		$year = $currentYear;
		$month = $currentMon;
	}
	$year=intval($year);
	$month=intval($month);

	$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

	// get month event list
	$even_para=isset($para->_src)?'/'.$para->_src:'';
	$para->from=date($year.'-'.sprintf('%02d',$month).'-01');
	$monthItems = R::Model('calendar.get.list',NULL,$para);

	$tables = new Table();
	$tables->addClass('calendar-agenda');
	//		$tables->thead=array('วัน','เวลา','รายการ');
	foreach ($monthItems as $date=>$dayItems) {
		unset($row);
		$row[]='<strong>'.sg_date($date,'ววว j ดด ปป').'</strong>';
		foreach ($dayItems as $i=>$rs) {
			if ($i!=0) {
				unset($row);
				$row[]='';
			}
			$row[]=substr($rs['from_time'],0,5).' น.'.($rs['to_time']!=$rs['from_time']?' - '.substr($rs['to_time'],0,5).' น.':'');
			$row[]='<strong>กิจกรรม : '.$rs['title'].'</strong>'. ($rs['topicTitle']?'<br />โครงการ : '.$rs['topicTitle'].'':'');
			$tables->rows[]=$row;
		}
	}
	$ret .= $tables->build();
	return $ret;
}
?>