<?php
/**
* Green : Animal Information
* Created 2020-12-02
* Modify  2020-12-02
*
* @param Object $self
* @param Int $animalId
* @return String
*
* @usage green/rubber/my/rubber/{$Id}
*/

$debug = true;

function green_my_animal_view($self, $animalId) {
	$plantInfo = R::Model('green.plant.get', $animalId, '{data: "orgInfo,animal"}');

	if (!$plantInfo) return 'ไม่มีรายการ';

	$orgInfo = $plantInfo->orgInfo;
	$orgId = $orgInfo->orgId;

	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $plantInfo->uid == i()->uid;

	//debugMsg($plantInfo, '$plantInfo');

	$headerUi = new Ui();
	$navUi = new Ui();
	$dropUi = new Ui();

	if ($isItemEdit) {
		$headerUi->add('<a class="sg-action" href="'.url('green/my/animal/form/'.$animalId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$headerUi->add('<a class="sg-action" href="'.url('green/my/plant/'.$plantInfo->plantId.'/crop').'" data-rel="box"><i class="icon -material">content_cut</i></a>');
		if ($plantInfo->msgId) {
			$dropUi->add('<a id="delete" class="sg-action" href="'.url('green/my/info/activity.delete/'.$plantInfo->msgId).'" data-rel="notify" data-done="close | remove:#green-animal-'.$animalId.'" data-title="ลบ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบรายการ</span></a>');
		}
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	$ret = '<section id="green-my-animal-view" data-url="'.url('green/my/animal/view/'.$animalId).'">';
	$ret .= '<header class="header -hidden">'._HEADER_BACK.'<h3>'.$plantInfo->productName.' @'.$plantInfo->info->landName.'</h3><nav class="nav">'.$headerUi->build().'</nav></header>';

	$cardStr = '<div class="header">'._NL
		. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($plantInfo->info->username).'" width="32" height="32" alt="" />'._NL
		. '<h3>'.$plantInfo->productName.' @'.$plantInfo->info->landName.'</h3>'._NL
		. '<span class="poster-name">By '.$plantInfo->info->ownerName.' @'.sg_date($plantInfo->info->created, 'ว ดด ปปปป').'</span>'
		. '</span><!-- profile -->'._NL
		. '</div><!-- header -->'._NL;


	$cardStr .= '<div class="detail">'._NL
		. ($plantInfo->info->productcode ? 'หมายเลข <b>'.$plantInfo->info->productcode.'</b><br />' : '')
		. ($plantInfo->info->startdate ? 'เริ่มเลี้ยง '.sg_date($plantInfo->info->startdate, 'ว ดด ปปปป').($plantInfo->info->startage ? ' ตอนอายุ '.$plantInfo->info->startage.' เดือน' : '').'<br />' : '')
		. 'อายุ ? ปี ?? เดือน<br />'
		. ($plantInfo->info->weight ? 'น้ำหนัก '.$plantInfo->info->weight.' กิโลกรัม<br />' : '')
		. ($plantInfo->info->round ? 'รอบอก '.$plantInfo->info->round.' เซ็นติเมตร<br />' : '')
		. ($plantInfo->info->cropdate ? ' จำหน่าย '.sg_date($plantInfo->info->cropdate, 'ว ดด ปปปป').'<br />' : '')
		. 'จำนวน '.$plantInfo->info->qty.' '.$plantInfo->info->unit.'<br />'
		. nl2br($plantInfo->info->detail)
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
			. '<input type="hidden" name="orgid" value="'.$orgId.'" />'
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


	if ($navUi->count()) $cardStr .= '<nav class="nav -card">'.$navUi->build().'</nav>';

	$cardUi = new Ui('div', 'ui-card green-plant-card -single');
	$cardUi->add($cardStr);

	$ret .= $cardUi->build();

	$weightCard = new Ui(NULL, 'ui-card -weight');

	foreach ($plantInfo->animalWeight as $rs) {
		$cardStr = '<div class="header"><h3>@'.sg_date($rs->date, 'ว ดด ปปปป').'</h3></div>'
			. ($isItemEdit ? '<nav class="nav -icons -hover -top-right"><ul><li><a class="sg-action btn -link" href="'.url('green/my/animal/weight/form/'.$rs->weightId).'" data-rel="#weight-'.$rs->weightId.'"><i class="icon -material">edit</i></a></li><li><a class="sg-action btn -link" href="'.url('green/my/info/animal.weight.remove/'.$rs->weightId).'" data-rel="notify" data-done="remove:parent .ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel></i></a></li></nav>' : '')
			. '<div class="detail">'
			. ($rs->weight ? 'น้ำหนัก '.$rs->weight.' กก. ' : '')
			. ($rs->round ? 'รอบเอว '.$rs->round.' ซ.ม.' : '')
			. '<h4>อาหาร</h4>'
			. ($rs->grassweight ? 'หญ้า '.$rs->grassweight.' กก. ' : '')
			. ($rs->grassmoney ? 'จำนวนเงิน '.$rs->grassmoney.' บาท<br />' : '')
			. ($rs->strawweight ? 'ฟาง '.$rs->strawweight.' กก. ' : '')
			. ($rs->strawmoney ? 'จำนวนเงิน '.$rs->strawmoney.' บาท<br />' : '')
			. ($rs->foodweight ? 'อาหารข้น '.$rs->foodweight.' กก. ' : '')
			. ($rs->foodmoney ? 'จำนวนเงิน '.$rs->foodmoney.' บาท<br />' : '')
			. ($rs->mineralmoney ? 'แร่ธาตุ '.$rs->mineralmoney.' บาท ' : '')
			. ($rs->drugmoney ? 'ยา '.$rs->drugmoney.' บาท ' : '')
			//. print_o($rs,'$rs')
			. '</div>';
		$weightCard->add(
			$cardStr,
			array(
				'id' => 'weight-'.$rs->weightId,
				'class' => '-hover-parent',
			)
		);
	}

	if ($isItemEdit) {
		$weightCard->add('<nav class="nav -card" style="padding: 8px;"><ul><li><a id="add-weight" class="btn -link"><i class="icon -material">add_circle</i><span>บันทึกน้ำหนัก/อาหาร</span></a></li><li><a id="add-vaccine" class="btn -link -disabled"><i class="icon -material">add_circle</i><span>ฉีดวัคซีน</span></a></li></ul></nav>');
	}

	$ret .= $weightCard->build();

	//$ret .= print_o($orgInfo, '$orgInfo');
	//$ret .= print_o($plantInfo, '$plantInfo');

	$ret .= '<div class="-hidden">'
		. '<div id="green-weight-form">'.R::View('green.my.animal.weight.form','{plantId: '.$animalId.'}' , '{retUrl: "green/my/animal?land=$id", title: "เลือกคอก", btnText: "เลือกคอก"}')->build().'</div>'
		. '</div>';


	$ret .= '<style type="text/css">
	.module.-softganz-app {}
	</style>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {title: "'.htmlspecialchars($plantInfo->productName).'", refreshResume: false, history: true}
		menu = []
		menu.push({id: "edit", label: "แก้ไข", title: "แก้ไข", link: "green/my/animal/form/'.$animalId.'", options: {}})
		menu.push({id: "delete", label: "ลบ", title: "ลบ", action: "DELETE_ANIMAL"})
		options.menu = menu
		return options
	}
	function onWebViewResume() {
		console.log("CALL onWebViewResume FROM WEBVIEW")
	}
	function onWebViewMenuSelect(action) {
		if (action.action == "DELETE_ANIMAL") {
			$("#delete").trigger("click")
		}
	}
	</script>');

	$ret .= '<script>
	$("#add-weight").click(function() {
		var html = "@date<br />น้ำหนัก ??? กก. รอบอก ??? ซ.ม.<br />หญ้า ??? กก. ??? บาท<br />ฟาง ??? กก. ??? บาท<br />อาหารข้น ??? บาท<br />แร่ธาตุ ??? บาท<br />ยา ??? บาท"
		html = $("#green-weight-form").html()
		var $li = $("<li></li>").addClass("ui-item").html(html)
		$(this).closest(".ui-card>li").before($li)
	});
	</script>';

	$ret .= '<!-- green-my-animal-view --></section>';

	return $ret;
}
?>