<?php
/**
 * Calendar:: Get Month Calendar Table
 * Created :: 2007-03-06
 * Modify  :: 2025-07-19
 * Version :: 3
 *
 * @param Int $year
 * @param Int $month
 * @return String
 *
 * @usage R::Model('calendar.get.month', $year, $month)
 */

$debug = true;

function r_calendar_get_month ($year = '', $month = '') {
	$para=para(func_get_args(),2);

	$isAdd=user_access('administer calendars,create calendar content');
	if ($para->module) {
		$isAdd = R::On($para->module.'.calendar.isadd',$para);
	}
	# If date is not specified assume current date
	if ((!$month || !$year) || $month<1 || $year<1 || $month>12) {
		$year = date('Y');
		$month = date('m');
	}
	$year=intval($year);
	$month=intval($month);
	$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

	$todayDate = intval(date('d'));
	$todayMonth = intval(date('m'));
	$todayYear = intval(date('Y'));


	// Get month event list
	$even_para = isset($para->_src)?'/'.$para->_src:'';



	$month_list = R::Model('calendar.get.list','getMonth='.$year.'-'.sprintf('%02d',$month),$para);
	//print_r($month_list);
	//return;



	# Calculate value of (prev year,prev month,next year and next month)
	$n_month=date('m',mktime(0, 0, 0, $month+1, 1, $year));
	$n_year=date('Y',mktime(0, 0, 0, $month+1, 1,$year));
	$p_month=date('m',mktime(0, 0, 0, $month-1, 1, $year));
	$p_year=date('Y',mktime(0, 0, 0, $month-1, 1, $year));

	$even_title_field = cfg('calendar.month.event_title_field');

	// Show current month
	$month_str = array('January','February','March','April','May','June','July','August','September','October','November','December');
	$current_month = getdate(mktime(0,0,0,$month,1,$year));
	$prev_month = getdate(mktime(0,0,0,$month-1,1,$year));
	$next_month = getdate(mktime(0,0,0,$month+1,1,$year));

	$ret .= '<div class="calendar-main" data-month="'.sg_date($year.'-'.$month.'-01','ดดด ปปปป').'">'._NL;
	$ret .= '<table class="calendar-month" width=100% height="400" border="0" cellspacing="2" cellpadding="1">'._NL;

	// Show date name
	$day_array = array('Sun' , 'Mon' , 'Tue' , 'Wed' , 'Thu' , 'Fri' , 'Sat' );
	$ret .= '<thead><tr align=center height=20>'._NL;
	foreach ( $day_array as $key=>$pic ) {
		$ret .= '<th width=14.2857%><div class="dayname">'.$pic.'</div></th>'._NL;
	}
	$ret .= '</tr></thead>'._NL;

	$ret .= '<tr align=left valign=top style="height:80px;">'._NL;
	# Calculate unix timestamp for first day of the month
	$first_month_day = getdate (mktime(0, 0, 0, $month, 1, $year));
	$first_month_weekday = $first_month_day['wday'];

	if ( $first_month_weekday == 0 ) $first_month_weekday = 7;

	# calculate last day of the month
	$last_prev_month_day = getdate (mktime(0, 0, 0, $month, 0, $year));
	$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

	# Print empty weekdays before first day of the month
	$last_month_day_start=$last_prev_month_day['mday']-$first_month_weekday;
	$row=1;
	if($first_month_weekday != 7){
		for ($weekday = 1; $weekday <= $first_month_weekday; $weekday++) {
			$ret .= '<td><div class="daynum">'.($last_month_day_start+$weekday).'</div></td>'._NL;
		}
	}
	# Print every month days
	for ($mday = 1; $mday <= $last_month_day['mday']; $mday++) {
		if (($weekday > 7 || $weekday==1)) { // Is Sunday
			$ret .= '</tr>'._NL.'<tr align=left valign=top style="height:80px;">'._NL;
			$weekday = 1;
			$row++;
		}
		if ( !$weekday ) $weekday=1;
		$weekday ++;
		$prefix = '';
		$subfix = '';
		// วันอาทิตย์
		if ( $weekday == 2 ) ;
		// วันปัจจุบัน
		if ( $mday == $todayDate and IntVal($month) == $todayMonth) ;

		/*
		if ( IsSet($this->varDayProperty[$mday]) ) {	// วันที่มีรายการ
			if ( IsSet($this->varDayProperty[$mday]['bgcolor']) )
				$style .= 'background-color:'.$this->varDayProperty[$mday]['bgcolor'].';';
			if ( IsSet($this->varDayProperty[$mday]['link']) ) {
				$prefix .= $this->varDayProperty[$mday]['link'];
				$subfix .= '</a>';
			}
		}
		*/



		$show_date=$year.'-'.sprintf('%02d',$month).'-'.sprintf('%02d',$mday);
		$is_calendar_item=array_key_exists($show_date, $month_list);

		//			$add_calendar_link=$isAdd?'onclick="ajax.link(\'calendar-body\',\''.url('calendar/add'.($para->tpid?'/tpid/'.$para->tpid:''),array('module'=>$para->module,'d'=>$mday.'/'.$month.'/'.$year).'\');return false;"':'';

		$ret .= '<td id="'.(sprintf('%02d',$mday).'/'.sprintf('%02d',$month).'/'.$year).'" class="daybox'.($mday==$todayDate && intval($month)==$todayMonth && intval($year)==$todayYear?' currentdaybox':'').($isAdd ? ' calendar-add':'').'"'.($isAdd?' title="'.tr('Add new event','คลิกเพื่อเพิ่มกิจกรรมใหม่'):'').'">'._NL;
		//'" onmouseover="$(\'#addevent_'.$mday.'\').show()" onmouseout="$(\'#addevent_'.$mday.'\').hide()"':'').'>'.($isAdd?'<img src="http://img.softganz.com/icon/date_add.png" id="addevent_'.$mday.'" style="display:none;">':'')
		$ret .= '<div class="daynum'.($is_calendar_item?' have_item':'').'" ><span>'.$mday.'</span></div>'._NL;

		if ($is_calendar_item) {
			foreach ($month_list[$show_date] as $item) {
				$calendarOptions = $item['options'];

				// title text
				$item_title = $item[$even_title_field] ? $item[$even_title_field].($even_title_field!='title'?':'.$item['title']:'') : $item['title'];
				$event_title = $item['title'];
				if ($item['category_name']) $event_title .= ' ('.$item['category_name'].')';
				$event_detail = ' วัน'.sg_date($item['from_date'],'ววว ว ดดด ปปปป');
				if ($item['to_date']!=$item['from_date']) $event_detail .= ' - '.sg_date($item['to_date'],'ววว ว ดดด ปปปป');
				if ($item['from_time']) $event_detail .= ' เวลา '.substr($item['from_time'],0,5);
				if ($item['to_time']) $event_detail .= '-'.substr($item['to_time'],0,5);
				if ($item['location']) $event_detail .= '<br />ที่ '.$item['location'];
				$event_detail .= '<br />by '.$item['owner_name'].' ('.$item['privacy'].')';
				$event_title = htmlspecialchars($event_title);
				$event_detail = htmlspecialchars($event_detail);

				$ret .= '<div id="reldiv'.$item['id'].'" class="month_event -calendar-item-'.$item['id'].($item['category'] ? ' tags-'.$item['category'] : '').'" data-tooltip="'.$event_title.'<br />'.$event_detail.'">';
				// $ret .= '<div id="reldiv'.$item['id'].'"
				// 	class="month_event'.($item['category']?' tags-'.$item['category']:'').'"
				// 	 title="'.$event_title.'<p>'.$event_detail.'</p>"
				// 	>';
				$ret .= '<a class="sg-action '.$item['privacy'].'" href="'.url('calendar/'.$item['id'],array('tpid'=>$para->tpid,'module'=>$para->module)).'"'
					. ($calendarOptions->color ? ' style="color:'.$calendarOptions->color.';"' : '')
					. ' data-rel="box" data-width="600" data-height="300">';
				if ($item['from_time'] && (intval(substr($item['from_time'],0,2)) || intval(substr($item['from_time'],3,2)))) {
					if (cfg('calendar.format.time')=='short') {
						$hr=intval(substr($item['from_time'],0,2));
						$am=$hr<12?'':'p';
						$hr=$hr<12?$hr:$hr-12;
						$min=intval(substr($item['from_time'],3,2));
						if ($min==0) $min='';
						$time=$hr.($min?':':'').$min.$am;
					} else {
						$time=substr($item['from_time'],0,5).' น.';
					}
					$ret .= '<em class="time">'.$time.'</em>&nbsp;';
				}
				$ret .= '<span class="event-title" style="cursor: pointer;">'.$item_title.'</span>';
				$ret .= '</a>';
				$ret .= '</div>'._NL;
			}
		}
		$ret .= '</td>'._NL;
	}

	if ($weekday != 1) { # print empty weekdays after last month day
		$day=0;
		for ($weekday; $weekday <= 7 ; $weekday++) {
			$ret .= '<td><div class="daynum">'.(++$day).'</div></td>'._NL;
		}
	}
	$ret .= '</tr>'._NL;
	$ret .= '</table>'._NL;

	//debugMsg($month_list,'$month_list');
	$ret .= '</div><!-- mainbody -->'._NL;
	if (user_access('administer calendars,create calendar content')) $ret .= '<div class="tips">'.tr('Add new calendar item by click on date you want to add.').'</div>';
	return $ret;
}
?>