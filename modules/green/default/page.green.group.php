<?php
/**
* Green :: Group Controller
*
* @param Object $self
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*/

$debug = true;

function green_group($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';
	unset($self->theme->toolbar, $self->theme->title);

	if ($orgId) {
		$orgInfo = R::Model('green.shop.get', $orgId, '{debug: false}');
	}

	if (empty($orgInfo)) $action = 'home';
	else if ($orgInfo && empty($action)) $action = 'view';

	$argIndex = 3; // Start argument

	//debugMsg(func_get_args(), '$args');
	//debugMsg('PAGE IBUY/SHOP ShopId = '.$orgId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg = '.func_get_arg($argIndex));

	$ret = R::Page(
		'green.group.'.$action,
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

	return $ret;
}
?>