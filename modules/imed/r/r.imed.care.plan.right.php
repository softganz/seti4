<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_imed_care_plan_right($careInfo, $orgInfo) {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;
	$result->RIGHT = NULL;
	$result->RIGHTBIN = NULL;
	$result->is = NULL;

	if (!i()->ok) return;
	
	$result->is->access = $careInfo->uid == i()->uid || $orgInfo->is->socialtype;
	$result->is->edit = $careInfo->uid == i()->uid || $orgInfo->is->admin || $orgInfo->is->socialtype == 'MODERATOR';

	$result->is->delete = $result->is->edit;
	$result->is->tran = $result->is->edit || $careInfo->info->cguid == i()->uid;

	if ($result->is->access) $result->RIGHT = $result->RIGHT | _IS_ACCESS;
	if ($result->is->edit) $result->RIGHT = $result->RIGHT | _IS_EDITABLE;
	if ($result->is->delete) $result->RIGHT = $result->RIGHT | _IS_DELETABLE;
	$result->RIGHTBIN = decbin($result->RIGHT);

	return $result;
}
?>