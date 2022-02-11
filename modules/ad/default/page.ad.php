<?php
/**
* Ad Home
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad($self, $adId = NULL, $action = NULL) {
	$ret = '';

	if ($adId) {
		if (empty($action)) $action = 'view';
		$ret .= R::Page('ad.'.$action, $self, $adId);
	} else {
		$ret .= R::Page('ad.list', $self);
	}
	return $ret;
}
?>