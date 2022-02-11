<?php
/**
* Green :: My Organic Land
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function green_organic_my_land($self, $landId = NULL) {
	if ($landId) return R::Page('green.organic.my.land.view', $self, $landId);

	$orgInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "close | reload:'.url('green/organic/my/land').'"}');

	if (!($orgId = $orgInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$siteTitle = 'แปลงที่ดิน';

	$isAdmin = is_admin('green');
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($orgInfo->is->membership,array('NETWORK'));

	$toolbar = new Toolbar($self, $orgInfo->name.' @เกษตรอินทรีย์');
	$toolbarNav = new Ui(NULL, 'ui-nav -main');

	$toolbarNav->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	//$toolbarNav->add('<a href="'.url('green/rubber/my/land').'"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	//$toolbarNav->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	if ($isAddLand) {
		$toolbarNav->add('<a class="sg-action -add" href="#green-land-form" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	}
	//$toolbarNav->add('<a class="sg-action -add" href="#green-land-form" data-rel="#input"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	$toolbar->addNav('main', $toolbarNav);


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
		$mainUi->addConfig('container', '{tag: "nav", class: "nav -app-menu green-land-menu"}');
		$mainUi->add('<a class="sg-action" href="'.url('green/my/plant/form').'" data-rel="box" data-width="480" data-webview="ปลูกผัก"><i class="icon -material">add</i><span>ปลูกผัก</span></a>');

		$ret = $mainUi->build();
	}



	$ret .= '<section class="green-land-card">';

	$ret .= '<div id="input"></div>';


	if ($landList->_empty) {
		$ret .= '<p style="padding: 32px; text-align: center;">ยังไม่มีแปลงที่ดินในกลุ่ม</p>';
	} else {
		$landUi = new Ui(NULL, 'ui-card -land');
		$landUi->addConfig('container', '{tag: "div", class: ""}');

		foreach ($landList->items as $rs) {
			$isItemEdit = $isEdit || $rs->uid == i()->uid;
			$linkUrl = url('green/organic/my/land/'.$rs->landid);

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
	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` LIKE "GREEN,%" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON u.`uid` = p.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();


	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-form">'.R::Page('green.my.land.form', NULL).'</div>'
		. '</div>';

	$ret .= '</section><!-- green-land-card -->';


	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshResume: true}
		return options
	}
	function onWebViewResume() {
		console.log("CALL onWebViewResume")
	}

	</script>');

	return $ret;
















	$getLandId = SG\getFirst($landId, post('land'));
	$ret = '';
	//debugMsg('SHOP SESSION = '.$_SESSION['shopid']);

	//if (!($shopId = ($shopInfo = R::Model('green.shop.get', 'my', '{debug: true}')->shopId))) return R::Page('green.my.shop.select', null);

	$shopInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/organic/my/land').'"}');

	if (!($shopId = $shopInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$siteTitle = 'แปลงที่ดิน @'.$shopInfo->name;
	$toolbar = new Toolbar($self, $siteTitle);
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="480"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	if ($shopId) {
		//$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="box"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
		$ui->add('<a class="sg-action -add" href="'.url('green/my/land/form').'" data-rel="box"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	}
	//$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="#input"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	$toolbar->addNav('main', $ui);

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	//$ret .= 'SHOP ID = '.$shopId.'<br />';
	//$ret .= print_o($shopInfo, '$shopInfo');
	//$ret .= '<header class="header"><h3>แปลงผลิต</h3></header>';

	$stmt = 'SELECT
		l.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `latlng`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		WHERE l.`orgid` = :orgid';

	$dbs = mydb::select($stmt, ':orgid', $shopId);


	$ret .= '<section>';

	$tables = new Table();
	$tables->thead = array(
		'ชื่อแปลง',
		'area -amt' => 'พื้นที่',
		'map -center'=>'',
		'standard -center' => 'มาตรฐาน',
		'status -hover-parent' => 'Approve',
	);

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$topLandSelect = array();

	$cardUi = new Ui('div', 'ui-card green-land-card');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {
		$topLandSelect[$rs->landid] = $rs->landname;

		if ($getLandId && $rs->landid != $getLandId) continue;

		//$cardStr = R::Model('ibuy.land.render', $rs, $shopInfo);

		$stmt = 'SELECT
			p.*
			, m.`msgid`
			, tg.`name` `categoryName`
			FROM %ibuy_farmplant% p
				LEFT JOIN %msg% m ON m.`tagname` = p.`tagname` AND m.`plantid` = p.`plantid`
				LEFT JOIN %tag% tg ON tg.`tid` = p.`catid`
			WHERE p.`landid` = :landid AND p.`tagname` = "GREEN,PLANT"
			ORDER BY p.`startdate` DESC';

		$plantDbs = mydb::select($stmt, ':landid', $rs->landid);

		$isItemEdit = $isEdit || $rs->uid == i()->uid;

		$headerUi = new Ui();
		$navUi = new Ui();
		$dropUi = new Ui();
		$goodsUi = new Ui();

		$headerUi->add('<a class="sg-action btn'.($rs->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'green/standard/set/'.$rs->landid : 'green/standard/info/'.$rs->landid).'" data-rel="box" data-width="320">'.SG\getFirst($rs->standard,'NONE').'</a>');
			if (in_array($rs->approved, array('Approve','ApproveWithCondition'))) {
			$headerUi->add('<a class="sg-action btn -link" href="'.url('green/land/'.$rs->landid.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
		}

		if ($isItemEdit) {
			$dropUi->add('<a class="sg-action" href="'.url('green/my/land/form/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
			if (!$plantDbs->count()) {
				$dropUi->add('<a class="sg-action" href="'.url('green/my/info/land.remove/'.$rs->landid).'" data-rel="notify" data-done="remove:parent .ui-card.-land>.ui-item" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบแปลงผลิต</span></a>');
			}
		}

		if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

		$cardStr = '<div class="header">'._NL
			. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="" />'._NL
			. '<h3>'.$rs->landname.'</h3>'._NL
			. '<span class="poster-name">By '.$rs->ownerName.'</span>'
			. '</span><!-- profile -->'._NL
			. '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL
			. '</div><!-- header -->'._NL;

		$cardStr .= '<div class="detail">'._NL
			. '<p>พื้นที่ '
			. ($rs->arearai > 0 ? $rs->arearai.' ไร่ ' : '')
			. ($rs->areahan > 0 ? $rs->areahan.' งาน ' : '')
			. ($rs->areawa > 0 ? $rs->areawa.' ตารางวา' : '').'<br />'._NL
			. 'มาตรฐาน '.$rs->standard.' ('.$rs->approved.')<br />'._NL
			. 'ประเภทผลผลิต '.$rs->producttype.'</p>'
			. ($rs->detail ? '<p>'.nl2br($rs->detail).'</p>' : '')
			. '</div><!-- detail -->'._NL;

		$stmt = 'SELECT * FROM %topic_files% WHERE `tagname` = "GREEN,LAND" AND `refid` = :refid';
		$photoDbs = mydb::select($stmt, ':orgid', $shopId, ':refid', $rs->landid);

		$photoStr = '';
		foreach ($photoDbs->items as $item) {
			$photoStrItem = '';
			$ui = new Ui('span');

			if ($item->type == 'photo') {
				//$ret.=print_o($item,'$item');
				$photoInfo=model::get_photo_property($item->file);

				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('green/my/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
				}

				$photo_alt = $item->title;
				$photoStrItem .= '<li class="ui-item -hover-parent">';

				$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

				$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />';
				//$photoStrItem .= '<img class="photo -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				//$photoStrItem .= ' />';
				$photoStrItem .= '</a>';

				$photoStrItem .= '</li>'._NL;

				$photoStr .= $photoStrItem;

			} else if ($item->type == 'doc') {
				$docStr .= '<li class="-hover-parent">';
				$docStr .= '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'">';
				$docStr .= '<img class="doc-logo -pdf" src="http://img.softganz.com/icon/icon-file.png" width="63" style="display: block; padding: 16px; margin: 0 auto; background-color: #eee; border-radius: 50%;" />';
				$docStr .= $item->title;
				$docStr .= '</a>';

				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('green/my/info/docs.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
				}
				$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
				$docStr .= '</li>';
			}
		}


		$cardStr .= '<div class="-photolist -action">'._NL
			. '<ul id="ibuy-land-photo-'.$rs->landid.'" class="ui-album">'._NL
			. $photoStr
			. '</ul>'._NL
			. '</div>'._NL;


		if ($isItemEdit) {
			$navUi->add(
				'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$rs->landid).'" data-rel="#ibuy-land-photo-'.$rs->landid.'" data-append="li">'
				. '<input type="hidden" name="orgid" value="'.$rs->orgid.'" />'
				. '<input type="hidden" name="module" value="GREEN" />'
				. '<input type="hidden" name="tagname" value="LAND" />'
				. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span class="-sg-is-desktop">'.$cameraStr.'</span>'
				. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
				. '<input class="-hidden" type="submit" value="upload" />'
				. '</form>'
			);
		}

		$navUi->add('<a class="sg-action btn -link" href="'.url('green/my/land/map/'.$rs->landid, array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-options=\'{refresh: false}\'><i class="icon -material -land-map '.($rs->latlng ? '-active' : '').'">'.($rs->latlng ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');

			//$navUi->add('<a class="sg-action btn -link" href="'.url('green/my/land/standard/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">how_to_reg</i><span>มาตรฐาน</span></a>');

		if ($isItemEdit) {
			$navUi->add('<a class="sg-action btn" href="'.url('green/my/plant/form',array('land' => $rs->landid)).'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>ผลผลิตรอบใหม่</span></a>');
		}

		if ($navUi->count()) $cardStr .= '<nav class="nav -card">'.$navUi->build().'</nav>';



		// Show Plant in Land
		if ($plantDbs->count()) {
			$cardStr .= '<header class="header"><h3>ผลผลิต</h3></header>';
		}
		$plantCardUi = new Ui('div', 'ui-card -plant');

		$goodsTables = new Table();
		$goodsTables->thead = array('ผลผลิต', 'start -date' => 'เริ่มลงแปลง', 'crop -date' => 'วันเก็บเกี่ยว', 'qty -amt' => 'ประเภทผลผลิต', 'standard -center -hover-parent' => 'มาตรฐาน');

		foreach ($plantDbs->items as $plantRs) {
			$isCroped = $plantRs->cropdate <= date('Y-m-d');
			$linkUrl = url('green/my/plant/view/'.$plantRs->plantid);
			$plantCardUi->add(
				'<div class="header"><h5>'.$plantRs->productname.($isCroped ? ' <span>(เก็บเกี่ยวแล้ว)</span>' : '').'</h5></div>'
				. '<div class="detail">'
				. ($plantRs->startdate ? 'เริ่มลงแปลง '.sg_date($plantRs->startdate) : '')
				. ($plantRs->cropdate ? ' วันเก็บเกี่ยว '.sg_date($plantRs->cropdate) : '')
				. '</div>'
				. '<div class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดผลผลิต</a></div>',
				array(
					'class' => 'sg-action'.($isCroped ? ' -croped' : ''),
					'href' => $linkUrl,
					'data-rel' => 'box',
					'data-width' => '640',
					'data-height' => '90%',
					'data-webview' => $rs->productname,
				)
			);
		}


		/*
		foreach ($plantDbs->items as $plantRs) {
			$plantUi = new Ui();
			if ($isItemEdit) {
				$plantUi->add(
					'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$plantRs->plantid).'" data-rel="#ibuy-plant-photo-'.$plantRs->plantid.'" data-append="li">'
					. '<input type="hidden" name="module" value="GREEN" />'
					. '<input type="hidden" name="tagname" value="PLANT" />'
					. '<span class="btn -link fileinput-button"><i class="icon -material -gray">add_a_photo</i><span class="-sg-is-desktop">ภาพผลผลิต</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
					. '<input class="-hidden" type="submit" value="upload" />'
					. '</form>'
				);
				$plantUi->add('<a class="sg-action btn -link" href="'.url('green/my/plant/form/'.$plantRs->plantid, array('land' => $plantRs->landid)).'" data-rel="box" data-width="640"><i class="icon -material -gray">edit</i><span class="-sg-is-desktop">แก้ไขผลผลิต</span></a>');
				$plantUi->add('<a class="sg-action btn -link" href="'.url('green/my/info/plant.remove/'.$plantRs->plantid).'" data-rel="notify" data-done="remove:parent .ui-card.-plan>.ui-item" data-title="ลบผลผลิต" data-confirm="ต้องการลบผลผลิต กรุณายืนยัน?"><i class="icon -material -gray">delete</i><span class="-sg-is-desktop">ลบผลผลิต</span></a>');
			}
			$productMenu = '<nav class="nav -icons -hover">'.$plantUi->build().'</nav>';

			$plantCardStr = '<div class="header"><h5>'.$plantRs->productname.' ('.$plantRs->categoryName.')'.'</h5></div>';
			$plantCardStr .= '<div class="detail">'
				. '<p>เริ่มลงแปลง '.($plantRs->startdate ? sg_date($plantRs->startdate, 'ว ดด ปปปป') : '').' '
				. 'วันเก็บเกี่ยว '.($plantRs->cropdate ? sg_date($plantRs->cropdate, 'ว ดด ปปปป') : '').' '
				. 'ปริมาณผลผลิต '.$plantRs->qty.' '.$plantRs->unit
				. '</p>'
				. ($plantRs->detail ? '<p>'.nl2br($plantRs->detail).'</p>' : '')
				. '</div>';


			$stmt = 'SELECT * FROM %topic_files% WHERE `orgid` = :orgid AND `tagname` = "GREEN,PLANT" AND `refid` = :refid';
			$photoDbs = mydb::select($stmt, ':orgid', $shopId, ':refid', $plantRs->plantid);

			$photoStr = '';
			foreach ($photoDbs->items as $item) {
				$photoStrItem = '';
				$ui = new Ui('span');

				if ($item->type == 'photo') {
					//$ret.=print_o($item,'$item');
					$photoInfo=model::get_photo_property($item->file);

					if ($isItemEdit) {
						$ui->add('<a class="sg-action" href="'.url('green/my/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
					}

					$photo_alt = $item->title;
					$photoStrItem .= '<li class="ui-item -hover-parent">';

					$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

					$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
					$photoStrItem .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />';
					//$photoStrItem .= ' />';
					$photoStrItem .= '</a>';

					$photoStrItem .= '</li>'._NL;

					$photoStr .= $photoStrItem;

				}
			}


			$plantCardStr .= '<div class="-photolist -action">'._NL
				. '<ul id="ibuy-plant-photo-'.$plantRs->plantid.'" class="ui-album">'._NL
				. $photoStr
				. '</ul>'._NL
				. '</div>'._NL;


			$plantCardStr .= '<nav class="nav -card">'.$plantUi->build().'</nav>';

			$plantCardUi->add($plantCardStr, '{class: "'.($plantRs->cropdate <= date('Y-m-d') ? '-croped' : '').'"}');

		}
		*/



		$cardStr .= $plantCardUi->build();

		$cardUi->add($cardStr, '{id: "ibuy-land-'.$rs->landid.'"}');

	
		/*
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('green/my/land/form/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$ui->add('<a class="sg-action" href="'.url('green/my/info/land.remove/'.$rs->landid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i></a>');

		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';

		$tables->rows[] = array(
			$rs->landname,
			$rs->arearai,
			'<a class="sg-action" href="'.url('green/my/land/map/'.$rs->landid).'" data-rel="box" data-width="640" data-class-name="-map" data-options=\'{refresh: false}\'><i class="icon -material '.($rs->latlng ? '-green' : '-gray').'">'.($rs->latlng ? 'where_to_vote' : 'room').'</i></a>',
			$rs->standard,
			$rs->approved
			.$menu,
		);
		*/
	}



	$ret .= $cardUi->build();

	//$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.my.land.select', NULL, '{retUrl: "green/organic/my/land/$id"}').'</div>'
		//. '<div id="green-land-form">'.R::Page('green.my.land.form', NULL).'</div>'
		. '</div>';

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {title: "'.$siteTitle.'", refreshResume: true}
		menu = []
		//menu.push({id: "add", label: "สร้างกลุ่ม", title: "สร้างกลุ่ม", link: "green/my/org/new?ref=green/organic/my/land", options: {actionBar: true}})
		//options.menu = menu
		return options
	}
	</script>');
	return $ret;
}
?>
