<?php
/**
* My GoGreen
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function green_organic_my_org_view($self, $orgId) {
	$isAdmin = is_admin('green');
	$isAccessDev = in_array(i()->username, array('softganz','momo'));
	$isLocalHost = _DOMAIN_SHORT == 'localhost';

	$orgId = ($shopInfo = R::Model('green.shop.get', $orgId, '{setShop: true, checkMyShop: true}'))->shopId;

	$toolbar = new Toolbar($self, $shopInfo->name.' @Green Smile');

	/*
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/rubber/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกไม้</span></a>');
	$toolbar->addNav('main', $ui);
	*/

	if (!$orgId) return message('error', 'SORRY!!!. Group not exists or not your group.');

	$ret = '<section class="green-my-org-view">';

	$mainUi = new Ui();
	$mainUi->addConfig('container', '{tag: "nav", class: "nav -app-menu"}');
	$mainUi->add('<a class="sg-action" href="'.url('green/organic/my/land').'" data-webview="แปลงที่ดิน"><i class="icon -material">nature_people</i><span>แปลงที่ดิน</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/organic/my/plant').'" data-webview="ผลผลิต"><i class="icon -material">grass</i><span>ผลผลิต</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/my/goods').'" data-webview="สินค้า"><i class="icon -material">local_florist</i><span>สินค้า</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/my/follower').'" data-webview="ผู้ติดตาม"><i class="icon -material">stars</i><span>ผู้ติดตาม</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="กำหนดค่า"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');

	$ret .= $mainUi->build();



	$stmt = 'SELECT * FROM %ibuy_farmland% WHERE `orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);


	$landUi = new Ui(NULL, 'ui-card -land');
	$landUi->addConfig('container', '{tag: "div", class: ""}');
	$landUi->header('<h3>แปลงที่ดิน</h3>');
	foreach ($dbs->items as $rs) {
		$linkUrl = url('green/organic/my/land/'.$rs->landid);
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

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` LIKE "GREEN,%" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON u.`uid` = p.`uid`
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
