<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_help($self) {
	R::View('imed.toolbar',$self,'Help and feedback','none');

	return $ret;
}
?>