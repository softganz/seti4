<?php
/**
* Flood City Climate Home Page
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @return String
*/

$debug = true;

function flood_climate($self) {
	$ret = '';

	//R::View('toolbar', $self, 'City Climate', 'flood.climate');

	$ret .= R::Page('flood.climate.feed', $self);

	return $ret;
}
?>