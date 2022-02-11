<?php
/**
* Garage Model :: Get Insurer List
* Created 2021-04-01
* Modify  2021-04-01
*
* @param Int $shopId
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("garage.insurers", $shopId, $options)
*
* Get All Insurers Of Shop And All Shop Parent
*/

$debug = true;

function r_garage_insurers($shopId, $options = '{}') {
	$defaults = '{debug: false, result: "item", optionPreList: null}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$dbs = mydb::select(
		'SELECT
			i.*
		, s.`shopname` `shopName`
		, s.`shortname` `shortName`
		, s.`shopparent` `shopParent`
		, s.`shopname` `shopParentName`
		FROM %garage_insurer% i
			LEFT JOIN %garage_shop% s ON s.`shopid` = i.`shopid`
		WHERE i.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = (SELECT `shopparent` FROM %garage_shop% WHERE `shopid` = :shopId))
		ORDER BY CONVERT(i.`insurername` USING tis620)',
		':shopId', $shopId
	);

	if ($options->result == 'option') {
		$result = Array();
		if (is_array($options->optionPreList)) {
			$result = $options->optionPreList;
		}
		foreach ($dbs->items as $rs) {
			$result[$rs->insurerid] = $rs->insurername;
		}
	} else {
		$result = $dbs->items;
	}

	//debugMsg($options, '$options');
	//debugMsg(mydb()->_query);
	//debugMsg($dbs, '$dbs');
	//debugMsg($result,'$result');

	return $result;
}
?>