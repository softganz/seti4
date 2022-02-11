<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_tiny($self, $year = "", $month = "") {
	$para=para(func_get_args(),0);

	// don't user package template
	$self->use_package_template=false;

	$currentYear = date('Y');
	$currentMon = date('m');
	$currentDay = date('d');

	// if date is not specified assume current date
	if ((!$month || !$year) || $month<1 || $year<1 || $month>12) {
		$year = $currentYear;
		$month = $currentMonth;
	}
	$year=intval($year);
	$month=intval($month);

	$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

	// get month event list
	$even_para=isset($para->_src)?'/'.$para->_src:'';
	$month_list = R::Model('calendar.get.list','month='.$year.'-'.sprintf('%02d',$month));
	$day_item=array();
	foreach ($month_list as $date=>$day_items) {
		$day_item[$date] = $date.' : ';
		$day_str = '';
		foreach ($day_items as $item) {
			$day_str .= $item['title'].' , ';
		}
		$day_item[$date] = substr($day_str,0,-3);
	}

	// calculate value of (prev year,prev month,next year and next month)
	$n_month=date("m",mktime(0, 0, 0, $month+1, 1, $year));
	$n_year=date("Y",mktime(0, 0, 0, $month+1, 1,$year));
	$p_month=date("m",mktime(0, 0, 0, $month-1, 1, $year));
	$p_year=date("Y",mktime(0, 0, 0, $month-1, 1, $year));

	$even_title_field = cfg('calendar.month.event_title_field');

	// แสดงเดือนปัจจุบัน
	$month_str = sg_client_convert(array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค","มิ.ย.","ก.ค.","ส.ค.", "ก.ย.","ต.ค.","พ.ย.","ธ.ค."));
	$current_month = getdate(mktime(0,0,0,$month,1,$year));
	$prev_month = getdate(mktime(0,0,0,$month-1,1,$year));
	$next_month = getdate(mktime(0,0,0,$month+1,1,$year));

	$ret .= '<div id="tiny_calendar">'._NL;
	$ret .= '<table class="body">
	<caption><a class="sg-action" href="'.url('calendar/tiny/'.$prev_month['year'].'/'.$prev_month['mon']).'" data-rel="replace:#tiny_calendar">&laquo;</a> &nbsp;'.$month_str[$current_month['mon']-1].' '.($current_month['year']+543).'&nbsp; <a class="sg-action" href="'.url('calendar/tiny/'.$next_month['year'].'/'.$next_month['mon']).'" data-rel="replace:#tiny_calendar">&raquo;</a></caption>'._NL;

	// แสดงชื่อวัน
	$day_array = sg_client_convert(array("อา" , "จ" , "อ" , "พ" , "พฤ" , "ศ" , "ส" ));
	$ret .= '<tr>';
	foreach ( $day_array as $key=>$pic ) $ret .= '<th>'.$pic.'</th>';
	$ret .= '</tr>'._NL;

	$ret .= '<tr>'._NL;
	// calculate unix timestamp for first day of the month
	$first_month_day = getdate (mktime(0, 0, 0, $month, 1, $year));
	$first_month_weekday = $first_month_day["wday"];

	if ( $first_month_weekday == 0 ) $first_month_weekday = 7;

	// calculate last day of the month
	$last_prev_month_day = getdate (mktime(0, 0, 0, $month, 0, $year));
	$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

	// print empty weekdays before first day of the month
	$last_month_day_start=$last_prev_month_day['mday']-$first_month_weekday;
	$row=1;
	if($first_month_weekday != 7){
		for ($weekday = 1; $weekday <= $first_month_weekday; $weekday++) {
			$ret .= '<td class="not_in_month">'.($last_month_day_start+$weekday).'</td>'._NL;
		}
	}
	// print every month days
	for ($mday = 1; $mday <= $last_month_day["mday"]; $mday++) {
		// วันอาทิตย์
		if (($weekday > 7 || $weekday==1)) {
			$ret .= '</tr>'._NL._NL.'<tr>'._NL;
			$weekday = 1;
			$row++;
		}
		if ( !$weekday ) $weekday=1;
		$weekday ++;
		$prefix = "";
		$subfix = "";
		// วันอาทิตย์
		if ( $weekday == 2 ) ;
		// วันปัจจุบัน
		if ( $mday == $currentDay and $month == $currentMonth) ;
		$show_date=$year.'-'.sprintf('%02d',$month).'-'.sprintf('%02d',$mday);
		$is_calendar_item=array_key_exists($show_date, $month_list);
		$ret .= '<td class="'.($mday==$currentDay && $month==$currentMonth && $year==$currentYear?'today':'').($is_calendar_item?' have_item':'').'" >';
		if ($is_calendar_item) {
			$ret .= '<a href="#" onclick="ajax.link(\'tiny_calendar_detail\',\''.url('calendar/date/'.$show_date).'\');return false;" data-tooltip="'.$show_date .' : '.$day_item[$show_date].'">';
	//			$ret .= '<a class="sg-action" href="'.url('ajax/calendar/date/'.$show_date).'" data-rel="tiny_calendar_detail" title="header=[] body=['.$show_date .' : '.$day_item[$show_date].']">';
	//				$ret .= '<a href="#" onclick="ajax.Box(event,300,0,\'url:'.url('ajax/calendar/date/'.$show_date).'\');return false;"  title="header=[] body=['.$show_date .' : '.$day_item[$show_date].']">';
		}
		$ret .= cfg('calendar.today.blink') && $mday==$currentDay && $month==$currentMonth && $year==$currentYear?'<BLINK>':'';
		$ret .= $mday;
		$ret .= cfg('calendar.today.blink') && $mday==$currentDay && $month==$currentMonth && $year==$currentYear?'</BLINK>':'';
		if ($is_calendar_item) $ret .= '</a>';
		$ret .= "</td>"._NL;
	}

	// print empty weekdays after last month day
	if ($weekday != 1) {
		$day=0;
		for ($weekday; $weekday <= 7 ; $weekday++) $ret .= '<td class="not_in_month">'.(++$day).'</td>'._NL;
	}
	$ret .= '</tr>'._NL;
	$ret .= '<tr><td colspan="7"><div id="tiny_calendar_detail"></div></td></tr>'._NL;
	$ret .= '</table>'._NL;
	$ret .= '</div><!--tiny_calendar-->'._NL;
	return $ret;
}
?>