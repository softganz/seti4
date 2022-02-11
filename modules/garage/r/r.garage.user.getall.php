<?php
function r_garage_user_getall($shopid) {
	$debug=false;
	$result=NULL;
	$stmt='SELECT
					  o.*
					, u.`name`
					FROM %garage_user% o
						LEFT JOIN %users% u USING(`uid`)
					WHERE `shopid`=:shopid
					-- {key:"uid"}';
	$dbs=mydb::select($stmt,':shopid',$shopid);
	$result=$dbs->items;
	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>