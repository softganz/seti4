<?php
/**
 * Calendar:: Page
 * Created :: 2007-03-06
 * Modify  :: 2025-07-19
 * Version :: 1
 *
 * @param String $args
 * @return Widget
 *
 * @usage calendar
 * @example
 *  $_REQUEST
 *  cat = category
 *  tpid = TopicId
 *  orgid = OrgId
 * /calendar/*,t:229,o:1
 */

class CalendarHome extends Page {
	var $action;

	function __construct($action = NULL) {
		parent::__construct([
			'action' => $action
		]);
	}

	#[\Override]
	function build() {
		$post = (object) post();

		$ret = '';

		if (substr($this->action,0,1) == '*') $post->get = $this->action;
		$year = \SG\getFirst($post->year, $year);
		$month = \SG\getFirst($post->month, $month);
		$hash = post('hash');

		head('<meta name="robots" content="noindex,nofollow">');

		if (!_AJAX) {
			$calendarAttr = array();
			$calendarAttr['data-url'] = url('calendar');
			if (substr($this->action,0,1) == '*') $calendarAttr['data-get'] = $this->action;
			if ($post->tpid) $calendarAttr['data-tpid'] = $post->tpid;
			if ($post->orgid) $calendarAttr['data-orgid'] = $post->orgid;
			if ($post->module) $calendarAttr['data-module'] = $post->module;
			$ret .= '<div id="calendar-body" class="calendar-body" '.sg_implode_attr($calendarAttr).'>'._NL;
		}


		//$ret .= 'Year = '.$year.' , Month = '.$month.' , Hash = '.$hash.'<br />';
		//$ret .= print_o($calendarAttr,'$calendarAttr',2);
		//$ret .= print_o($post,'$post',2);

		switch ($hash) {
			case 'agenda' :
				$ret .= R::Model('calendar.get.agenda',$year,$month,$post);
				break;

			default :
				$ret .= R::Model('calendar.get.month',$year,$month,$post);
				break;
		}

		if (!_AJAX) {
			$ret .= '</div>';
		}
		
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar',
				'navigator' => new CalendarNavWidget(),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret
				], // children
			]), // Widget
		]);
	}
}
?>