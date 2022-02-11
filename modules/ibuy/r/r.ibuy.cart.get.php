<?php
/**
* Generate cart information
* Created 2019-06-08
* Modify  2019-06-08
*
* @param Object $conditions
* @param Object $options
* @return Object
*/

$debug = true;

function r_ibuy_cart_get($conditions = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$userId  = SG\getFirst($conditions->id, i()->uid);

	$result->uid = $userId;
	$result->amt = 0;
	$result->discount_yes = 0;
	$result->discount_no = 0;
	$result->subtotal = 0;
	$result->discount = 0;
	$result->total = 0;
	$result->shipping = 0;
	$result->leveldiscount = 0;
	$result->marketvalue = 0;
	$result->resalerdiscount = 0;
	$result->franchisorvalue = 0;
	$result->discount_summary = mydb::select('SELECT `discount` FROM %ibuy_customer% WHERE `uid` = :uid LIMIT 1', ':uid', $userId)->discount;

	// TODO: Remove $result->mycart if not used
	$result->mycart = $_SESSION['mycart'];

	$stmt = 'SELECT c.`tpid`, t.`title`, c.*, p.*
			FROM %ibuy_cart% c
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %ibuy_product% p USING(`tpid`)
			WHERE c.`uid` = :uid;
			-- {sum: "amt"}';

	$dbs = mydb::select($stmt, ':uid', $userId);

	$result->mycart->add2cart = $dbs->sum->amt;

	$result->items = $dbs->items;

	foreach ($result->items as $key=>$rs) {

		$result->mycart->items[$rs->tpid] = $rs->qty;

		$amt = $rs->amt;
		$rs->price = R::Model('ibuy.get.price',$rs);
		$rs->subtotal = $subtotal = $rs->price * $amt;

		$rs->discount = $discount = 0;
		if ($rs->isdiscount) {
			$result->discount_yes += $subtotal;
			if (user_access('ibuy resaler price')) {
				$result->resalerdiscount += round($subtotal * cfg('ibuy.resaler.discount') / 100);
			}
		} else {
			$result->discount_no += $subtotal;
		}

		if ($rs->ismarket) {
			$rs->marketvalue = $subtotal;
			$result->marketvalue += $subtotal;
			$result->leveldiscount += $subtotal;
			$rs->leveldiscount = $subtotal;
		} else {
			$rs->marketvalue = 0;
			$rs->leveldiscount = 0;
		}

		if ($rs->isfranchisor) {
			$result->franchisorvalue += $subtotal;
		}

		$rs->total = $subtotal - $discount;

		$result->items[$key] = $rs;

		$result->amt += $amt;
		$result->subtotal += $subtotal;
		$result->discount += $discount;
		$result->total += $subtotal - $discount;
	}

	if (cfg('ibuy.shipping.lower') == 0 || (cfg('ibuy.shipping.lower') > 0 && $result->total < cfg('ibuy.shipping.lower'))) {
		$result->shipping = cfg('ibuy.shipping.price');
	}

	return $result;
}
?>