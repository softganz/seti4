<?php
/**
* My project in project set
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_set_my($self, $tpid = NULL, $action = NULL, $tranId = NULL) {

	$ret = R::Page('project.my.all', NULL);

	return $ret;
}
?>