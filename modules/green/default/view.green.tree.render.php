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

function view_green_tree_render($treeInfo, $options = '{}') {
	$orgInfo = R::Model('org.get', $treeInfo->orgid);
	$isEdit = is_admin('green') || ($orgInfo->RIGHT & _IS_EDITABLE) || (i()->ok && in_array($orgInfo->is->membership, array('OFFICER')));
	$isItemEdit = $isEdit || $treeInfo->uid == i()->uid;

	$headerUi = new Ui();
	$navUi = new Ui();
	$dropUi = new Ui();
	$goodsUi = new Ui();

	//$headerUi->add('<a class="sg-action btn'.($treeInfo->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'green/standard/set/'.$treeInfo->landid : 'green/standard/info/'.$treeInfo->landid).'" data-rel="box" data-width="320">'.SG\getFirst($treeInfo->standard,'NONE').'</a>');

	/*
	if (in_array($treeInfo->approved, array('Approve','ApproveWithCondition'))) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('green/land/'.$treeInfo->landid.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
	}
	*/

	if ($isItemEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree/form/'.$treeInfo->plantid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('green/my/info/activity.delete/'.$treeInfo->msgid).'" data-rel="notify" data-done="remove:parent .ui-card.-plant>.ui-item" data-title="ลบต้นไม้" data-confirm="ต้องการลบต้นไม้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบต้นไม้</span></a>');
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
		. ($treeInfo->round ? ' เส้นรอบวง '.$treeInfo->round.' เซ็นติเมตร' : '')
		. ($treeInfo->height ? ' ความสูง '.$treeInfo->height.' เซ็นติเมตร' : '')
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
			'round -amt' => 'เส้นรอบวง(เซ็นติเมตร)',
			'height -amt -hover-parent' => 'ความสูง(เซ็นติเมตร)'
		);
		foreach ($roundDbs->items as $rs) {
			$roundMenu = new Ui();
			$roundMenu->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isItemEdit || $rs->ucreated == i()->uid) {
				$roundMenu->add('<a class="sg-action" href="'.url('green/my/info/tree.round.remove/'.$rs->bigid).'" data-rel="notify" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">cancel</i></a>');
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

	$ret .= '<div class="-photolist -action">'._NL
		. '<ul id="ibuy-plant-photo-'.$treeInfo->msgid.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div>'._NL;




	if ($isItemEdit) {
		$navUi->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$treeInfo->msgid).'" data-rel="#ibuy-plant-photo-'.$treeInfo->msgid.'" data-append="li">'
			. '<input type="hidden" name="module" value="GREEN" />'
			. '<input type="hidden" name="tagname" value="TREE" />'
			. '<input type="hidden" name="orgid" value="'.$treeInfo->orgid.'" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i><span class="-sg-is-desktop">'.$cameraStr.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>'
		);

		$navUi->add('<a class="sg-action btn -link" href="'.url('green/rubber/my/tree/round/'.$treeInfo->plantid).'" data-rel="box" data-width="480" title="บันทึกเส้นรอบวง"><i class="icon -material">donut_large</i><span class="-sg-is-desktop">เส้นรอบวง</span></a>');

		$navUi->add('<a class="sg-action btn -link" href="'.url('green/rubber/my/tree/map/'. $treeInfo->plantid, array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-height="100%" data-class-name="-map" data-webview="แผนที่ต้นไม้" title="พิกัดต้นไม้" data-PERMISSION="ACCESS_FINE_LOCATION"><i class="icon -material -land-map '.($treeInfo->location ? '-active' : '').'">'.($treeInfo->location ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');
	}


	if ($navUi->count()) $ret .= '<nav class="nav -card">'.$navUi->build().'</nav>';

	//$ret .= print_o($orgInfo, '$orgInfo');
	//$ret .= print_o($treeInfo,'$treeInfo');

	return $ret;
}
?>