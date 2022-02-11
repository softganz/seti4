<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_my($self) {
	$userZone = imed_model::get_user_zone(i()->uid,'imed');

	$ret = '';

	$zoneStr = '';
	foreach ($userZone as $zone) {
		$zoneStr .= '<li>'.SG\implode_address($zone,'short').'('.$zone->right.')</li>';
	}

	$ret .= '<h4>พื้นที่รับผิดชอบ</h4><ul>'.($zoneStr?$zoneStr:'ไม่กำหนดพื้นที่').'</ul>';

	return $ret;
}
?>