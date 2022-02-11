<?php
/**
* Flood Application Sign In
*
* @param Object $self
* @return String
*/

$debug = true;

function flood_app_signin($self) {
	if (i()->ok) {
		location('flood/app/menu');
	} else {
		$ret .= R::View('signform');
	}
	return $ret;
}
?>