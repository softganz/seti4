<?php
function module_publicmon_install() {


	$ret .= implode('<br /><br />'._NL, $queryResult);

	return $ret;
}
?>