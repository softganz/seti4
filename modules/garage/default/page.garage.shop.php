<?php
/**
* Module Method
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_shop($self, $shopId = NULL, $action = NULL, $tranId = NULL) {
	$shopInfo = R::Model('garage.get.shop', $shopId);

	$isAdmin = is_admin('garage');
	$isMember = $shopInfo->iam;
	
	if (!$isMember) return message('error', 'Access Denied');

	if (!is_numeric($shopId)) {$action = $shopId; unset($shopId);} // Action as customerId and clear

	$ret = '';

	switch ($action) {

		default:
			if (empty($action) && empty($shopId)) $action = 'home';
			if (empty($action) && $shopId) $action = 'view';
			if (empty($shopInfo)) $shopInfo = $shopId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$shopId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'garage.shop.'.$action,
								$self,
								$shopInfo,
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

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>