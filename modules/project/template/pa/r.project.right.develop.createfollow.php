<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_right_develop_createfollow($devInfo) {
	$isInTrainerGroup = in_array('trainer', i()->roles);
	$result = ($devInfo->RIGHT & (_IS_ADMIN | _IS_TRAINER)) || $isInTrainerGroup;
	return $result;
}
?>