<?php
/**
* Green Smile : My Co-Tree in Rubber Land
* Created 2020-09-10
* Modify  2020-09-10
*
* @param Object $self
* @param Int $plantId
* @return String
*/

$debug = true;

function green_rubber_my_plant($self, $plantId = NULL) {
	$shopInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/rubber/my/plant').'"}');

	if (!($orgId = $shopInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$getLandId = SG\getFirst(post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}

	$isAdmin = is_admin('green');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;



	// Start View

	$toolbar = new Toolbar($self, 'พืชผสมผสาน @'.$headerTitle, NULL, $landInfo);

	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/plant').'"><i class="icon -material">nature</i><span>พืช</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/my/plant/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกพืช</span></a>');
	$toolbar->addNav('main', $ui);

	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการกลุ่ม"><i class="icon -material">settings</i><span>จัดการกลุ่ม</span></a>');
	}
	if ($dropUi->count()) $toolbar->addNav('more', $dropUi);

	$ret = '<section id="green-my-tree" data-url="'.url('green/my/tree/'.$landId).'">';

	// Show Plant in Land
	mydb::where('p.`orgid` = :orgId AND p.`tagname` = :tagname', ':orgId', $orgId, ':tagname', 'GREEN,PLANT');
	if ($getLandId) mydb::where('p.`landid` = :landid', ':landid', $getLandId);
	if (!$isEdit) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = "GREEN,PLANT" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();

	/*
	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,PLANT" AND l.`orgid` = :orgid', ':orgid', $orgId);
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
	//$ret .= mydb()->_query;

	if ($dbs->_empty) $ret .= '<p style="padding: 32px; text-align: center;">ยังไม่มีการปลูกพืชผสมผสานในที่ดินแปลงนี้</p>';

	$cardUi = new Ui('div', 'ui-card -plant');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {

		if (empty($rs->plantid)) continue;
		if ($getLandId && $rs->landid != $getLandId) continue;

		$cardStr = R::View('green.plant.render', $rs, $shopInfo);

		$cardUi->add($cardStr, '{id: "ibuy-plant-'.$rs->plantid.'"}');
	
	}


	$ret .= $cardUi->build();
	*/


	$ret .= '<div class="-hidden">'
		. '<div id="green-org-select">'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/rubber/my/plant?land=$id"}')->build().'</div>'
		. '</div>';

	$ret .= '</section>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshResume: true}
		return options
	}
	</script>');
	
	return $ret;
}
?>