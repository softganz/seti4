<?php
/**
* GoGreen Land
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function green_land($self, $landId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';

	if ($landId) {
		$landInfo = R::Model('ibuy.land.get', $landId, '{debug: false}');
	}

	switch ($action) {
		case 'create' :
			break;

		default :

			if (empty($landInfo)) $action = 'home';
			else if (empty($action)) $action = 'view';

			$argIndex = 3; // Start argument

			//debugMsg(func_get_args(), '$args');
			//debugMsg('PAGE IBUY/SHOP ShopId = '.$shopId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg = '.func_get_arg($argIndex));

			$ret = R::Page(
								'green.land.'.$action,
								$self,
								$landInfo,
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

	//$ret .= print_o($landInfo, '$landInfo');
	return $ret;
}
?>