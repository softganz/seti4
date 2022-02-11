<?php
/**
* Saveup :: Report Main Page
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report
*/

$debug = true;

function saveup_report($self) {
	R::View('saveup.toolbar',$self,'รายงาน');

	$ret.=R::View('saveup.menu.main');
	return $ret;
}
?>