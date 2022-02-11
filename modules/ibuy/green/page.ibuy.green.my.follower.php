<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_my_follower($self) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$ret = '';

	R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');

	$ret .= '<header class="header"><h3>ผู้ติดตาม</h3></header>';

	$tables = new Table();
	$tables->thead = array('ชื่อผู้ติดตาม');

	$ret .= $tables->build();

	//$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn" href="'.url('ibuy/green/shop/'.$orgId.'/field.add').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มสินค้า</span></a></nav>';

	return $ret;
}
?>