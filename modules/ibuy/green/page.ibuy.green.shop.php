<?php
/**
* GoGreen Spp Shop
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function ibuy_green_shop($self, $shopId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';
	unset($self->theme->toolbar, $self->theme->title);

	if ($shopId) {
		$shopInfo = R::Model('ibuy.shop.get', $shopId, '{debug: false}');
	}

	switch ($action) {
		case 'create' :
			break;

		default :

			if (empty($shopInfo)) $shopInfo = $shopId;
			if (empty($shopInfo)) $action = 'home';
			else if (empty($action)) $action = 'view';

			$argIndex = 3; // Start argument

			//debugMsg(func_get_args(), '$args');
			//debugMsg('PAGE IBUY/SHOP ShopId = '.$shopId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg = '.func_get_arg($argIndex));

			$ret = R::Page(
								'ibuy.green.shop.'.$action,
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

	return $ret;
}
?>