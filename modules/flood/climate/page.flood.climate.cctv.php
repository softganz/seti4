<?php
/**
* Flood City Climate - CCTV
* Created 2019-08-22
* Modify  2019-08-22
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_climate_cctv($self) {
	R::View('toolbar', $self, 'City Climate', 'flood.climate');

	$ret .= R::Page('flood.app.basin', NULL);
	return $ret;
}
?>