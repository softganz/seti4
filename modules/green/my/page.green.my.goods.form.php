<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_my_goods_form($self, $productId = NULL) {
	$shopId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	if ($landId = post('land')) {
		$stmt = 'SELECT *
			, CONCAT(X(`location`),",",Y(`location`)) `latlng`
			, X(`location`) `lat`
			, Y(`location`) `lnt`
			FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
		$landInfo = mydb::select($stmt, ':landid', $landId, ':orgid', $shopId);
	}

	if ($productId) {
		$stmt = 'SELECT * FROM %ibuy_farmplant% WHERE `plantid` = :plantid AND `orgid` = :orgid LIMIT 1';
		$data = mydb::select($stmt, ':plantid', $productId, ':orgid', $shopId);
	}

	$ret = '';

	$ret = '<header class="header">'._HEADER_BACK.'<h3>เพิ่มสินค้า @'.$shopInfo->name.'</h3></header>';

	$form = new Form('data', url('green/my/info/product.save/'.$productId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField('plantid', array('type' => 'hidden', 'value' => $data->plantid));

	$form->addField(
		'title',
		array(
			'label' => 'ชื่อสินค้า',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->title),
			'placeholder' => 'ระบุชื่อสินค้า',
		)
	);

	$productOptions = R::Model('category.get', array('vid' => cfg('ibuy.vocab.category')), 'tid', '{result: "group"}');

	$form->addField(
		'productid',
		array(
			'type' => 'select',
			'class' => '-fill',
			'require' => true,
			'value' => $data->productid,
			'options' => array_merge_recursive(array('' => '== เลือกชนิด =='), $productOptions),
		)
	);

	$form->addField(
		'listprice',
		array(
			'type' => 'text',
			'label' => 'ราคาตั้ง (บาท)',
			'class' => '-fill',
			'require' => true,
			'value' => $data->listprice,
			'placeholder' => '0.00',
		)
	);

	$form->addField(
		'price1',
		array(
			'type' => 'text',
			'label' => 'ราคาขาย (บาท)',
			'class' => '-fill',
			'require' => true,
			'value' => $data->retailprice,
			'placeholder' => '0.00',
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