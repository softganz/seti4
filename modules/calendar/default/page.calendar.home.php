<?php
/**
 * Calendar:: Page
 * Created :: 2007-03-06
 * Modify  :: 2025-07-22
 * Version :: 3
 *
 * @param String $args
 * @return Widget
 *
 * @usage calendar
 * @example
 *  $_REQUEST
 *  cat = category
 *  nodeId = TopicId
 *  orgId = OrgId
 * /calendar/*,t:229,o:1
 */

class CalendarHome extends Page {
	var $url;
	var $year;
	var $month;
	var $get;
	var $hash;
	var $nodeId;
	var $orgId;

	function __construct($action = NULL) {
		parent::__construct([
			'url' => url('calendar'),
			'year' => SG\getFirstInt(Request::all('year'), date('Y')), // 2025
			'month' => SG\getFirstInt(Request::all('month'), date('m')), // 01
			'hash' => SG\getFirst(Request::all('hash'), 'month'), // month, agenda
			'get' => SG\valid(preg_match('/^\*/',$action) ? $action : NULL, '/^[\*\,\:\dto]*$/'), // *,t:1,o:1
			'nodeId' => SG\valid(SG\getFirst(Request::all('nodeId'), Request::all('nodeid')), '/^[0-9\,]*[0-9]$/'), // 1,2,3
			'orgId' => SG\getFirstInt(Request::all('orgId'), Request::all('orgid')), // 1,2,3
		]);
	}

	#[\Override]
	function build() {
		// $post = (object) post();

		// if (preg_match('/^\*/',$this->action)) $post->get = $this->action;
		// $year = SG\getFirst($post->year, $year);
		// $month = SG\getFirst($post->month, $month);
		// $hash = post('hash');

		head('<meta name="robots" content="noindex,nofollow">');

		// if (!_AJAX) {
		// 	$calendarAttr = [
		// 		'data-url' => url('calendar'),
		// 		'data-get' => substr($this->action,0,1) == '*' ? $this->action : NULL,
		// 		'data-tpid' => $post->tpid ? $post->tpid : NULL,
		// 		'data-orgid' => $post->orgid ? $post->orgid : NULL,
		// 		'data-module' => $post->module ? $post->module : NULL
		// 	];
		// 	$ret .= '<div id="calendar" class="widget-calendar" '.sg_implode_attr($calendarAttr).'>'._NL;
		// }


		// //$ret .= 'Year = '.$year.' , Month = '.$month.' , Hash = '.$hash.'<br />';
		// //$ret .= print_o($calendarAttr,'$calendarAttr',2);
		// //$ret .= print_o($post,'$post',2);

		// switch ($hash) {
		// 	case 'agenda' :
		// 		$ret .= R::Model('calendar.get.agenda',$year,$month,$post);
		// 		break;

		// 	default :
		// 		$ret .= R::Model('calendar.get.month',$year,$month,$post);
		// 		break;
		// }

		// if (!_AJAX) {
		// 	$ret .= '</div>';
		// }
		// debugMsg($this, '$this');
		// die('This page is deprecated. Please use the new CalendarWidget instead.');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar',
			]), // AppBar
			'body' => new CalendarWidget([
				'year' => $this->year,
				'month' => $this->month,
				'hash' => $this->hash,
				'url' => $this->url,
				'get' => $this->get,
				'nodeId' => $this->nodeId,
				'orgId' => $this->orgId,
				// 'date' => SG\getFirst(Request::all('date')),
				// 'from' => SG\getFirst(Request::all('from')),
				// 'to' => SG\getFirst(Request::all('to')),
				// 'callFrom' => $this->callFrom,
			]), // CalendarWidget
		]);
	}
}
?>