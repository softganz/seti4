<?php
/**
 * Calendar:: Calcendr Widget
 * Created :: 2025-07-20
 * Modify  :: 2025-07-22
 * Version :: 1
 *
 * @param Array $args
 * @return Object
 *
 * @usage import('widget:calendar.widget.php')
 * @usage new CalendarWidgetl([])
 */

class CalendarWidget extends Widget {
	var $id = 'calendar-body';
	var $class = 'widget-calendar';
	var $tagName = 'div';
	private $classContent = 'calendar-content'; // class for content div
	var $year;
	var $month;
	var $hash;
	var $callFrom;
	var $src;
	var $nodeId;
	var $orgId;
	var $url;
	var $debug = false; // debug mode
	var $attribute = [];
	var $navigator;
	var $right;

	function __construct($args = []) {
		if (isset($args['class'])) $this->class .= ' '.$args['class'];
		unset($args['class']);

		parent::__construct(
			array_replace_recursive(
				[
					'year' => NULL,
					'month' => NULL,
					'hash' => NULL,
					'url' => url('calendar'),
					'get' => NULL,
					'nodeId' => NULL,
					'orgId' => NULL,
					'module' => NULL,
					'src' => NULL,
					'debug' => $this->debug,
					'attribute' => array_replace_recursive(
						[
							'data-year' => $args['year'] ?? SG\getFirstInt($args['year'], date('Y')),
							'data-month' => SG\getFirstInt($args['month'], date('m')),
							'data-hash' => SG\getFirst($args['hash']),
							'data-src' => SG\getFirst($args['src']),
							'data-nodeId' => SG\getFirstInt($args['nodeId']),
							'data-orgId' => SG\getFirstInt($args['orgId']),
							'data-callFrom' => $args['callFrom'],
							'data-apiUrl' => SG\getFirst($args['url'], url('calendar')),
							'data-get' => SG\getFirst($args['get']),
						],
						(Array) $this->attribute
					),
					'navigator' => SG\getFirst($args['navigator'], new CalendarNavWidget()),
					'right' => (Object) [
						'add' => empty($args['callFrom']) ? user_access('administer calendars,create calendar content') : false,
					],
				],
				$args,
				['args' => (Array) $args]
			)
		);

		// if ($this->module) {
		// 	$this->right->add = R::On($this->module.'.calendar.isadd', $this);
		// }

		if (!_AJAX) {
			$this->children = [
					'navigator' => new CalendarNavWidget(),
					// new DebugMsg($args, '$args'),
					new Container([
						'class' => $this->classContent,
						'atribute' => ['data-month' => sg_date($year.'-'.$month.'-01','ดดด ปปปป')],
						'child' => $this->month($this->year, $this->month),
					]), // Container
			]; // children
		}
		// debugMsg($this->right, '$this->right');
	}

	#[\Override]
	function build() {
		if (_AJAX) return $this->month($this->year, $this->month);

		head('calendar.js', '<script type="module" src="/calendar/js.calendar.js"></script>');

		return parent::build();

		// return new Widget([
		// 	'id' => $this->id,
		// 	'class' => $this->class,
		// 	'tagName' => $this->tagName,
		// 	'attribute' => array_replace_recursive(
		// 		(Array) $this->attribute,
		// 		[
		// 			'year' => $this->year,
		// 			'month' => $this->month,
		// 			'hash' => $this->hash,
		// 			'src' => $this->src,
		// 			'nodeId' => $this->nodeId,
		// 			'orgId' => $this->orgId,
		// 			'module' => $this->module,
		// 			'url' => $this->url,
		// 			'get' => $this->get,
		// 			'debug' => $this->debug,
		// 		]
		// 	),
		// 	'children' => [
		// 		'navigator' => new CalendarNavWidget(),
		// 		$this->month($this->year, $this->month),
		// 	], // children

		// 	// 'child' => new CalendarWidget([
		// 	// 	'year' => $year,
		// 	// 	'month' => $month,
		// 	// 	'hash' => $hash,
		// 	// 	'url' => url('calendar'),
		// 	// 	'get' => substr($this->action,0,1) == '*' ? $this->action : NULL,
		// 	// 	'nodeId' => $post->nodeId ? $post->nodeId : NULL,
		// 	// 	'orgId' => $post->orgId ? $post->orgId : NULL,
		// 	// 	'module' => $post->module ? $post->module : NULL
		// 	// ]),
		// ]); // Container
	}

	// Render month calendar
	function month($year = NULL, $month = NULL) {
		# If date is not specified assume current date
		if ((!$month || !$year) || $month<1 || $year<1 || $month>12) {
			$year = date('Y');
			$month = date('m');
		}
		$year = intval($year);
		$month = intval($month);

		// Get the first day of the month
		$firstDay = mktime(0, 0, 0, $month, 1, $year);
		$daysInMonth = date('t', $firstDay); // Number of days in month
		$startDayOfWeek = date('w', $firstDay); // Day of week (0=Sunday)
		$lastMonthDay = mktime(0, 0, 0, $month-1, 1, $year);
		$daysInLastMonth = date('t', $lastMonthDay); // Number of days in last month

		$even_title_field = cfg('calendar.month.event_title_field');
		$dayNames = ['Sun' , 'Mon' , 'Tue' , 'Wed' , 'Thu' , 'Fri' , 'Sat'];

		// debugMsg('year = '.$year.' month = '.$month);

		// Get month event list
		$calendarList = CalendarModel::getEvents(['getMonth' => $year.'-'.sprintf('%02d',$month)] + (Array) $this->args);

		// Show current month
		$ret = '<table class="calendar-month" width="100%" height="400" border="0" cellspacing="2" cellpadding="1" data-current-month="'.sg_date($year.'-'.$month.'-01', 'ดดด ปปปป').'">'._NL;

		// Show day name
		$ret .= '<thead><tr align="center">'._NL;
		foreach ( $dayNames as $dayName ) {
			$ret .= '<th width="14.2857%"><div class="dayname">'.$dayName.'</div></th>'._NL;
		}
		$ret .= '</tr></thead>'._NL;

		$ret .= '<tbody><tr align="left" valign="top">'._NL;

		// Empty cells for days before the first day of the month
		for ($i = $startDayOfWeek-1; $i >= 0 ; $i--) {
			$ret .= '<td><div class="daynum -empty">'.($daysInLastMonth - $i).'</div></td>'._NL;
		}

		// Print every month days
		$currentDay = 1;
		while ($currentDay <= $daysInMonth) {
			// Start new row if we're at the beginning of the week
			if (($currentDay + $startDayOfWeek - 1) % 7 == 0 && $currentDay > 1) {
				$ret .= '</tr><tr>';
			}

			// Check is today
			$isToday = $year == date('Y') && $month == date('n') && $currentDay == date('j');
			
			$ret .= '<td id="'.(sprintf('%02d',$currentDay).'/'.sprintf('%02d',$month).'/'.$year).'" '
				. 'class="daybox'.($isToday ? ' currentdaybox' : '').($this->right->add ? ' calendar-add' : '').'"'
				. ($this->right->add ? ' title="'.tr('Add new event','คลิกเพื่อเพิ่มกิจกรรมใหม่') : '').'"'
				. '>'._NL;

			$showDate = $year.'-'.sprintf('%02d',$month).'-'.sprintf('%02d',$currentDay);
			$hasCalendarItem = array_key_exists($showDate, $calendarList);

			$ret .= '<div class="daynum'.($hasCalendarItem?' have_item':'').'" ><span>'.$currentDay.'</span></div>'._NL;

			// Show calendar items for the day
			if ($hasCalendarItem) {
				foreach ($calendarList[$showDate] as $calendar) {
					$ret .= $this->renderCalendarItem($calendar, $even_title_field);
				}
			}

			$ret .= '</td>'._NL;
			$currentDay++;
		}

		// Fill remaining cells in the last row
		$endDayOfWeek = ($startDayOfWeek + $daysInMonth - 1) % 7;
		for ($day = $endDayOfWeek + 1; $day < 7; $day++) {
			$ret .= '<td><div class="daynum -empty">'.($day - $endDayOfWeek).'</div></td>'._NL;
		}
		
		$ret .= '</tr>'._NL;
		$ret .= '<tbody></table>'._NL;
		
		return new Container([
			'class' => $this->classContent,
			'atribute' => ['data-month' => sg_date($year.'-'.$month.'-01','ดดด ปปปป')],

			'children' => [
				// '@'.date('H:i:s').' Year ='.$year.' Month ='.$month,
				// new DebugMsg($this->args, '$this->args'),
				// new DebugMsg($this->right, '$this->right'),
				$ret,
				$this->right->add ? new Container([
					'class' => 'tips -sg-paddingnorm',
					'child' => tr('Add new calendar item by click on date you want to add.'),
				]) : NULL,
			], // children
		]);
	}

	private function renderCalendarItem($calendar, $even_title_field) {
		$calendarOptions = $calendar['options'];

		$title = $calendar[$even_title_field] ? $calendar[$even_title_field].($even_title_field != 'title' ? ':'.$calendar['title'] : '') : $calendar['title'];
		$eventTitle = $calendar['title'];
		if ($calendar['category_name']) $eventTitle .= ' ('.$calendar['category_name'].')';
		$eventDetail = ' วัน'.sg_date($calendar['from_date'],'ววว ว ดดด ปปปป');
		if ($calendar['to_date'] != $calendar['from_date']) $eventDetail .= ' - '.sg_date($calendar['to_date'],'ววว ว ดดด ปปปป');
		if ($calendar['from_time']) $eventDetail .= ' เวลา '.substr($calendar['from_time'],0,5);
		if ($calendar['to_time']) $eventDetail .= '-'.substr($calendar['to_time'],0,5);
		if ($calendar['location']) $eventDetail .= '<br />ที่ '.$calendar['location'];
		$eventDetail .= '<br />by '.$calendar['owner_name'].' ('.$calendar['privacy'].')';
		$eventTitle = htmlspecialchars($eventTitle);
		$eventDetail = htmlspecialchars($eventDetail);

		$ret .= '<div id="reldiv'.$calendar['id'].'" '
			. 'class="month-event -calendar-item-'.$calendar['id'].($calendar['category'] ? ' tags-'.$calendar['category'] : '').'" '
			. 'data-tooltip="'.$eventTitle.'<br />'.$eventDetail.'" '
			. 'data-color="'.$calendarOptions->color.'" '
			. '>';
		$ret .= '<a class="sg-action '.$calendar['privacy'].'" '
			. 'href="'.url('calendar/'.$calendar['id'], ['nodeId' => $this->nodeId, 'callFrom' => $this->callFrom]).'" '
			. ($calendarOptions->color ? 'style="color:'.$calendarOptions->color.';" ' : '')
			. 'data-rel="box" data-width="600" data-height="300"'
			. '>';
		if ($calendar['from_time'] && (intval(substr($calendar['from_time'],0,2)) || intval(substr($calendar['from_time'],3,2)))) {
			if (cfg('calendar.format.time')=='short') {
				$hr = intval(substr($calendar['from_time'],0,2));
				$am = $hr < 12 ? '' : 'p';
				$hr = $hr < 12 ? $hr : $hr-12;
				$min = intval(substr($calendar['from_time'],3,2));
				if ($min == 0) $min = '';
				$time = $hr.($min ? ':' : '').$min.$am;
			} else {
				$time = substr($calendar['from_time'],0,5).' น.';
			}
			$ret .= '<em class="time">'.$time.'</em>&nbsp;';
		}
		$ret .= '<span class="event-title" style="cursor: pointer;">'.$title.'</span>';
		$ret .= '</a>';
		$ret .= '</div>'._NL;

		return $ret;
	}
}
?>