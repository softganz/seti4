<?php
/**
* Project :: Admin Information Model
* Created 2021-01-25
* Modify  2021-01-25
*
* @param Object $self
* @param String $action
* @param Int $tranId
* @return String
*
* @usage project/admin/info
*/

$debug = true;

function project_admin_info($self, $action = NULL, $tranId = NULL) {
	$ret = '';

	switch ($action) {
		case '':
			# code...
			break;
		
		default:
			$ret .= 'NO ACTION';
			break;
	}

	return $ret;
}
?>