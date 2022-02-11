<?php
/**
* My GoGreen
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function green_rubber_my_org_view($self, $orgId) {
	$orgId = ($shopInfo = R::Model('green.shop.get', $orgId, '{setShop: true, checkMyShop: true}'))->shopId;

	$toolbar = new Toolbar($self, $shopInfo->name.' @สวนยางยั่งยืน'.($isAdmin ? ' ('.$_SESSION['shopid'].')' : ''));

	//debugMsg('session = '.$_SESSION['shopid']);

	/*
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/rubber/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกไม้</span></a>');
	$toolbar->addNav('main', $ui);
	*/

	if (!$orgId) return message('error', 'SORRY!!!. Group not exists or not your group.');

	$isAdmin = is_admin('green');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isAccessDev = in_array(i()->username, array('softganz','momo'));
	$isLocalHost = _DOMAIN_SHORT == 'localhost';

	$ret = '<section class="green-my-org-view">';

	$mainUi = new Ui();
	$mainUi->addConfig('container', '{tag: "nav", class: "nav -app-menu"}');

	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/land').'" data-webview="แปลงสวนยาง"><i class="icon -material">nature_people</i><span>แปลงสวนยาง</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/rubber').'" data-webview="ต้นยาง"><i class="icon -material">nature</i><span>ต้นยาง</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'" data-webview="ธนาคารต้นไม้"><i class="icon -material">nature</i><span>ธนาคารต้นไม้</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/plant').'" data-webview="พืชผสมผสาน"><i class="icon -material">grass</i><span>พืชผสมผสาน</span></a>');
	//$mainUi->add('<a class="sg-action" href="'.url('green/my/animal').'" data-webview="ปศุสัตว์"><i class="icon -material">emoji_nature</i><span>ปศุสัตว์</span></a>');
	$mainUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/buy').'" data-webview="รับซื้อน้ำยาง"><i class="icon -material">money</i><span>รับซื้อน้ำยาง</span></a>');
	$mainUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/gl').'" data-webview="บัญชีต้นทุน"><i class="icon -material">attach_money</i><span>บัญชีต้นทุน</span></a>');

	$mainUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="ผู้ติดตาม"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');

	$ret .= $mainUi->build();



	$stmt = 'SELECT * FROM %ibuy_farmland% WHERE `orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);


	$landUi = new Ui(NULL, 'ui-card -land');
	$landUi->addConfig('container', '{tag: "div", class: ""}');
	$landUi->header('<h3>แปลงที่ดิน</h3>');
	foreach ($dbs->items as $rs) {
		$linkUrl = url('green/rubber/my/land/'.$rs->landid);
		$cardStr = '<div class="header"><i class="icon -material">nature_people</i><h3>'.$rs->landname.'</h3></div>'
			. '<div class="detail">'
			. '</div>'
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

	mydb::where('p.`orgid` = :orgid', ':orgid', $orgId);
	if (!$isEdit) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT p.*, l.`landname` `landName`, u.`username`, u.`name` `ownerName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`plantid` DESC';
	$dbs = mydb::select($stmt);

	$plantCardUi = R::View('green.my.plant.list', $dbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();

	$ret .= '</section><!-- green-my-org-view -->';

	return $ret;
}
?>
