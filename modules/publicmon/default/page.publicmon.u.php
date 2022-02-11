<?php
/**
* Public Monitor
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_u($self, $username = NULL) {
	R::View('publicmon.toolbar',$self,'Public Monitor');

	$ret .= '<div class="ui-card"><div class="ui-item">';
	$ret .= '<h3>Information of '.$username.'</h3>';
	$ret .= '</div>';
	$ret .= '</div>';
	return $ret;
}
?>