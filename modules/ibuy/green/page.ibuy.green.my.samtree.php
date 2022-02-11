<?php
/**
* Green Smile : My Co-Tree in Rubber Land
* Created 2020-09-10
* Modify  2020-09-10
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function ibuy_green_my_samtree($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$getLandId = SG\getFirst($landId, post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}
	R::View('toolbar',$self, 'พืชแซมยาง @'.$headerTitle,'ibuy.green.my.samtree',$landInfo);

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '';

	$ret .= '<section id="ibuy-green-my-tree" data-url="'.url('ibuy/green/my/tree/'.$landId).'">';

	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,SAMTREE" AND l.`orgid` = :orgid', ':orgid', $shopId);
	if ($getLandId) mydb::where('l.`landid` = :landid', ':landid', $getLandId);

	$stmt = 'SELECT
		p.*
		, m.`msgid`
		, l.`landname` `landName`
		, l.`arearai`, l.`areahan`, l.`areawa`
		, l.`standard` `landStandard`
		, l.`approved` `landApproved`
		, l.`detail` `landDetail`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `landLocation`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %users% u ON u.`uid` = p.`uid`
			LEFT JOIN %msg% m ON m.`tagname` = p.`tagname` AND m.`plantid` = p.`plantid`
		%WHERE%
		ORDER BY `plantid` DESC
		';

	$dbs = mydb::select($stmt);

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('ibuy.green.plant.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "ibuy-plant-'.$rs->plantid.'"}');
	
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
	.module-ibuy.-green .ui-card.-plant>.ui-item {margin-bottom: 16px;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';
	return $ret;
}
?>