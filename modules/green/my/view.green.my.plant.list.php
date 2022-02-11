<?php
/**
* Green View :: Plant List Card
*
* @param Object $plantList
* @param Object $options
* @return Ui
*/

$debug = true;

function view_green_my_plant_list($plantList, $options = '{}') {
	$plantUi = new Ui(NULL, 'ui-card green-plant-list');
	$plantUi->addConfig('container', '{tag: "div", class: "green-my-plant"}');

	foreach ($plantList as $rs) {
		$isCroped = $rs->cropdate && $rs->cropdate <= date('Y-m-d');
		if ($rs->tagname == 'GREEN,TREE') {
			$linkUrl = url('green/rubber/my/tree/'.$rs->plantid);
		} else if ($rs->tagname == 'GREEN,RUBBER') {
			$linkUrl = url('green/rubber/my/rubber/'.$rs->plantid);			
		} else if ($rs->tagname == 'GREEN,PLANT') {
			$linkUrl = url('green/my/plant/'.$rs->plantid);			
		} else if ($rs->tagname == 'GREEN,ANIMAL') {
			$linkUrl = url('green/my/animal/view/'.$rs->plantid);			
		} else {
			$linkUrl = url('green/my/plant/'.$rs->plantid);
		}

		$photoInfo = $rs->coverPhoto ? model::get_photo_property($rs->coverPhoto) : NULL;

		$cardStr = '<div class="header"><h3><i class="icon -material -sg-16">nature</i>'
			. $rs->productname
			. ($rs->productcode ? ' #'.$rs->productcode : '')
			. ($rs->landName ? ' <span>@'.$rs->landName.'</span>' : '')
			. ($isCroped ? ' <span>(เก็บเกี่ยวแล้ว)</span>' : '')
			. '</h3>'
			. ($rs->username ? '<br /><span>โดย '.$rs->ownerName.'</span>' : '')
			. '<span>เมื่อ '.sg_date($rs->created, 'ว ดด ปป H:i').' น.</span>'
			. '</div>'
			. '<div class="detail">'
			. ($rs->coverPhoto ? '<span class="-cover-photo"><img src="'.$photoInfo->_url.'" width="140" /></span>' : '')
			. '<span class="-info">'
			. ($rs->startdate ? 'เริ่มลงแปลง '.sg_date($rs->startdate) : '')
			. ($rs->cropdate ? ' วันเก็บเกี่ยว '.sg_date($rs->cropdate) : '')
			. ($rs->tagname == 'GREEN,RUBBER' ? ' จำนวน '.$rs->qty.' ต้น' : '')
			//. print_o($rs,'$rs')
			. '</span>'
			. '</div>'
			. '<nav class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดผลผลิต</a></nav>';

		$plantUi->add(
			$cardStr,
			array(
				'id' => 'green-plant-'.$rs->plantid,
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