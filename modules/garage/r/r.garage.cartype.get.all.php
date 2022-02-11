<?php
/**
* Gara9ge Model :: Get All Car Type
* Created 2020-09-25
* Modify  2020-09-25
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function r_garage_cartype_get_all($shopId = NULL, $options = '{}') {
	$debug = false;
	$defaults = '{value:"templatename"}';
	$options = sg_json_decode($options,$defaults);
	$result = NULL;

	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;
	$shopbranch = array_keys(R::Model('garage.shop.branch',$shopid));

	$stmt='SELECT *
		FROM %garage_cartype% i
		WHERE i.`shopid` = 0 OR i.`shopid` IN (:shopbranch)
		ORDER BY CONVERT(i.`cartypename` USING tis620) ASC
		';

	$dbs = mydb::select($stmt,':shopbranch','SET:'.implode(',',$shopbranch));


	$result = $dbs->items;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>