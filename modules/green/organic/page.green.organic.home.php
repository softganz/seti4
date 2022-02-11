<?php
/**
* Green : Organic Main Page
* Created 2020-10-03
* Modify  2020-10-03
*
* @param Object $self
* @return String
*
* @usage green/organic
*/

$debug = true;

function green_organic_home($self) {

	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	if ($myShopList) {
		$headerNav->add('<a class="btn -hots" href="'.url('green/organic/my').'">บันทึกข้อมูลเกษตรอินทรีย์</a>');
	} else {
		$headerNav->add('<a class="btn -hots" href="'.url('green/organic/register').'">สมัครสมาชิกเกษตรอินทรีย์</a>');
	}

	$ret = '<header class="header"><h3>เกษตรอินทรีย์</h3>'.$headerNav->build().'</header>';


	$ret .= '<section id="green-organic" data-url="'.url('green/organic').'">';

	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,PLANT"');
	if ($getOrg) mydb::where('l.`orgid` = :orgid', ':orgid', $getOrg);
	if ($getLandId) mydb::where('l.`landid` = :landid', ':landid', $getLandId);

	$stmt = 'SELECT
		p.*
		, k.`name` `treeKind`
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
			LEFT JOIN %tag% k ON k.`taggroup` = "tree:kind" AND k.`catid` = p.`catid`
		%WHERE%
		ORDER BY `plantid` DESC
		';

	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	$cardUi = new Ui('div', 'ui-card -plant');

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('green.plant.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "green-plant-'.$rs->plantid.'"}');
	
	}

	$ret .= $cardUi->build();


	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	return $ret;
}
?>