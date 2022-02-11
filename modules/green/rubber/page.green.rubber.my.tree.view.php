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

function green_rubber_my_tree_view($self, $plantId = NULL) {
	$plantInfo = R::Model('green.plant.get', $plantId, '{data: "orgInfo"}');

	if (!$plantInfo) return 'ไม่มีรายการ';

	$orgInfo = $plantInfo->orgInfo;

	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $plantInfo->uid == i()->uid;

	$headerUi = new Ui();
	$dropUi = new Ui();

	if ($isItemEdit) {
		$headerUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree/form/'.$plantId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$headerUi->add('<a class="sg-action" href="'.url('green/my/plant/'.$plantInfo->plantId.'/crop').'" data-rel="box" title="ตัดต้นไม้"><i class="icon -material">content_cut</i></a>');
		if ($plantInfo->msgId) {
			$dropUi->add('<a class="sg-action" href="'.url('green/my/plant/'.$plantInfo->plantId.'/crop').'" data-rel="box"><i class="icon -material">content_cut</i><span>ตัดต้นไม้</span></a>');
			$dropUi->add('<sep>');
			$dropUi->add('<a class="sg-action" href="'.url('green/my/info/activity.delete/'.$plantInfo->msgId).'" data-rel="notify" data-done="back | remove:#green-plant-'.$plantId.'" data-title="ลบ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบรายการ</span></a>');
		}
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$plantInfo->productName.' @'.$plantInfo->info->landName.'</h3><nav class="nav">'.$headerUi->build().'</nav></header>';

	$ret .= '<section id="green-my-tree" data-url="'.url('green/rubber/my/tree/'.$landId).'">';


	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	$cardStr = R::View('green.tree.render', $plantInfo->info, $orgInfo);

	$cardUi->add($cardStr, '{id: "green-plant-'.$plantId.'"}');
	
	$ret .= $cardUi->build();






	/*
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
	//$ret .= print_o($dbs,'$dbs');


	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('green.tree.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "green-plant-'.$rs->plantid.'"}');
	
	}

	$ret .= $cardUi->build();

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-green.-green .page.-content {background-color: transparent;}
	.module-green.-green .ui-card.-plant>.ui-item {margin-bottom: 16px;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	.module.-softganz-app .nav.-back {display: none;}
	</style>';
	*/
	return $ret;
}
?>