<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_right_develop_delete($devInfo) {
	$result = $devInfo->RIGHT & (_IS_ADMIN | _IS_TRAINER | _IS_OWNER);
	return $result;
}
?>