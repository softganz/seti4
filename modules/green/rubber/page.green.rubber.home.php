<?php
/**
* Green Rubber : Main Page
* Created 2020-09-28
* Modify  2020-09-28
*
* @param Object $self
* @return String
*
* @usage green/rubber/{$Id}/method
*/

$debug = true;

function green_rubber_home($self) {

	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	if ($myShopList) {
		$headerNav->add('<a class="btn -hots" href="'.url('green/rubber/my').'">บันทึกข้อมูลสวนยางยั่งยืน</a>');
	} else {
		$headerNav->add('<a class="btn -hots" href="'.url('green/rubber/register').'">สมัครสมาชิกสวนยางยั่งยืน</a>');
	}

	$ret = '<header class="header"><h3>สวนยางยั่งยืน</h3>'.$headerNav->build().'</header>';


	$ret .= '<section id="green-my-tree" data-url="'.url('green/my/tree/'.$landId).'">';

	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,TREE"');
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

		$cardStr = R::View('green.tree.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "green-tree-'.$rs->plantid.'"}');
	
	}

	$ret .= $cardUi->build();


	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	return $ret;
}
?>