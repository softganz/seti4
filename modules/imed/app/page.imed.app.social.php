<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_social($self, $orgId = NULL) {
	if ($_SESSION['imedapp'] === 'psyc') return location('imed/psyc/group');
	else if ($_SESSION['imedapp'] === 'care') return location('imed/care/team');
	else if ($orgId) {
		return R::Page('imed.app.social.info', $orgId);
	} else {
		return R::Page('imed.app.social.group',$self);
	}
}
?>