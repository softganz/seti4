<?php
/**
* Green :: Rubber My Land
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function green_rubber_my_land($self, $landId = NULL) {
	if ($landId) return R::Page('green.rubber.my.land.view', $self, $landId);

	$orgInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "close | load:#main:'.url('green/rubber/my/land').'"}');

	if (!($orgId = $orgInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$isAdmin = is_admin('green');
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($orgInfo->is->membership,array('NETWORK'));

	$toolbar = new Toolbar($self, $orgInfo->name.' @แปลงสวนยาง');
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	//$ui->add('<a href="'.url('green/rubber/my/land').'"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	if ($isAddLand) {
		$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	}
	//$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="#input"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	$toolbar->addNav('main', $ui);



	mydb::where('l.`orgid` = :orgid', ':orgid', $orgId);
	if (!$isEdit) mydb::where('l.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		l.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `latlng`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%';

	$landList = mydb::select($stmt);

	if (cfg('green')->land->showPlantMenu) {
		$mainUi = new Ui();
		$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
		$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/rubber/form').'" data-rel="box" data-width="480" data-webview="แปลงสวนยาง"><i class="icon -material">add</i><span>ปลูกยาง</span></a>');
		//$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree/form').'" data-rel="box" data-width="480" data-webview="ธนาคารต้นไม้"><i class="icon -material">add</i><span>ปลูกธนาคารต้นไม้</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('green/my/plant/form').'" data-rel="box" data-width="480" data-webview="พืชผสมผสาน"><i class="icon -material">add</i><span>ปลูกพืชผสมผสาน</span></a>');
		//$mainUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="ผู้ติดตาม"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');

		$ret = $mainUi->build();
	}



	$ret .= '<section class="green-land-card">';

	$ret .= '<div id="input"></div>';


	if ($landList->_empty) {
		$ret .= '<p style="padding: 32px; text-align: center;">ยังไม่มีแปลงสวนยางในกลุ่ม</p>';
	} else {
		$landUi = new Ui(NULL, 'ui-card -land');
		$landUi->addConfig('container', '{tag: "div", class: ""}');

		foreach ($landList->items as $rs) {
			$isItemEdit = $isEdit || $rs->uid == i()->uid;
			$linkUrl = url('green/rubber/my/land/'.$rs->landid);

			$cardStr = '<div class="header"><i class="icon -material">nature_people</i><h3>'.$rs->landname.'</h3></div>'
				. '<nav class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดแปลง</a></nav>';
			$landUi->add(
				$cardStr,
				array(
					'class' => 'sg-action',
					'href' => $linkUrl,
					'data-webview' => $rs->landname,
				)
			);
		}

		$ret .= $landUi->build();
	}



	// Show Plant in Land
	mydb::where('p.`orgid` = :orgId', ':orgId', $orgId);
	if (!$isEdit) mydb::where('l.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT p.*, l.`landname` `landName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();


	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-form">'.R::Page('green.my.land.form', NULL).'</div>'
		. '</div>';

	return $ret;
}
?>
