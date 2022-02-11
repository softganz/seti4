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

function green_rubber_my_tree($self, $plantId = NULL) {
	// Data Model
	if ($plantId) {
		return R::Page('green.rubber.my.tree.view', $self, $plantId);
	}

	$shopInfo = R::Model('green.shop.get', 'my', '{debug: false}');
	//debugMsg('<pre>'.mydb()->_query.'</pre>');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/rubber/my/tree').'"}');
	//debugMsg('<pre>'.mydb()->_query.'</pre>');
	//debugMsg($orgSelectCard);
	//debugMsg($shopInfo, '$shopInfo');

	if (!($orgId = $shopInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	if (!$shopInfo) {}

	$isAdmin = is_admin('green');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isLandAdmin = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$hasLand = mydb::select('SELECT COUNT(*) `amt` FROM %ibuy_farmland% WHERE `uid` = :uid AND `orgid` = :orgId LIMIT 1', ':uid', i()->uid, ':orgId', $shopInfo->orgId)->amt;
	//debugMsg(mydb()->_query);

	$getLandId = SG\getFirst($landId, post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}


	//View Model
	$ret = '';

	$toolbar = new Toolbar($self, 'ธนาคารต้นไม้ @'.$headerTitle, NULL, $landInfo);

	$ui = new Ui(NULL, 'ui-nav -main');
	$dropUi = new Ui();

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	if ($hasLand || $isAdmin) {
		$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="480"><i class="icon -material">nature_people</i><span>สมุดบัญชี</span></a>');
		$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
		$ui->add('<a class="sg-action -add" href="'.url('green/rubber/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ฝาก</span></a>');
	}

	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการกลุ่ม"><i class="icon -material">settings</i><span>จัดการกลุ่ม</span></a>');
	}

	$toolbar->addNav('main', $ui);
	if ($dropUi->count()) $toolbar->addNav('more', $dropUi);


	//$ret .= $toolbar->build();

	if (!$isAdmin && !$hasLand) {
		$ret .= '<p class="-sg-text-center -sg-paddingmore">ท่านยังไม่เคยเปิดบัญชีธนาคารต้นไม้กับกลุ่ม <b>"'.$shopInfo->name.'" !!!</b><br /><br />กรุณาเปิดบัญชีธนาคารต้นไม้ เพื่อดำเนินการธนาคารต้นไม้ในขั้นตอนต่อไป<br /><br />'
			. '<a class="sg-action btn -primary" href="#green-land-form" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เปิดบัญชีธนาคารต้นไม้</span></a>'
			. '</p>';

		$ret .= '<div class="template -hidden">'
			. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
			. '<div id="green-land-form">'.R::Page('green.my.land.form', NULL).'</div>'
			. '</div>';

		return $ret;
	}

	$ret .= '<section id="green-my-tree" data-url="'.url('green/rubber/my/tree/'.$landId).'">';

	// Show Plant in Land
	mydb::where('p.`orgid` = :orgId AND p.`tagname` = :tagname', ':orgId', $orgId, ':tagname', 'GREEN,TREE');
	if ($getLandId) mydb::where('p.`landid` = :landid', ':landid', $getLandId);
	if (!$isEdit) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = p.`tagname` AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($plantDbs);

	if (!$plantDbs->count()) {
		$ret .= '<p class="-sg-text-center -sg-paddingmore">ยังไม่มีการฝากต้นไม้ในที่บัญชีนี้<br /><br />'
			. '<a class="sg-action btn -primary" href="'.url('green/rubber/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ฝากต้นไม้</span></a>'
			. '</p>';
	} else {
		$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
		$plantCardUi->header('<h3>ต้นไม้</h3>');

		$ret .= $plantCardUi->build();
	}

	/*
	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,TREE" AND l.`orgid` = :orgid', ':orgid', $orgId);
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

	if ($dbs->_empty) $ret .= '<p style="padding: 32px; text-align: center;">ยังไม่มีการปลูกต้นไม้ในที่ดินแปลงนี้</p>';

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('green.tree.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "ibuy-plant-'.$rs->plantid.'"}');

	}

	if ($isAddLand) {
		$topUi->add('<a class="sg-action btn -primary" href="'.url('green/rubber/my/land/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มแปลงผลิต</span></a>');
	}

	$ret .= '<nav class="nav -page -top">'.$topUi->build().'</nav>';

	$ret .= $cardUi->build();
	*/


	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/rubber/my/tree?land=$id"}')->build().'</div>'
		. '</div>';

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	.module-ibuy.-green .ui-card.-plant>.ui-item {margin-bottom: 16px;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshResume: true}
		return options
	}
	function onWebViewResume() {
		//notify("Refresh",500)
		//$("#green-my-tree").sgAction(null,null)
	}
	</script>');
	return $ret;
}
?>