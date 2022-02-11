<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_rehab($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action) && empty($orgId)) {
		return R::Page('imed.rehab.home',$self);
	} else if (empty($action) && $orgId) {
		return R::Page('imed.rehab.view',$self,$orgId);
	}

	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	$isAdmin = user_access('administer imeds') || $orgInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $orgInfo->is->officer;

	// DO submodule controller
	R::View('imed.toolbar', $self, 'ศูนย์ฟื้นฟูสมรรถภาพ', 'rehab', $orgInfo);

	$ret = '';

	switch ($action) {

		default:
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'imed.rehab.'.$action,
								$self,
								$orgInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	return $ret;
}
?>