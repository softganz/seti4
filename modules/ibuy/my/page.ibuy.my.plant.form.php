<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Int $plantId
* @return String
*/

$debug = true;

function ibuy_my_plant_form($self, $plantId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	if ($landId = post('land')) {
		$stmt = 'SELECT *
			, CONCAT(X(`location`),",",Y(`location`)) `latlng`
			, X(`location`) `lat`
			, Y(`location`) `lnt`
			FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
		$landInfo = mydb::select($stmt, ':landid', $landId, ':orgid', $shopId);
	}

	if ($plantId) {
		$stmt = 'SELECT p.*, m.`msgid`
			FROM %ibuy_farmplant% p
				LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			WHERE p.`plantid` = :plantid AND p.`orgid` = :orgid
			LIMIT 1';
		$data = mydb::select($stmt, ':plantid', $plantId, ':orgid', $shopId);
	}

	$ret = '';

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>เพิ่มผลผลิตรอบใหม่ @'.$shopInfo->name.'</h3></header>';

	$form = new Form(NULL, url('ibuy/my/info/activity.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	if ($data->msgid) {
		$form->addField('msgid', array('type' => 'hidden', 'value' => $data->msgid));
	}

	if ($data->plantid) {
		$form->addField('plantid', array('type' => 'hidden', 'value' => $data->plantid));
	}
	$form->addField('landid', array('type' => 'hidden', 'value' => $landInfo->landid));
	$form->addField('tagname', array('type' => 'hidden', 'value' => 'ibuy-plant'));

	$form->addField(
		'productname',
		array(
			'label' => 'ชื่อผลผลิต',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->productname),
			'placeholder' => 'ระบุชื่อผลผลิต',
		)
	);

	$productOptions = R::Model('category.get', array('vid' => cfg('ibuy.vocab.category')), 'tid', '{debug: false, result: "group"}');
	/*
	$productOptions['สินค้าเกษตร']['ข้าว'] = $productOptions['ข้าว'];
	$ret .= print_o($productOptions,'$productOptions');
	*/

	$form->addField(
		'catid',
		array(
			'type' => 'select',
			'label' => 'หมวด:',
			'class' => '-fill',
			'require' => true,
			'value' => $data->catid,
			'options' => array_merge_recursive(array('' => '== เลือกหมวด =='), $productOptions),
		)
	);

	$form->addField(
		'startdate',
		array(
			'label' => 'วันที่เริ่มลงแปลง',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->startdate ? sg_date($data->startdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'cropdate',
		array(
			'label' => 'วันที่คาดว่าจะเก็บเกี่ยว',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => $data->cropdate ? sg_date($data->cropdate, 'd/m/Y') : '',
			'placeholder' => '31/12/2562',
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'text',
			'label' => 'ปริมาณการผลิต',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->qty),
		)
	);

	$form->addField(
		'unit',
		array(
			'type' => 'select',
			'label' => 'หน่วยการผลิต:',
			'maxlength' => 20,
			'options' => array('กก.' => 'กก.', 'ลิตร' => 'ลิตร', 'ก้อน' => 'ก้อน', 'ชิ้น' => 'ชิ้น', 'ตัว' => 'ตัว', 'แพ็ค' => 'แพ็ค', 'ขวด' => 'ขวด', 'ลัง' => 'ลัง'),
			'value' => htmlspecialchars($data->unit),
		)
	);

	$form->addField(
		'saleprice',
		array(
			'type' => 'text',
			'label' => 'ราคาขาย (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->saleprice),
		)
	);

	$form->addField(
		'bookprice',
		array(
			'type' => 'text',
			'label' => 'ราคาจอง (บาท)',
			'maxlength' => 10,
			'value' => htmlspecialchars($data->bookprice),
		)
	);

	/*
	$form->addField(
		'safety',
		array(
			'type' => 'radio',
			'label' => 'ประเภทของการผลิต:',
			'options' => array(1 => 'ปลอดภัย', 2 => 'ไร้สารพิษ'),
			'value' => htmlspecialchars($data->safety),
		)
	);
	*/

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->detail,
			'placeholder' => 'เช่น ปลูกพืชอะไร รายละเอียด/วิธีการปลูก',
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

	return $ret;
}
?>