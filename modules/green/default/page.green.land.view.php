<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_land_view($self, $landInfo) {
	if (!($landId = $landInfo->landId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	if (!R()->appAgent) new Toolbar($self,$landInfo->landName.' @Green Smile', NULL,$landInfo);

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	//if (i()->username == 'softganz') $ret .= print_o($landInfo);
	//$ret .= '<header class="header"><h3>แปลงผลิต</h3></header>';

	$ret .= '<section>';

	$topUi = new Ui(NULL,'-sg-flex -nowrap');

	$topLandSelect = array();

	$cardUi = new Ui('div', 'ui-card -land');

	$cameraStr = 'ภาพแปลง';


	$isItemEdit = $isEdit || $landInfo->info->uid == i()->uid;

	$headerUi = new Ui();
	$navUi = new Ui();
	$dropUi = new Ui();
	$goodsUi = new Ui();

	$headerUi->add('<a class="sg-action btn'.($landInfo->info->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'green/standard/set/'.$landInfo->info->landid : 'green/standard/info/'.$landInfo->info->landid).'" data-rel="box" data-width="320">'
		. (in_array($landInfo->info->approved, array('Approve', 'ApproveWithCondition')) ? '<i class="icon -material">'.($landInfo->info->approved == 'Approve' ? 'done_all' : 'done').'</i>' : '')
		. '<span>'.SG\getFirst($landInfo->info->standard,'NONE').'</span></a>'
	);

	$headerUi->add('<sep>');
	if (in_array($landInfo->info->approved, array('Approve','ApproveWithCondition'))) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('green/land/'.$landInfo->info->landid.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
	}
	if ($landInfo->info->location) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('green/land/'.$landInfo->info->landid.'/map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-options=\'{refresh: false}\'><i class="icon -material -land-map -active">where_to_vote</i><span class="-hidden">แผนที่</span></a>');
	}

	if ($isItemEdit) {
		//$dropUi->add('<a class="sg-action" href="'.url('green/my/land/form/'.$landInfo->info->landid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
		//$dropUi->add('<a class="sg-action" href="'.url('green/my/info/land.remove/'.$landInfo->info->landid).'" data-rel="notify" data-done="remove:parent .ui-card.-land>.ui-item" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบแปลงผลิต</span></a>');
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$cardStr = '<div class="header">'._NL
		. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($landInfo->info->username).'" width="32" height="32" alt="" />'._NL
		. '<h3>'.$landInfo->info->landname.'</h3>'._NL
		. '<span class="poster-name">By '.$landInfo->info->ownerName.'</span>'._NL
		. '</span><!-- profile -->'._NL
		. '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL
		. '</div><!-- header -->'._NL;

	$cardStr .= '<div class="detail">'._NL
		. '<p>พื้นที่ '
		. ($landInfo->info->arearai > 0 ? $landInfo->info->arearai.' ไร่ ' : '')
		. ($landInfo->info->areahan > 0 ? $landInfo->info->areahan.' งาน ' : '')
		. ($landInfo->info->areawa > 0 ? $landInfo->info->areawa.' ตารางวา' : '').'<br />'._NL
		. 'มาตรฐาน '.$landInfo->info->standard.' ('.$landInfo->info->approved.')<br />'._NL
		. 'ประเภทผลผลิต '.$landInfo->info->producttype.'</p>'
		. ($landInfo->info->detail ? '<p>'.nl2br($landInfo->info->detail).'</p>' : '')
		. '</div><!-- detail -->'._NL;

	$stmt = 'SELECT * FROM %topic_files% WHERE `orgid` = :orgid AND `tagname` = "GREEN,LAND" AND `refid` = :refid';
	$photoDbs = mydb::select($stmt, ':orgid', $landInfo->shopId, ':refid', $landId);

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
		. '<ul id="ibuy-land-photo-'.$landInfo->info->landid.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div>'._NL;


	if ($isItemEdit) {
		$navUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$landInfo->info->landid).'" data-rel="#ibuy-land-photo-'.$landInfo->info->landid.'" data-append="li"><input type="hidden" name="tagname" value="land" /><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span class="-sg-is-desktop">'.$cameraStr.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');

		$navUi->add('<a class="sg-action btn -link" href="'.url('green/my/land/map/'.$landInfo->info->landid).'" data-rel="box" data-width="640" data-class-name="-map" data-PERMISSION="ACCESS_FINE_LOCATION" data-options=\'{refresh: false}\'><i class="icon -material -land-map '.($landInfo->info->latlng ? '-active' : '').'">'.($landInfo->info->latlng ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');

		//$navUi->add('<a class="sg-action btn -link" href="'.url('green/my/land/standard/'.$landInfo->info->landid).'" data-rel="box" data-width="640"><i class="icon -material">how_to_reg</i><span>มาตรฐาน</span></a>');

		$navUi->add('<a class="sg-action btn" href="'.url('green/my/plant/form',array('land' => $landInfo->info->landid)).'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>ผลผลิตรอบใหม่</span></a>');
	}

	// TODO: BUG ERROR Incorect Current shopId
	//if ($navUi->count()) $cardStr .= '<nav class="nav -card">'.$navUi->build().'</nav>';



	$stmt = 'SELECT
		p.*
		, tg.`name` `categoryName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %tag% tg ON tg.`tid` = p.`catid`
		WHERE `landid` = :landid AND p.`tagname` IN ("GREEN,PLANT")
		ORDER BY `startdate` DESC';
	$plantDbs = mydb::select($stmt, ':landid', $landInfo->info->landid);

	$plantCardUi = new Ui('div', 'ui-card -plant');

	$goodsTables = new Table();
	$goodsTables->thead = array('ผลผลิต', 'start -date' => 'เริ่มลงแปลง', 'crop -date' => 'วันเก็บเกี่ยว', 'qty -amt' => 'ประเภทผลผลิต', 'standard -center -hover-parent' => 'มาตรฐาน');

	foreach ($plantDbs->items as $plantRs) {
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


		// TODO: BUG ERROR Incorect Current shopId
		//$plantCardStr .= '<nav class="nav -card">'.$plantUi->build().'</nav>';

		$plantCardUi->add($plantCardStr, '{class: "'.($plantRs->cropdate <= date('Y-m-d') ? '-croped' : '').'"}');

	}



	$cardStr .= $plantCardUi->build();

	$cardUi->add($cardStr, '{id: "ibuy-land-'.$landInfo->info->landid.'"}');


	if ($isAddLand) {
		$topUi->add('<a class="sg-action btn -primary" href="'.url('green/my/land/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มแปลงผลิต</span></a>');
	}

	$ret .= '<nav class="nav -page -top">'.$topUi->build().'</nav>';

	$ret .= $cardUi->build();

	//$ret .= $tables->build();

	//$ret .= print_o($landInfo,'$landInfo');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.nav.-page .ui-action>.ui-item:last-child {}
	.module-ibuy.-green .page.-content {background-color: transparent;}
	</style>';
	return $ret;
}
?>