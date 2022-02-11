<?php
/**
* LMS :: View Module Information
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Int $moduleInfo
* @return String
*/

$debug = true;

function lms_mod_info($self, $moduleId, $action) {
	$ret = '';

	switch ($action) {
		case 'survey.save':
			$ret .= 'Survey Save';
			$ret .= print_o(post(),'post()');
			break;
		
		default:
			$ret = 'NO ACTION';
			break;
	}

	return $ret;
}
?>