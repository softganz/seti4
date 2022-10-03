<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function user($self) {
	unset($self->theme->header);
	if (i()->ok) {
		location('profile/'.i()->uid);
	} else {
		return R::PageWidget('signin');
	}
}
?>