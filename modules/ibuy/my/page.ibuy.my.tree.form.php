<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Int $plantId
* @return String
*/

$debug = true;

function ibuy_my_tree_form($self, $plantId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	if ($landId = post('land')) {
		$stmt = 'SELECT *
			, CONCAT(X(`location`),",",Y(`location`)) `latlng`
			, X(`location`) `lat`
			, Y(`location`) `lnt`
			FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
		$landInfo = mydb::select($stmt, ':landid', $landId, ':orgid', $shopId);
		$data->landid = $landInfo->landid;
	}

	if ($plantId) {
		$stmt = 'SELECT p.*, m.`msgid`
			FROM %ibuy_farmplant% p
				LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			WHERE p.`plantid` = :plantid AND p.`orgid` = :orgid
			LIMIT 1';
		$data = mydb::select($stmt, ':plantid', $plantId, ':orgid', $shopId);
	}


	$stmt = 'SELECT
		l.`landid`, l.`landname`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(`location`),",",Y(`location`)) `latlng`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		WHERE `orgid` = :orgid';

	$landDbs = mydb::select($stmt, ':orgid', $shopId);
	$landOptions = array();
	foreach ($landDbs->items as $rs) $landOptions[$rs->landid] = $rs->landname;

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>ธนาคารต้นไม้ @'.$shopInfo->name.'</h3></header>';

	//$ret .= print_o($landInfo);

	$form = new Form(NULL, url('ibuy/my/info/activity.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#ibuy-green-my-tree');

	if ($data->msgid) {
		$form->addField('msgid', array('type' => 'hidden', 'value' => $data->msgid));
	}

	if($data->plantid) {
		$form->addField('plantid', array('type' => 'hidden', 'value' => $data->plantid));
	}

	$form->addField('tagname',array('type' => 'hidden', 'value' => 'GREEN,TREE'));

	$form->addField('message',array('type' => 'hidden', 'value' => 'ปลูกต้นไม้'));


	/*
	$form->addField(
		'productname',
		array(
			'label' => 'ชื่อต้นไม้',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->productname),
			'placeholder' => 'ระบุชื่อผลผลิต',
		)
	);
	*/

	$productOptions = R::Model('category.get', 'tree:kind', 'catid', '{debug: false, order: "CONVERT(tg.`name` USING tis620)"}');

	//$ret .= print_o($productOptions,'$productOptions');
	$form->addField(
		'catid',
		array(
			'type' => 'select',
			'label' => 'พันธุ์ไม้:',
			'class' => '-fill',
			'require' => true,
			'value' => $data->catid,
			'options' => array('' => '== เลือกพันธุ์ไม้ ==')+ $productOptions,
		)
	);

	$form->addField(
		'landid',
		array(
			'label' => 'แปลงที่ดิน:',
			'type' => 'select',
			'class' => '-fill',
			'value' => $data->landid,
			'options' => $landOptions,
		)
	);

	$form->addField(
		'productname',
		array(
			'type' => 'text',
			'label' => 'ชื่อพันธุ์ไม้',
			'class' => '-fill',
			'value' => $data->productname,
			'placeholder' => 'ระบุชื่อพันธุ์ไม้',
		)
	);

	$form->addField(
		'productcode',
		array(
			'type' => 'text',
			'label' => 'หมายเลขต้นไม้',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => $data->productcode,
			'placeholder' => 'ระบุหมายเลขไม้',
		)
	);

	$form->addField(
		'startdate',
		array(
			'label' => 'วันที่เริ่มปลูก',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'require' => true,
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
			'type' => 'hidden',
			'label' => 'ปริมาณการผลิต',
			'value' => 1,
		)
	);

	$form->addField(
		'unit',
		array(
			'type' => 'hidden',
			'label' => 'หน่วยการผลิต:',
			'value' => 'ต้น',
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
			'placeholder' => 'ระบุรายละเอียดอื่นๆ',
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

	$ret .= '<script type="text/javascript">
	$("#edit-catid").change(function() {
		var kindId = $(this).val()
		var selectedText = $(this).find("option:selected").text()
		console.log($(this).find("option:selected").text())
		if (kindId == 999) {
			$("#edit-productname").val("").attr("type", "text")
			$("#edit-message").val("ปลูกต้นไม้")
		} else {
			$("#edit-productname").val(selectedText)
			$("#edit-message").val("ปลูกต้นไม้ "+selectedText)
		}
	})
	</script>';
	return $ret;
}
?>