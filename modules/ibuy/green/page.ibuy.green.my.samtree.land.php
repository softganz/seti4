<?php
/**
* iBuy Green : My Sam Tree Land
* Created 2020-09-04
* Modify  2020-09-09
*
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_green_my_samtree_land($self) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$getLandId = post('land');

	R::View('toolbar',$self, 'ธนาคารต้นไม้ @'.$shopInfo->name,'ibuy.green.my.tree');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '<header class="header">'._HEADER_BACK.'<h3>แปลงที่ดิน</h3></header>';
	$ret .= '<section>';

	// Get Tree in my Land
	$stmt = 'SELECT
		l.`landid`, l.`landname` `landName`
		, l.`arearai`, l.`areahan`, l.`areawa`
		, l.`standard` `landStandard`
		, l.`approved` `landApproved`
		, l.`detail` `landDetail`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `landLocation`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u ON u.`uid` = l.`uid`
		WHERE l.`orgid` = :orgid
		';

	$dbs = mydb::select($stmt, ':orgid', $shopId);

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$topLandSelect = array();

	$cardUi = new Ui('div', 'ui-card -land');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {
		$cardStr = '<a href="'.url('ibuy/green/my/samtree/'.$rs->landid).'"><h3><i class="icon -material">nature_people</i><span>'.$rs->landName.'</span></h3>'
			. '<p>'.$rs->landDetail.'</p>'
			. '</a>';

		$cardUi->add($cardStr, '{id: "ibuy-land-'.$rs->plantid.'"}');
	
	}

	if ($isAddLand) {
		$topUi->add('<a class="sg-action btn -primary" href="'.url('ibuy/my/land/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มแปลงผลิต</span></a>');
	}

	$ret .= '<nav class="nav -page -top">'.$topUi->build().'</nav>';

	$ret .= $cardUi->build();


	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';
	return $ret;
}
?>