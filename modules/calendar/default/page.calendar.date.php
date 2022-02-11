<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_date($self,$date=NULL) {
	$date_list = R::Model('calendar.get.list', 'date='.$date);

	foreach ($date_list->items as $item) {
		$ret .= '<p>';
		$ret .= '<strong>'.($item->from_date===$item->to_date ? $item->from_date : $item->from_date.' - '.$item->to_date).' '.substr($item->from_time,0,5).($item->to_time?'-'.substr($item->to_time,0,5):'').'</strong>';
		$ret .= '<br /><strong>'.$item->title.'</strong>';
		if ($item->location) $ret .= '<br /><strong>ณ</strong> '.$item->location;
		if ($item->detail) $ret .= '<br />'.$item->detail;
		$ret .= '<br />';
		$ret .= '</p>';
	}
	return $ret;
}
?>