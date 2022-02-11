<?php
/**
* Green : Plant Information
* Created 2020-11-09
* Modify  2020-11-09
*
* @param Object $self
* @param Int $plantId
* @return String
*
* @usage green/plant/view/{$Id}
*/

$debug = true;

function green_my_plant_view($self, $plantId) {
	$plantInfo = R::Model('green.plant.get', $plantId, '{data: "orgInfo"}');

	if (!$plantInfo) return 'ไม่มีรายการ';

	$orgInfo = $plantInfo->orgInfo;

	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $plantInfo->uid == i()->uid;

	$headerUi = new Ui();
	$dropUi = new Ui();

	if ($isItemEdit) {
		$headerUi->add('<a class="sg-action" href="'.url('green/my/plant/form/'.$plantId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$headerUi->add('<a class="sg-action" href="'.url('green/my/plant/'.$plantInfo->plantId.'/crop').'" data-rel="box" title="เก็บเกี่ยว/ตัด"><i class="icon -material">content_cut</i></a>');
		if ($plantInfo->msgId) {
			$dropUi->add('<a class="sg-action" href="'.url('green/my/plant/'.$plantInfo->plantId.'/crop').'" data-rel="box"><i class="icon -material">content_cut</i><span>เก็บเกี่ยว/ตัด</span></a>');
			$dropUi->add('<sep>');
			$dropUi->add('<a class="sg-action" href="'.url('green/my/info/activity.delete/'.$plantInfo->msgId).'" data-rel="notify" data-done="back | remove:#green-plant-'.$plantId.'" data-title="ลบ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบรายการ</span></a>');
		}
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$plantInfo->productName.' @'.$plantInfo->info->landName.'</h3><nav class="nav">'.$headerUi->build().'</nav></header>';

	$cardStr = '<div class="header">'._NL
		. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($plantInfo->info->username).'" width="32" height="32" alt="" />'._NL
		. '<h3>'.$plantInfo->info->landName.'</h3>'._NL
		. '<span class="poster-name">By '.$plantInfo->info->ownerName.' @'.sg_date($plantInfo->info->created, 'ว ดด ปปปป').'</span>'
		. '</span><!-- profile -->'._NL
		. '</div><!-- header -->'._NL;


	$cardStr .= '<div class="detail">'._NL
		. ($plantInfo->productcode ? 'หมายเลขต้นไม้ <b>'.$plantInfo->productcode.'</b><br />' : '')
		. ($plantInfo->info->round ? 'เส้นรอบวง '.$plantInfo->info->round.' เมตร'.'<br />' : '')
		. ($plantInfo->info->height ? 'ความสูง '.$plantInfo->info->height.' เมตร'.'<br />' : '')
		. ($plantInfo->info->startdate ? 'เริ่มปลูก '.sg_date($plantInfo->info->startdate, 'ว ดด ปปปป') : '')
		. ($plantInfo->info->cropdate ? ' เก็บเกี่ยว '.sg_date($plantInfo->info->cropdate, 'ว ดด ปปปป') : '')
		. '<br />'
		. ( $plantInfo->arearai || $plantInfo->arearai || $plantInfo->arearai
			?
			'พื้นที่ '
			. ($plantInfo->arearai > 0 ? $plantInfo->arearai.' ไร่ ' : '')
			. ($plantInfo->areahan > 0 ? $plantInfo->areahan.' งาน ' : '')
			. ($plantInfo->areawa > 0 ? $plantInfo->areawa.' ตารางวา' : '').'<br />'._NL
			: ''
			)
		. 'จำนวน '.$plantInfo->info->qty.' '.$plantInfo->info->unit.'<br />'
		. 'ราคาขาย '.$plantInfo->info->saleprice.' บาท'.'<br />'
		. 'ราคาจอง '.$plantInfo->info->bookprice.' บาท'.'<br />'
		. 'คงเหลือ '.$plantInfo->info->balance.'<br />'
		. nl2br($plantInfo->info->detail)
		. ($plantInfo->info->landDetail ? '<p>'.nl2br($plantInfo->info->landDetail).'</p>' : '')
		. '</div><!-- detail -->'._NL;

	$photoStr = '';

	$photoAlbumUi = new Ui(NULL,'ui-album -'.$photo->fid.' -justify-left');
	$photoAlbumUi->addId($photo->fid);

	foreach ($plantInfo->photos as $photo) {
		$photoStrItem = '';
		$photoNav = new Ui('span');
		$photoNav->addConfig('container', '{tag: "nav", class: "nav -icons -hover -top-right -no-print"}');

		if ($photo->type == 'photo') {
			//$ret.=print_o($photo,'$photo');
			$photoInfo = model::get_photo_property($photo->file);
			$photo_alt = $photo->title;

			if ($isItemEdit) {
				$photoNav->add('<a class="sg-action" href="'.url('green/my/info/photo.delete/'.$photo->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
			}

			$photoAlbumUi->add(
				'<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">'
				. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />'
				. '</a>'
				. $photoNav->build(),
				array('class' => '-hover-parent')
			);

		} else if ($photo->type == 'doc') {
			$docStr .= '<li class="-hover-parent">';
			$docStr .= '<a href="'.cfg('paper.upload.document.url').$photo->file.'" title="'.htmlspecialchars($photo_alt).'">';
			$docStr .= '<img class="doc-logo -pdf" src="http://img.softganz.com/icon/icon-file.png" width="63" style="display: block; padding: 16px; margin: 0 auto; background-color: #eee; border-radius: 50%;" />';
			$docStr .= $photo->title;
			$docStr .= '</a>';

			if ($isItemEdit) {
				$ui->add('<a class="sg-action" href="'.url('green/my/info/docs.delete/'.$photo->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
			}
			$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
			$docStr .= '</li>';
		}
	}

	list($treeModule, $treeTagName) = explode(',', $plantInfo->info->tagname);

	if ($isItemEdit) {
		$photoAlbumUi->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$plantInfo->msgId).'" data-rel=".ui-album" data-before="li">'
			. '<input type="hidden" name="module" value="'.$treeModule.'" />'
			. '<input type="hidden" name="tagname" value="'.$treeTagName.'" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>',
			'{class: "-upload"}'
		);
	}

	$cardStr .= $photoAlbumUi->build();




	$cardStr .= '<div class="-photolist -action">'._NL
		. '<ul id="ibuy-plant-photo-'.$plantInfo->info->msgid.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div>'._NL;

	if ($isItemEdit) {
	}




	$cardUi = new Ui('div', 'ui-card green-plant-card -single');
	$cardUi->add($cardStr);

	$ret .= $cardUi->build();

	//$ret .= print_o($orgInfo, '$orgInfo');
	//$ret .= print_o($plantInfo, '$plantInfo');

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {}
		menu = []
		menu.push({id: "edit", label: "แก้ไข", title: "แก้ไข", link: "green/my/plant/form/'.$plantId.'", options: {actionBar: true}})
		menu.push({id: "delete", label: "ลบ", title: "ลบ", link: "green/my/plant/delete/'.$plantId.'", options: {actionBar: true}})
		options.menu = menu
		return options
	}
	</script>');

	return $ret;
}
?>