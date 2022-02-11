<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_my_location_form($self, $landId = NULL) {
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

	$ret = '<header class="header">'._HEADER_BACK.'<h3>ตำแหน่ง'.$shopInfo->name.'</h3></header>';

	$form = new Form('data', url('ibuy/my/info/location.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back');

	$form->addField('locid', array('type' => 'hidden', 'value' => $data->locid));

	$form->addField(
		'locname',
		array(
			'label' => 'ชื่อสถานที่',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->locname),
			'placeholder' => 'ระบุชื่อสถานที่',
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
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
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