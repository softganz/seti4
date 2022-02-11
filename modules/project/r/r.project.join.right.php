<?php
/**
* Project Join Right
* Created 2019-07-30
* Modify  2019-07-30
*
* @param Object $projectInfo
* @return Object
*/

$debug = true;

function r_project_join_right($projectInfo) {
	$result = NULL;

	$joinRight = $projectInfo->doingInfo->options->right;

	$isWebAdmin = is_admin();
	$isAdmin = $isWebAdmin || $projectInfo->RIGHT & _IS_ADMIN;
	$isMember = $projectInfo->info->membershipType;


	$groupToEdit = SG\getFirst($joinRight->joinEdit, 'OWNER');
	$groupToCreateRcv = SG\getFirst($joinRight->rcvCreate, 'OWNER');
	$groupToLockRcv = SG\getFirst($joinRight->rcvLock, 'OWNER');
	$groupToUnlockRcv = SG\getFirst($joinRight->rcvUnlock, 'OWNER');

	$result->accessJoin = $isAdmin || $isMember;
	$result->editJoin = $isAdmin || in_array($isMember, explode(',',$groupToEdit));
	$result->createRcv = $isAdmin || in_array($isMember, explode(',',$groupToCreateRcv));
	$result->lockRcv = $isAdmin || in_array($isMember, explode(',',$groupToLockRcv));
	$result->unlockRcv = $isWebAdmin || in_array($isMember, explode(',',$groupToUnlockRcv));
	$result->adminJoin = $isAdmin;
	$result->adminWeb = $isWebAdmin;

	//debugMsg($result, '$right');

	return $result;
}
?>