<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function map_view($self, $mapid) {
	$ret = '';

	$ret .= '<h3>Map View '.$mapid.'</h3>';
	$mapInfo = R::Model('map.get',$mapid);

	$ret .= print_o($mapInfo,'$mapInfo');

	return $ret;
}
?>