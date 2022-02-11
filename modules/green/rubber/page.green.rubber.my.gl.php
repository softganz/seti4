<?php
/**
* Green Smile : My Animal in Rubber Land
* Created 2020-09-10
* Modify  2020-09-10
*
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function green_rubber_my_gl($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : location('green/my/shop');

	$getLandId = SG\getFirst($landId, post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}
	new Toolbar($self, 'บัญชีต้นทุน @'.$headerTitle,'my.glorg',$landInfo);

	$isAdmin = is_admin('green');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '<p class="notify" style="margin: 32px; padding: 32px;">อยู่ระหว่างการพัฒนา</p>';

	$ret .= '<section id="green-my-animal" data-url="'.url('green/my/animal/'.$landId).'">';


	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	.module-ibuy.-green .ui-card.-plant>.ui-item {margin-bottom: 16px;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';
	return $ret;
}
?>