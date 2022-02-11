<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_care_home($self) {
	$ret .= R::View('imed.toolbox',$self,'iMed@Care Plan', 'social');

	$ret .= '<h2>Care Plan Home</h2>';
	return $ret;
}
?>