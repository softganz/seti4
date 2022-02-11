<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_my_land($self) {
	$getLandId = post('land');
	$ret = '';

	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	//$ret .= print_o($shopInfo);
	//$ret .= '<header class="header"><h3>แปลงผลิต</h3></header>';

	$ret .= '<section>';

	$stmt = 'SELECT
		l.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `latlng`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		WHERE l.`orgid` = :orgid';

	$dbs = mydb::select($stmt, ':orgid', $shopId);

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

	$cardUi = new Ui('div', 'ui-card -land');

	$cameraStr = 'ภาพแปลง';

	foreach ($dbs->items as $rs) {
		$topLandSelect[$rs->landid] = $rs->landname;

		if ($getLandId && $rs->landid != $getLandId) continue;

		//$cardStr = R::Model('ibuy.land.render', $rs, $shopInfo);

		$isItemEdit = $isEdit || $rs->uid == i()->uid;

		$headerUi = new Ui();
		$navUi = new Ui();
		$dropUi = new Ui();
		$goodsUi = new Ui();

		$headerUi->add('<a class="sg-action btn'.($rs->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'ibuy/green/standard/set/'.$rs->landid : 'ibuy/green/standard/info/'.$rs->landid).'" data-rel="box" data-width="320">'.SG\getFirst($rs->standard,'NONE').'</a>');
			if (in_array($rs->approved, array('Approve','ApproveWithCondition'))) {
			$headerUi->add('<a class="sg-action btn -link" href="'.url('ibuy/green/land/'.$rs->landid.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
		}

		if ($isItemEdit) {
			$dropUi->add('<a class="sg-action" href="'.url('ibuy/my/land/form/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
			$dropUi->add('<a class="sg-action" href="'.url('ibuy/my/info/land.remove/'.$rs->landid).'" data-rel="notify" data-done="remove:parent .ui-card.-land>.ui-item" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบแปลงผลิต</span></a>');
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

		$stmt = 'SELECT * FROM %topic_files% WHERE `orgid` = :orgid AND `tagname` = "GREEN,LAND" AND `refid` = :refid';
		$photoDbs = mydb::select($stmt, ':orgid', $shopId, ':refid', $rs->landid);

		$photoStr = '';
		foreach ($photoDbs->items as $item) {
			$photoStrItem = '';
			$ui = new Ui('span');

			if ($item->type == 'photo') {
				//$ret.=print_o($item,'$item');
				$photoInfo=model::get_photo_property($item->file);

				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('ibuy/my/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
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
					$ui->add('<a class="sg-action" href="'.url('ibuy/my/info/docs.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
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
				'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$rs->landid).'" data-rel="#ibuy-land-photo-'.$rs->landid.'" data-append="li">'
				. '<input type="hidden" name="module" value="GREEN" />'
				. '<input type="hidden" name="tagname" value="LAND" />'
				. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span class="-sg-is-desktop">'.$cameraStr.'</span>'
				. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
				. '<input class="-hidden" type="submit" value="upload" />'
				. '</form>'
			);
		}

		$navUi->add('<a class="sg-action btn -link" href="'.url('ibuy/green/land/'.$rs->landid.'/map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-refresh="no"><i class="icon -material -land-map '.($rs->latlng ? '-active' : '').'">'.($rs->latlng ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');

			//$navUi->add('<a class="sg-action btn -link" href="'.url('ibuy/my/land/standard/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">how_to_reg</i><span>มาตรฐาน</span></a>');

		if ($isItemEdit) {
			$navUi->add('<a class="sg-action btn" href="'.url('ibuy/my/plant/form',array('land' => $rs->landid)).'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>ผลผลิตรอบใหม่</span></a>');
		}

		if ($navUi->count()) $cardStr .= '<nav class="nav -card">'.$navUi->build().'</nav>';



		// Show Plant in Land
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

		$plantCardUi = new Ui('div', 'ui-card -plant');

		$goodsTables = new Table();
		$goodsTables->thead = array('ผลผลิต', 'start -date' => 'เริ่มลงแปลง', 'crop -date' => 'วันเก็บเกี่ยว', 'qty -amt' => 'ประเภทผลผลิต', 'standard -center -hover-parent' => 'มาตรฐาน');

		foreach ($plantDbs->items as $plantRs) {
			$plantUi = new Ui();
			if ($isItemEdit) {
				$plantUi->add(
					'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$plantRs->plantid).'" data-rel="#ibuy-plant-photo-'.$plantRs->plantid.'" data-append="li">'
					. '<input type="hidden" name="module" value="GREEN" />'
					. '<input type="hidden" name="tagname" value="PLANT" />'
					. '<span class="btn -link fileinput-button"><i class="icon -material -gray">add_a_photo</i><span class="-sg-is-desktop">ภาพผลผลิต</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
					. '<input class="-hidden" type="submit" value="upload" />'
					. '</form>'
				);
				$plantUi->add('<a class="sg-action btn -link" href="'.url('ibuy/my/plant/form/'.$plantRs->plantid, array('land' => $plantRs->landid)).'" data-rel="box" data-width="640"><i class="icon -material -gray">edit</i><span class="-sg-is-desktop">แก้ไขผลผลิต</span></a>');
				$plantUi->add('<a class="sg-action btn -link" href="'.url('ibuy/my/info/plant.remove/'.$plantRs->plantid).'" data-rel="notify" data-done="remove:parent .ui-card.-plan>.ui-item" data-title="ลบผลผลิต" data-confirm="ต้องการลบผลผลิต กรุณายืนยัน?"><i class="icon -material -gray">delete</i><span class="-sg-is-desktop">ลบผลผลิต</span></a>');
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
						$ui->add('<a class="sg-action" href="'.url('ibuy/my/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
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



		$cardStr .= $plantCardUi->build();

		$cardUi->add($cardStr, '{id: "ibuy-land-'.$rs->landid.'"}');

	
		/*
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('ibuy/my/land/form/'.$rs->landid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/my/info/land.remove/'.$rs->landid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i></a>');

		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';

		$tables->rows[] = array(
			$rs->landname,
			$rs->arearai,
			'<a class="sg-action" href="'.url('ibuy/my/land/map/'.$rs->landid).'" data-rel="box" data-width="640" data-class-name="-map"><i class="icon -material '.($rs->latlng ? '-green' : '-gray').'">'.($rs->latlng ? 'where_to_vote' : 'room').'</i></a>',
			$rs->standard,
			$rs->approved
			.$menu,
		);
		*/
	}



	$form = new Form(NULL, url('ibuy/green/my/land'));
	$form->addConfig('method','get');

	if ($topLandSelect) {
		$form->addField(
			'land',
			array(
				'type' => 'select',
				'options' => $topLandSelect,
				'value' => $getLandId,
				'attr' => array('onChange' => '$(this).closest(\'form\').submit()'),
			)
		);
	} else {
		$form->addText('*** ยังไม่มีแปลงผลิต ***');
	}

	$topUi->add($form->build());

	if ($isAddLand) {
		$topUi->add('<a class="sg-action btn -primary" href="'.url('ibuy/my/land/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มแปลงผลิต</span></a>');
	}

	$ret .= '<nav class="nav -page -top">'.$topUi->build().'</nav>';

	$ret .= $cardUi->build();

	//$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	.icon.-material.-land-map {color: gray;}
	.icon.-material.-land-map.-active {color: green;}
	</style>';
	return $ret;
}
?>