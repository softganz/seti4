<?php
/**
* iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_org($self, $orgId = NULL, $action = NULL, $actionId = NULL) {
	if (empty($orgId)) return R::Page('imed.org.home');

	if ($orgId) {
		$orgInfo = R::Model('org.get', $orgId);
		$orgId = $orgInfo->orgid;
	}

	if (!$orgId) return message('error', 'Invalid org');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;

	switch ($action) {
		case 'addmember':
			# code...
			break;

		case 'addpatient':
			# code...
			break;
		
		default:
			R::View('imed.toolbar', $self, $orgInfo->name, 'org', $orgInfo);
			if (empty($action)) $action = 'view';
			$ret .= R::View('imed.org.'.$action, $orgInfo);
			break;
	}
	return $ret;
}
?>