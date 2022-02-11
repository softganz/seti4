<?php
function r_icar_shop_getpartner($shopid) {
	$result=array();

	$stmt = 'SELECT `partner`,`name`
		FROM %icarpartner%
		WHERE `shopid`=:shopid
		ORDER BY CONVERT(`name` USING tis620) ASC';

	$dbs = mydb::select($stmt,':shopid',$shopid);

	foreach ($dbs->items as $rs) {
		$result[$rs->partner] = $rs->name;
	}
	//debugMsg($result,'$result');
	//debugMsg(mydb()->_query);
	return $result;
}
?>