<?php
/**
* Get price for each user
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $productInfo
* @return Decimal
*/

$debug = true;

/**
 * 
 * 
 * @param Object $rs
 * @return Numeric
 */
function r_ibuy_get_price($info, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$defaultPrice = cfg('ibuy.price.default');
	$canNotBuyPrice = cfg('ibuy.price.cannotbuy');

	if (i()->am == 'franchise') $result = $info->retailprice;
	else if (i()->am == 'resaler') $result = $info->resalerprice;
	else if (i()->am == 'price1') $result = $info->price1;
	else if (i()->am == 'price2') $result = $info->price2;
	else if (i()->am == 'price3') $result = $info->price3;
	else if (i()->am == 'price4') $result = $info->price4;
	else if (i()->am == 'price5') $result = $info->price5;
	else if ($defaultPrice == 'franchise') $result = $info->retailprice;
	else if ($defaultPrice == 'resaler') $result = $info->resalerprice;
	else if ($defaultPrice == 'price1') $result = $info->price1;
	else if ($defaultPrice == 'price2') $result = $info->price2;
	else if ($defaultPrice == 'price3') $result = $info->price3;
	else if ($defaultPrice == 'price4') $result = $info->price4;
	else if ($defaultPrice == 'price5') $result = $info->price5;
	else $result = $info->listprice;
	return $result;
}
?>