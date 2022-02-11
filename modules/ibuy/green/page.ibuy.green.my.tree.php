<?php
/**
* Green Smile : My Tree Bank
* Created 2020-09-04
* Modify  2020-09-09
*
*
* @param Object $self
* @param Int $landId
* @return String
*
* Ref : https://thaipublica.org/2015/10/sawai-bank-of-trees-1/
* การจัดกลุ่มเนื้อไม้
* กลุ่มที่ 1 ไม้โตเร็วเนื้ออ่อนราคาต่ำ ลูกบาศก์เมตรละ 2,500 บาท ต้นไม้ที่มีอัตราการเติบโตเร็ว รอบตัดฟันสั้น มูลค่าของเนื้อไม้ต่ำ ต้นไม้ในกลุ่มนี้มีอัตราการเติบโตค่อนข้างเร็ว เช่น กระถินเทพา ปอ ทุเรียนบ้าน ฯลฯ
* 
* กลุ่มที่ 2 ไม้โตปานกลางเนื้อปานกลางราคาไม่สูง ราคาลูกบาศก์เมตรละ 5,000 บาท ต้นไม้ที่มีอัตราการเติบโตปานกลาง รอบตัดฟันยาว มูลค่าของเนื้อไม้ค่อนข้างสูง ต้นไม้ในกลุ่มนี้มีอัตราการเติบโตช้ากว่ากลุ่มที่ 1 เช่น มะฮอกกานี พะยอม ยาง ฯลฯ
* 
* กลุ่มที่ 3 ไม้โตค่อนข้างเร็วถึงโตช้า เนื้อแข็ง ลูกบาศก์เมตรละ 7,500 บาท ต้นไม้ในกลุ่มนี้มีอัตราการเติบโตช้ากว่ากลุ่มที่ 1 โดยมีการเติบโตใกล้เคียงกับต้นไม้ในกลุ่มที่ 2 แต่มูลค่าของเนื้อไม้สูงกว่าไม้ในกลุ่มที่ 2 เช่น มะค่าโมง แดง ประดู่ ฯลฯ
* 
* กลุ่มที่ 4 ไม้ราคาสูงเป็นพิเศษ ลูกบาศก์เมตรละ 10,000 บาท ต้นไม้ในกลุ่มนี้มีอัตราการเติบโตช้ามาก ราคาแพง รอบตัดฟันยาว โดยเฉพาะในระยะแรก มีมูลค่าของเนื้อไม้สูงมาก และเติบโตช้ากว่ากลุ่มที่ 1 โดยมีเติบโตใกล้เคียงกับต้นไม้ในกลุ่มที่ 2 แต่มูลค่าของเนื้อไม้สูงกว่าไม้ในกลุ่มที่ 2เช่น พะยูง สัก จำปาทอง ฯลฯ
*/

$debug = true;

function ibuy_green_my_tree($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$getLandId = SG\getFirst($landId, post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}
	R::View('toolbar',$self, 'ธนาคารต้นไม้ @'.$headerTitle,'ibuy.green.my.tree',$landInfo);

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '';

	$ret .= '<section id="ibuy-green-my-tree" data-url="'.url('ibuy/green/my/tree/'.$landId).'">';

	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,TREE" AND l.`orgid` = :orgid', ':orgid', $shopId);
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

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('ibuy.green.tree.render', $rs, $shopInfo);

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