<?php
/**
* Garage Model :: Get Shop Branch List
* Created 2017-04-20
* Modify  2021-01-01
*
* @param Int $shopId
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("garage.shop.branch", $shopId, $options)
*/

$debug = true;

function r_garage_shop_branch($shopId, $options = '{}') {
	$defaults = '{debug: false, result: "item", value: "name"}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;
	$stmt = 'SELECT s.*
			FROM %garage_shop% s
			WHERE s.`shopid` = :shopId OR s.`shopparent` = :shopId
		UNION
		SELECT s.*
			FROM %garage_shop% s
			WHERE `shopid` = (SELECT `shopparent` FROM %garage_shop% WHERE `shopid` = :shopId)
		GROUP BY `shopid`
		ORDER BY `shopid` ASC;
		-- {key:"shopid"}';

	$dbs = mydb::select($stmt, ':shopId', $shopId);

	if ($options->result == 'option') {
		$result = Array();
		foreach ($dbs->items as $rs) {
			if ($options->value == 'shortName') {
				$result[$rs->shopid] = $rs->shortname;
			} else {
				$result[$rs->shopid] = $rs->shopname;
			}
		}
	} else {
		$result = $dbs->items;
	}

	//debugMsg($options, '$options');
	//debugMsg($result,'$result');

	return $result;
}
?>