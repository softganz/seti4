<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_user($self, $uid = NULL) {
	$para = para(func_get_args(),2);

	$ret = '';

	//$ret .= print_o($para,'$para');

	if ($uid)
		$ret .= R::Page('paper.list', $self, 'user',$uid, 'page',$para->page);
	return $ret;
}
?>