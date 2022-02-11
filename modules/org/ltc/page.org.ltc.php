<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_ltc($self, $orgid = NULL) {
	R::View('org.toolbar',$self,'Long Term Care', 'ltc', $orgInfo);

	$ret = 'Long Term Care';
	return $ret;
}
?>