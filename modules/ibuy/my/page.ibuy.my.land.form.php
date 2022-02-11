<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_my_land_form($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	if ($landId) {
		$stmt = 'SELECT *
			, CONCAT(X(`location`),",",Y(`location`)) `latlng`
			, X(`location`) `lat`
			, Y(`location`) `lnt`
			FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
		$data = mydb::select($stmt, ':landid', $landId, ':orgid', $shopId);
	}

	$ret = '';

	$ret = '<header class="header">'._HEADER_BACK.'<h3>แปลงผลิต @'.$shopInfo->name.'</h3></header>';

	$form = new Form('data', url('ibuy/my/info/land.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField('landid', array('type' => 'hidden', 'value' => $data->landid));

	$form->addField(
		'landname',
		array(
			'label' => 'ชื่อแปลง',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->landname),
			'placeholder' => 'ระบุชื่อแปลง',
		)
	);

	$form->addText('<div class="area -sg-flex -justify-left">');
	$form->addField(
		'arearai',
		array(
			'label' => 'พื้นที่ :',
			'type' => 'text',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->arearai),
			'posttext' => '<div class="input-append"><span>ไร่</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
//			'posttext' => '<div class="input-append"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-searchqt").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
		)
	);
	$form->addField(
		'areahan',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areahan),
			'posttext' => '<div class="input-append"><span>งาน</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addField(
		'areawa',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areawa),
			'posttext' => '<div class="input-append -nowrap"><span>ตร.วา</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addText('</div>');

	$form->addField(
		'producttype',
		array(
			'label' => 'ประเภท',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->producttype),
			'placeholder' => 'เช่น ข้าว,ผักอินทรีย์',
		)
	);

	$form->addField(
		'location',
		array(
			'label' => 'พิกัด GPS',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->latlng),
			'placeholder' => 'เช่น 7.0000,100.0000 หรือ '.htmlspecialchars('7° 5\' 3" N / 100° 40\' 9 E"'),
		)
	);

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->detail,
			'placeholder' => 'เช่น ปลูกพืชอะไร',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($data, '$data');
	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<style type="text/css">
	.area .form-item .form-text {width: 50px;}
	.form-item.-edit-data-arearai>label {margin-right: 16px; white-space: nowrap}
	</style>';

	return $ret;
}
?>