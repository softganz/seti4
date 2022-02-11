<?php
/**
* Calendar Home
*
* @param Object $self
* @param Int $var
* @param Array $_REQUEST
* @return String
*	@example
*		$_REQUEST
			cat = category
*			tpid = TopicId
*			orgid = OrgId
*			module = Module name
*		/calendar/*,t:229,o:1
*/

$debug = true;

function calendar_home($self, $action = NULL) {
	$post = (object) post();

	$ret = '';

	if (substr($action,0,1) == '*') $post->get = $action;
	$year = SG\getFirst($post->year, $year);
	$month = SG\getFirst($post->month, $month);
	$hash = post('hash');

	head('<meta name="robots" content="noindex,nofollow">');

	R::View('calendar.toolbar',$self);

	if (!_AJAX) {
		$calendarAttr = array();
		$calendarAttr['data-url'] = url('calendar');
		if (substr($action,0,1) == '*') $calendarAttr['data-get'] = $action;
		if ($post->tpid) $calendarAttr['data-tpid'] = $post->tpid;
		if ($post->orgid) $calendarAttr['data-orgid'] = $post->orgid;
		if ($post->module) $calendarAttr['data-module'] = $post->module;
		$ret .= '<div id="calendar-body" '.sg_implode_attr($calendarAttr).'>'._NL;
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

	return $ret;
}
?>