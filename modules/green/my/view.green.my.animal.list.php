<?php
/**
* Green View :: Plant List Card
*
* @param Object $plantList
* @param Object $options
* @return Ui
*/

$debug = true;

function view_green_my_animal_list($plantList, $options = '{}') {
	$plantUi = new Ui(NULL, 'ui-card green-plant-list');
	$plantUi->addConfig('container', '{tag: "div", class: "green-my-plant"}');

	foreach ($plantList as $rs) {
		$isCroped = $rs->cropdate && $rs->cropdate <= date('Y-m-d');
		$linkUrl = url('green/my/animal/view/'.$rs->plantid);
		$photoInfo = $rs->coverPhoto ? model::get_photo_property($rs->coverPhoto) : NULL;

		$cardStr = '<div class="header"><h3>'
			. $rs->productname
			. ($rs->productcode ? ' #'.$rs->productcode : '')
			. ($rs->landName ? ' <span>@'.$rs->landName.'</span>' : '')
			. ($isCroped ? ' <span>(จำหน่ายแล้ว)</span>' : '')
			. '</h3>'
			. ($rs->username ? '<br /><span>โดย '.$rs->ownerName.'</span>' : '')
			. '<span> เมื่อ '.sg_date($rs->created, 'ว ดด ปป H:i').' น.</span>'
			. '</div>'
			. '<div class="detail">'
			. ($rs->coverPhoto ? '<span class="-cover-photo"><img src="'.$photoInfo->_url.'" width="140" /></span>' : '')
			. '<span class="-info">'
			. ($rs->startdate ? 'เริ่มเลี้ยง '.sg_date($rs->startdate) : '')
			. ($rs->cropdate ? ' วันจำหน่าย '.sg_date($rs->cropdate) : '')
			. ' จำนวน '.$rs->qty.' ตัว'
			//. print_o($rs,'$rs')
			. '</span>'
			. '</div>'
			. '<nav class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดสัตว์</a></nav>';

		$plantUi->add(
			$cardStr,
			array(
				'id' => 'green-animal-'.$rs->plantid,
				'class' => 'sg-action'.($isCroped ? ' -croped' : ''),
				'href' => $linkUrl,
				'data-rel' => 'box',
				'data-width' => '640',
				'data-height' => '100%',
				'data-webview' => $rs->productname,
			)
		);
	}

	return $plantUi;
}
?>