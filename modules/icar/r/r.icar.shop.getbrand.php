<?php
function r_icar_shop_getbrand($shopid=NULL) {
	$result = array();

	$stmt = 'SELECT `tid`,`name`
		FROM %tag%
		WHERE `taggroup`="icar:brand"
		ORDER BY CONVERT(`name` USING tis620) ASC';

	$dbs = mydb::select($stmt,':shopid',$shopid);

	foreach ($dbs->items as $rs) {
		$result[$rs->tid] = $rs->name;
	}
	//debugMsg($result,'$result');
	return $result;
}
?>