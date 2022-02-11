<?php
/**
* Green :: Organic My Land View
* Created 2020-11-13
* Modify  2020-11-14
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/organic/my/land/{$Id}
*/

$debug = true;

function green_organic_my_land_view($self, $landId = NULL) {
	$landInfo = R::Model('green.land.get', $landId, '{debug:false, data: "orgInfo,plantInfo"}');

	if (!$landInfo) return message('error', 'ไม่มีแปลงการผลิดตามเงื่อนไขที่ระบุ');

	$orgInfo = $landInfo->orgInfo;

	$isAdmin = is_admin('green');
	$isEdit = $isAdmin || $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddPlant = $isEdit || in_array($orgInfo->is->membership,array('NETWORK')) || i()->uid == $landInfo->uid;

	$ret = '';

	$toolbar = new Toolbar($self, $landInfo->landName.' @'.$orgInfo->name);
	$ui = new Ui(NULL, 'ui-nav -main');

	//$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	//$ui->add('<a href="'.url('green/rubber/my/land').'"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	//$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	//$ui->add('<a class="sg-action -add" href="#green-land-form" data-rel="#input"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	//$toolbar->addNav('main', $ui);


	if (cfg('green')->land->showPlantMenu) {
		$mainUi = new Ui();
		$mainUi->addConfig('container', '{tag: "nav", class: "nav -app-menu green-land-menu"}');
		if ($isAddPlant) {
			$mainUi->add('<a class="sg-action" href="'.url('green/my/plant/form',array('land' => $landId)).'" data-rel="box" data-width="480" data-webview="ปลูกผัก"><i class="icon -material">add</i><span>ปลูกผัก</span></a>');
		}
		//$mainUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="กำหนดค่า"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');

		$ret .= $mainUi->build();
	}


	$ret .= '<section class="green-land-card">';

	$ret .= '<div id="input"></div>';




	$cardUi = new Ui('div', 'ui-card -land');

	$cameraStr = 'ภาพแปลง';

	$isItemEdit = $isEdit || $landInfo->uid == i()->uid;

	$headerUi = new Ui();
	$navUi = new Ui();
	$dropUi = new Ui();
	$goodsUi = new Ui();

	$headerUi->add('<a class="sg-action btn'.($landInfo->info->approved == 'Approve' ? ' -success' : '').'" href="'.url($isAdmin ? 'green/standard/set/'.$landId : 'green/standard/info/'.$landId).'" data-rel="box" data-width="320">'.SG\getFirst($landInfo->info->standard,'NONE').'</a>');
		if (in_array($landInfo->info->approved, array('Approve','ApproveWithCondition'))) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('green/land/'.$landId.'/qr').'" data-rel="box" data-width="320" data-webview="QR Code" title="QR Code ของแปลงผลิต"><i class="icon -qrcode -sg-24"></i><span class="-hidden">QR Code</span></a>');
	}

	if ($isItemEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/land/form/'.$landId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('green/my/land/change/group/'.$landId).'" data-rel="box" data-width="640"><i class="icon -material">people</i><span>ย้ายกลุ่ม</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('green/my/land/change/owner/'.$landId).'" data-rel="box" data-width="640"><i class="icon -material">person</i><span>เปลี่ยนเจ้าของ</span></a>');
		$dropUi->add('-');
		$dropUi->add('<a class="sg-action" href="'.url('green/my/info/land.remove/'.$landId).'" data-rel="notify" data-done="remove:parent .ui-card.-land>.ui-item" data-title="ลบแปลงผลิต" data-confirm="ต้องการลบแปลงผลิต กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบแปลงผลิต</span></a>');
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$cardStr = '<div class="header">'._NL
		. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($landInfo->info->username).'" width="32" height="32" alt="" />'._NL
		. '<h3>'.$landInfo->landName.'</h3>'._NL
		. '<span class="poster-name">By '.$landInfo->info->ownerName.'</span>'
		. '</span><!-- profile -->'._NL
		. '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL
		. '</div><!-- header -->'._NL;

	$cardStr .= '<div class="detail">'._NL
		. '<p>พื้นที่ '
		. ($landInfo->info->arearai > 0 ? $landInfo->info->arearai.' ไร่ ' : '')
		. ($landInfo->info->areahan > 0 ? $landInfo->info->areahan.' งาน ' : '')
		. ($landInfo->info->areawa > 0 ? $landInfo->info->areawa.' ตารางวา' : '')
		. ($landInfo->info->deedno ? ' เลขโฉนดที่ดิน '.$landInfo->info->deedno.' <a class="sg-action" href="https://dolwms.dol.go.th/tvwebp/" target="_blank" data-webview="ค้นหารูปแปลงที่ดิน">ค้นหารูปแปลงที่ดิน</a>' : '')
		. '<br />'._NL
		. 'มาตรฐาน '.$landInfo->info->standard.' ('.$landInfo->info->approved.')<br />'._NL
		. 'ประเภทผลผลิต '.$landInfo->info->producttype.'</p>'
		. ($landInfo->info->detail ? '<p>'.nl2br($landInfo->info->detail).'</p>' : '')
		. '</div><!-- detail -->'._NL;

	$photoStr = '';
	foreach ($landInfo->photos as $item) {
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
		. '<ul id="green-land-photo-'.$landId.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div>'._NL;


	if ($isItemEdit) {
		$navUi->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$landId).'" data-rel="#green-land-photo-'.$landId.'" data-append="li">'
			. '<input type="hidden" name="orgid" value="'.$landInfo->orgId.'" />'
			. '<input type="hidden" name="module" value="GREEN" />'
			. '<input type="hidden" name="tagname" value="LAND" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span class="-sg-is-desktop">'.$cameraStr.'</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>'
		);

		$navUi->add('<a class="sg-action btn -link" href="'.url('green/my/land.map/'.$landId, array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต"><i class="icon -material -land-map '.($landInfo->info->location ? '-active' : '').'">'.($landInfo->info->location ? 'where_to_vote' : 'room').'</i><span class="-sg-is-desktop">แผนที่</span></a>');
	}


	if ($navUi->count()) $cardStr .= '<nav class="nav -card">'.$navUi->build().'</nav>';

	$cardUi->add($cardStr, '{id: "green-land-'.$landId.'"}');

	$ret .= $cardUi->build();




	// Show Plant in Land
	$plantCardUi = R::View('green.my.plant.list', $landInfo->plantInfo);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshResume: false}
		return options
	}
	function onWebViewResume() {
		console.log("CALL onWebViewResume")
	}
	</script>');

	return $ret;
}
?>
