<?php
/**
* Get items in cart
* Created 2019-06-04
* Modify  2019-06-04
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_cart_items($self = NULL) {
	$stmt = 'SELECT SUM(`amt`) `amt`
					FROM %ibuy_cart%
					WHERE `uid` = :uid LIMIT 1';

	$total = mydb::select($stmt, ':uid', i()->uid)->amt;

	return number_format($total);
}
?>