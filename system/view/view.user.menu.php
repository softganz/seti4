<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function view_user_menu($active = NULL) {
	if (i()->ok) return;

	$ret .= '<ul class="tabs tabs-primary">'._NL;
	if (!i()->ok && user_access('register new member')) {
		$ret .= '<li'.($active=='register'?' class="-active"':'').'><a href="'.url('user/register').'" title="Create new account">'.tr('Create new account').'</a></li>';
	}

	$ret .= '<li'.(empty($active) || $active=='signform'?' class="-active"':'').'><a href="'.url('user').'" title="Sign in">'.tr('Sign in').'</a></li>';

	if (!i()->ok) {
		$ret .= '<li'.($active=='password'?' class="-active"':'').'><a href="javascript:void(0)" onclick="window.location=\''.url('user/password').'\';return false;" title="Request new password">'.tr('Request new password').'</a></li>';
	}
	$ret .= '</ul>'._NL;

	return $ret;
}
?>