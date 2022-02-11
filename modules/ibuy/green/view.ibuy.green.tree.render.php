<?php
/**
* ibuy : Green Tree Bank Render
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $treeInfo
* @param Object $options
* @return String
*/

$debug = true;

function view_ibuy_green_tree_render($treeInfo, $options = '{}') {
	$ret = '';

	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $treeInfo->uid == i()->uid;

	$headerUi = new Ui();
	$navUi = new Ui();
	$dropUi = new Ui();
	$goodsUi = new Ui();

	//$headerUi->add('<a class="sg-action btn'.($treeInfo->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'ibuy/green/standard/set/'.$treeInfo->landid : 'ibuy/green/standard/info/'.$treeInfo->landid).'" data-rel="box" data-width="320">'.SG\getFirst($treeInfo->standard,'NONE').'</a>');

	/*
	if (in_array($treeInfo->approved, array('Approve','ApproveWithCondition'))) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('ibuy/green/land/'.$treeInfo->landid.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
	}
	*/

	if ($isItemEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('ibuy/my/tree/form/'.$treeInfo->plantid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('ibuy/my/info/activity.delete/'.$treeInfo->msgid).'" data-rel="notify" data-done="remove:parent .ui-card.-plant>.ui-item" data-title="ลบต้นไม้" data-confirm="ต้องการลบต้นไม้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบต้นไม้</span></a>');
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$ret = '<div class="header">'._NL
		. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($treeInfo->username).'" width="32" height="32" alt="" />'._NL
		. '<h3>'.$treeInfo->productname.' ('.$treeInfo->treeKind.')@'.$treeInfo->landName.'</h3>'._NL
		. '<span class="poster-name">By '.$treeInfo->ownerName.' @'.sg_date($treeInfo->created, 'd/m/ปปปป').'</span>'
		. '</span><!-- profile -->'._NL
		. '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL
		. '</div><!-- header -->'._NL;

	$ret .= '<div class="detail">'._NL
		. '<p>'
		. ($treeInfo->productcode ? 'หมายเลขต้นไม้ <b>'.$treeInfo->productcode.'</b><br />' : '')
		. ($treeInfo->startdate ? 'เริ่มปลูก '.sg_date($treeInfo->startdate, 'ว ดด ปปปป') : '')
		. ($treeInfo->round ? ' เส้นรอบวง '.$treeInfo->round.' เมตร' : '')
		. ($treeInfo->height ? ' ความสูง '.$treeInfo->height.' เมตร' : '')
		. '<br />'
		. ( $treeInfo->arearai || $treeInfo->arearai || $treeInfo->arearai
			?
			'พื้นที่ '
			. ($treeInfo->arearai > 0 ? $treeInfo->arearai.' ไร่ ' : '')
			. ($treeInfo->areahan > 0 ? $treeInfo->areahan.' งาน ' : '')
			. ($treeInfo->areawa > 0 ? $treeInfo->areawa.' ตารางวา' : '').'<br />'._NL
			: ''
			)
		// . 'มาตรฐาน '.$treeInfo->standard.' ('.$treeInfo->approved.')<br />'._NL
		. '</p>'
		. ($treeInfo->landDetail ? '<p>'.nl2br($treeInfo->landDetail).'</p>' : '')
		. '</div><!-- detail -->'._NL;

	if ($treeInfo->round) {
		$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "GREEN,TREE" AND `keyid` = :plantid';
		$roundDbs = mydb::select($stmt, ':plantid', $treeInfo->plantid);

		$tables = new Table();
		$tables->thead = array(
			'created -date' => 'วันที่',
			'round -amt' => 'เส้นรอบวง(เมตร)',
			'height -amt -hover-parent' => 'ความสูง(เมตร)'
		);
		foreach ($roundDbs->items as $rs) {
			$roundMenu = new Ui();
			$roundMenu->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isItemEdit || $rs->ucreated == i()->uid) {
				$roundMenu->add('<a class="sg-action" href="'.url('ibuy/my/info/tree.round.remove/'.$rs->bigid).'" data-rel="notify" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">cancel</i></a>');
			}

			$data = json_decode($rs->flddata);
			$tables->rows[] = array(
				sg_date($rs->created, 'ว ดด ปปปป'),
				number_format($data->round,2),
				($data->height ? number_format($data->height,2) : '-')
				. $roundMenu->build(),
			);
		}
		$ret .= $tables->build();
	}


	// Show Tree Photo
	$stmt = 'SELECT * FROM %topic_files% WHERE `tagname` = "GREEN,TREE" AND `refid` = :refid';
	$photoDbs = mydb::select($stmt, ':refid', $treeInfo->msgid);

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

	$ret .= '<div class="-photolist -action">'._NL
		. '<ul id="ibuy-plant-photo-'.$treeInfo->msgid.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div>'._NL;




	if ($isItemEdit) {
		//$navUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$plantRs->plantid).'" data-rel="#ibuy-plant-photo-'.$plantRs->plantid.'" data-append="li"><input type="hidden" name="tagname" value="plant" /><span class="btn -link fileinput-button"><i class="icon -material -gray">add_a_photo</i><span class="-sg-is-desktop">ภาพผลผลิต</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
		$navUi->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$treeInfo->msgid).'" data-rel="#ibuy-plant-photo-'.$treeInfo->msgid.'" data-append="li">'
			. '<input type="hidden" name="module" value="GREEN" />'
			. '<input type="hidden" name="tagname" value="TREE" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i><span class="-sg-is-desktop">'.$cameraStr.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>'
		);

		$navUi->add('<a class="sg-action btn -link" href="'.url('ibuy/green/my/tree/round/'.$treeInfo->plantid).'" data-rel="box" data-width="480" title="บันทึกเส้นรอบวง"><i class="icon -material">donut_large</i><span class="-sg-is-desktop">เส้นรอบวง</span></a>');
	}

	$navUi->add('<a class="sg-action btn -link" href="'.url('ibuy/green/my/tree/map/'.$treeInfo->landid, array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-refresh="no" title="พิกัดต้นไม้"><i class="icon -material -land-map '.($treeInfo->landLocation ? '-active' : '').'">'.($treeInfo->landLocation ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');


	if ($navUi->count()) $ret .= '<nav class="nav -card">'.$navUi->build().'</nav>';


	/*
	$stmt = 'SELECT
		p.*
		, tg.`name` `categoryName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %tag% tg ON tg.`tid` = p.`catid`
		WHERE `landid` = :landid
		ORDER BY `startdate` DESC';
	$plantDbs = mydb::select($stmt, ':landid', $treeInfo->landid);

	$plantCardUi = new Ui('div', 'ui-card -plant');

	$goodsTables = new Table();
	$goodsTables->thead = array('ผลผลิต', 'start -date' => 'เริ่มลงแปลง', 'crop -date' => 'วันเก็บเกี่ยว', 'qty -amt' => 'ประเภทผลผลิต', 'standard -center -hover-parent' => 'มาตรฐาน');

	foreach ($plantDbs->items as $plantRs) {
		$plantUi = new Ui();
		if ($isItemEdit) {
			$plantUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$plantRs->plantid).'" data-rel="#ibuy-plant-photo-'.$plantRs->plantid.'" data-append="li"><input type="hidden" name="tagname" value="plant" /><span class="btn -link fileinput-button"><i class="icon -material -gray">add_a_photo</i><span class="-sg-is-desktop">ภาพผลผลิต</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
			$plantUi->add('<a class="sg-action btn -link" href="'.url('ibuy/my/plant/form/'.$plantRs->plantid, array('land' => $plantRs->landid)).'" data-rel="box" data-width="640"><i class="icon -material -gray">edit</i><span class="-sg-is-desktop">แก้ไขผลผลิต</span></a>');
			$plantUi->add('<a class="sg-action btn -link" href="'.url('ibuy/my/info/plant.remove/'.$plantRs->plantid).'" data-rel="notify" data-done="remove:parent .ui-card.-plan>.ui-item" data-title="ลบผลผลิต" data-confirm="ต้องการลบผลผลิต กรุณายืนยัน?"><i class="icon -material -gray">delete</i><span class="-sg-is-desktop">ลบผลผลิต</span></a>');
		}
		$productMenu = '<nav class="nav -icons -hover">'.$plantUi->build().'</nav>';

		$plantCardStr = '<div class="header"><h5>'.$plantRs->productname.' ('.$plantRs->categoryName.')'.'</h5></div>';
		$plantCardStr .= '<div class="detail">'
			. 'วันเก็บเกี่ยว '.($plantRs->cropdate ? sg_date($plantRs->cropdate, 'ว ดด ปปปป') : '').' '
			. 'ปริมาณผลผลิต '.$plantRs->qty.' '.$plantRs->unit
			. '</p>'
			. ($plantRs->detail ? '<p>'.nl2br($plantRs->detail).'</p>' : '')
			. '</div>';


		$stmt = 'SELECT * FROM %topic_files% WHERE `orgid` = :orgid AND `tagname` = "ibuy,plant" AND `refid` = :refid';
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



	$ret .= $plantCardUi->build();
	*/

	//$ret .= print_o($treeInfo,'$treeInfo');

	return $ret;
}
?>