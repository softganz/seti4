<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function test_sql_hack1($self) {
	$ret = '';

	$urlList[] = url('test/sql/hack?uid=3&limit=1&f=name,(SELECT password FROM sgz_users WHERE uid=4) a');

	$urlList[] = url('test/sql/hack?uid=3&limit=1&f=name, CONCAT(1,(SELECT password FROM sgz_users WHERE uid=4)) a');

	$urlList[] = url('test/sql/hack?uid=3&limit=1&f=name, CONCAT(1,(SELECT password FROM sgz_users WHERE uid=4 OR \'1\'=1)) a');

	foreach ($urlList as $value) {
		$ret .= '<a href="'.$value.'" target="_blank">'.$value.'</a><br />';
	}

	return $ret;
}
?>