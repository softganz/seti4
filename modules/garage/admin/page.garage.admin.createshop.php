<?php
function garage_admin_createshop($self) {
	// Copy car brand
	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;

	$stmt = 'INSERT INTO %garage_brand%
		SELECT :shopid , `brandid` , `brandname` FROM %garage_brand% WHERE `shopid` = 0';

	mydb::query($stmt,':shopid',$shopid);

}
?>