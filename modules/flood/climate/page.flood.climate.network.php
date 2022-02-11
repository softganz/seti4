<?php
/**
* Flood City Climate - Network
* Created 2019-08-22
* Modify  2019-08-22
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_climate_network($self) {
	R::View('toolbar', $self, 'City Climate', 'flood.climate');
	$ret = '';

	$ret .= R::Page('flood.event.send', NULL);
	
	return $ret;
}
?>