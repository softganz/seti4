<?php
/**
* Project Planning Controller
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_recieve($self, $rcvId = NULL, $action = NULL, $tranId = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	R::Model('garage.verify',$self, $shopInfo,'FINANCE');

	if (empty($rcvId)) $action = 'home';
	else if (empty($action)) $action = 'view';

	$rcvInfo = NULL;
	
	if ($rcvId) {
		$rcvInfo = R::Model('garage.recieve.get', $shopInfo->shopid, $rcvId, '{debug:false}');
		if (!($rcvId = $rcvInfo->rcvid)) return message('error', 'PROCESS ERROR:NO RECIEVE');
		$rcvInfo->shopInfo = $shopInfo;
	}

	$argIndex = 3; // Start argument

	//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//$ret .= print_o(func_get_args(), '$args');

	$ret = R::Page(
		'garage.recieve.'.$action,
		$self,
		$rcvInfo,
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