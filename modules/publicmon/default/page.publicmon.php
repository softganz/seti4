<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon($self) {
	R::View('publicmon.toolbar',$self,'Public Monitor');

	$ret .= '<div class="ui-card"><div class="ui-item">';
	$ret .= '<h3>Unknown Page</h3>';
	$ret .= '</div>';
	$ret .= '</div>';

	return $ret;
}
?>